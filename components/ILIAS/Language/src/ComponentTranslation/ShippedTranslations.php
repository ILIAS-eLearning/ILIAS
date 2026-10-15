<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\ComponentTranslation;

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use RuntimeException;

/**
 * Compiles the messages of a migrated module into the `.mo` native gettext serves (see NativeGettext): the
 * shipped state for the build (ShippedTranslationsBuild) and the overlay of local changes
 * (MigratedLanguageFileSync), both via compileCatalog().
 *
 * Layout, so txt() needs one lookup per identifier:
 * - every message of the module (see MigratedLanguageFileSync::moduleEntries()) under its identifier
 *   WITHOUT context;
 * - a plural message additionally with every form under PLURAL_CONTEXT (dngettext() with
 *   "<context>\x04<identifier>"), while the entry without context holds its default form
 *   (PluralForms::defaultValueOf());
 * - with $mark_identity, "IDENTITY_CONTEXT\x04<identifier>" => "1" for every message whose value is its
 *   identifier: gettext answers a missing translation with the identifier, this tells "translated as
 *   itself" from "not translated" (see MigratedTranslations).
 * The charset is always UTF-8; "Plural-Forms" is the rule PluralForms reads from the source (Germanic for
 * a missing or invalid header), so gettext and PluralForms agree. Untranslated messages are left out,
 * fuzzy ones compiled (see TranslationCatalog::toMoString()).
 *
 * compile() is compileCatalog() of a shipped `.po` with every value cleaned by TranslationMarkupPolicy::sanitize().
 */
final class ShippedTranslations
{
    public const string PLURAL_CONTEXT = 'ilias-plural';

    public const string IDENTITY_CONTEXT = 'ilias-identity';

    public function __construct(
        private readonly TranslationMarkupPolicy $markup_policy = new TranslationMarkupPolicy()
    ) {
    }

    /**
     * The compiled catalog of $module in $shipped_po, with markers (see the class docblock).
     *
     * @param (callable(string $identifier, list<string> $violations): void)|null $on_markup_violation
     *        called for every cleaned value with what was removed (for a plural form with its form key,
     *        see PluralFormKey)
     * @param (callable(string $message): void)|null $on_invalid_plural_forms called if there are plural
     *        messages but the "Plural-Forms" header is missing or invalid (the Germanic rule is used then)
     * @return array{mo: string, values: array<string, string>} the `.mo` and the value it serves for
     *         each identifier (see compileCatalog())
     * @throws RuntimeException if $shipped_po cannot be read, parsed or compiled
     */
    public function compile(
        string $shipped_po,
        string $module,
        ?callable $on_markup_violation = null,
        ?callable $on_invalid_plural_forms = null
    ): array {
        return $this->compileShippedCatalog(
            TranslationCatalog::fromPoFile($shipped_po),
            $module,
            $on_markup_violation,
            $on_invalid_plural_forms
        );
    }

    /**
     * @param (callable(string $identifier, list<string> $violations): void)|null $on_markup_violation see compile()
     * @param (callable(string $message): void)|null $on_invalid_plural_forms see compile()
     * @return array{mo: string, values: array<string, string>} see compile()
     * @throws RuntimeException if the messages cannot be compiled
     */
    public function compileShippedCatalog(
        TranslationCatalog $shipped,
        string $module,
        ?callable $on_markup_violation = null,
        ?callable $on_invalid_plural_forms = null
    ): array {
        return self::compileCatalog(
            $shipped,
            $module,
            true,
            fn(string $value, string $key): string => $this->sanitized($value, $key, $on_markup_violation),
            $on_invalid_plural_forms
        );
    }

    /**
     * The catalog native gettext serves for $module's messages in $source, see the class docblock.
     * Deterministic: the same messages always compile to the same bytes.
     *
     * @param bool $mark_identity whether to add the markers of messages translated as their identifier
     * @param (\Closure(string $value, string $key): string)|null $value_of what is compiled for a value
     *        (every form of a plural message with its form key, see PluralFormKey); default: the value itself
     * @param (callable(string $message): void)|null $on_invalid_plural_forms see compile()
     * @return array{mo: string, values: array<string, string>} the `.mo` and the value it serves for
     *         each identifier without context (for a plural message the value of its default form),
     *         in catalog order
     * @throws RuntimeException if the messages cannot be compiled (see TranslationCatalog::toMoString())
     */
    public static function compileCatalog(
        TranslationCatalog $source,
        string $module,
        bool $mark_identity,
        ?\Closure $value_of = null,
        ?callable $on_invalid_plural_forms = null
    ): array {
        $catalog = new TranslationCatalog();
        foreach ($source->getHeaders() as $name => $value) {
            $catalog->setHeader($name, $value);
        }
        // never an empty header: MigratedTranslations tells a readable catalog by it, and the C
        // library takes the charset from it (TranslationCatalog only reads UTF-8)
        $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');

        $plural_forms = null;
        $values = [];
        foreach (MigratedLanguageFileSync::moduleEntries($source, $module) as $identifier => $entry) {
            $identifier = (string) $identifier;
            if ($entry->isPlural()) {
                $forms = $entry->getPluralTranslations();
                foreach ($forms as $form => $form_value) {
                    $forms[$form] = $value_of === null ? $form_value : $value_of($form_value, PluralFormKey::of($identifier, $form));
                }
                if (implode('', $forms) === '') {
                    continue;
                }
                if ($plural_forms === null) {
                    $plural_forms = PluralForms::fromHeaderOrGermanic($source->getHeader('Plural-Forms'), $on_invalid_plural_forms);
                    $catalog->setHeader('Plural-Forms', $plural_forms->getHeader());
                }
                $plural = new TranslationEntry(self::PLURAL_CONTEXT, $identifier);
                $plural->setPlural((string) $entry->getPluralId(), $forms);
                $catalog->add($plural);
                $value = $plural_forms->defaultValueOf($forms);
            } else {
                $value = $value_of === null ? $entry->getTranslation() : $value_of($entry->getTranslation(), $identifier);
            }
            if ($value === '') {
                continue;
            }

            $singular = new TranslationEntry(null, $identifier);
            $singular->translate($value);
            $catalog->add($singular);
            $values[$identifier] = $value;
            if ($mark_identity && $value === $identifier) {
                $marker = new TranslationEntry(self::IDENTITY_CONTEXT, $identifier);
                $marker->translate('1');
                $catalog->add($marker);
            }
        }

        return ['mo' => $catalog->toMoString(), 'values' => $values];
    }

    /**
     * $value cleaned by the markup policy, $on_markup_violation called for $key if anything was removed.
     *
     * @param (callable(string $identifier, list<string> $violations): void)|null $on_markup_violation
     */
    private function sanitized(string $value, string $key, ?callable $on_markup_violation): string
    {
        $violations = $this->markup_policy->findViolations($value);
        if ($violations === []) {
            return $value;
        }
        if ($on_markup_violation !== null) {
            $on_markup_violation($key, $violations);
        }

        return $this->markup_policy->sanitize($value);
    }
}
