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

namespace ILIAS\Language\ComponentTranslation\Catalog;

use ErrorException;
use Gettext\Generator\MoGenerator;
use Gettext\Generator\PoGenerator;
use Gettext\Loader\MoLoader;
use Gettext\Loader\StrictPoLoader;
use Gettext\Translation;
use Gettext\Translations;
use ILIAS\Language\ComponentTranslation\PluralForms;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * An in-memory gettext catalog (header block plus messages) and the only place of the component that reads
 * or writes the gettext file formats (a `.mo` is read at runtime by native gettext, see NativeGettext).
 * A narrow adapter around gettext/gettext (Translations, StrictPoLoader, MoLoader, PoGenerator, MoGenerator): nothing
 * else imports the Gettext namespace, so a library change stays local to this class and TranslationEntry.
 * The library's Scanner is not used.
 *
 * What the adapter guarantees on top of the library:
 * - A `.po` is read with StrictPoLoader (GNU gettext rules; throws on broken syntax instead of guessing like
 *   PoLoader). Every failure, including PHP warnings/errors of the library, surfaces as RuntimeException.
 *   Comment text is not trimmed (only the space after the marker), a leading UTF-8 BOM is skipped, invalid
 *   UTF-8 or another charset in "Content-Type" is rejected (never converted), obsolete messages (`#~`) are
 *   dropped, and the same msgid with and without `msgctxt ""` is a duplicate (the library stores both under
 *   one id).
 * - The value "0": the generators treat a falsy translation as absent (empty `msgstr`, left out of the
 *   `.mo`), so both are fed "0\0": PoGenerator strips the NUL, the `.mo` holds the C string "0". A context
 *   or `msgid_plural` of "" or "0" cannot be represented; generating such a catalog throws.
 * - Header values are escaped for the `.po` (PoGenerator writes them verbatim).
 * - Plural messages in a `.mo` get exactly as many forms as the "Plural-Forms" header declares (missing ones
 *   empty, surplus dropped; a single form compiles as singular). One whose msgstr[0] is empty while another
 *   form is not would be silently dropped by MoGenerator, so generating throws.
 *
 * Difference to the GNU tools: a missing plural form cannot be told apart from an empty one.
 */
final class TranslationCatalog
{
    private const string UTF8_BOM = "\xEF\xBB\xBF";

    private Translations $translations;

    public function __construct()
    {
        $this->translations = Translations::create();
    }

    /** Independent of the original: Translations::__clone() copies every message and the headers. */
    public function __clone()
    {
        $this->translations = clone $this->translations;
    }

    /**
     * @throws RuntimeException if $file cannot be read or is not a valid `.po` file
     */
    public static function fromPoFile(string $file): self
    {
        $content = is_file($file) && is_readable($file) ? @file_get_contents($file) : false;
        if ($content === false) {
            throw new RuntimeException(sprintf('Could not read PO file "%s".', $file));
        }

        try {
            return self::fromPoString($content);
        } catch (RuntimeException $e) {
            throw new RuntimeException(sprintf('Invalid PO file "%s": %s', $file, $e->getMessage()), 0, $e);
        }
    }

    /**
     * @throws RuntimeException if $content is not valid `.po` data
     */
    public static function fromPoString(string $content): self
    {
        if (str_starts_with($content, self::UTF8_BOM)) {
            $content = substr($content, strlen(self::UTF8_BOM));
        }
        // values are used as UTF-8 as they are, never converted
        if (!mb_check_encoding($content, 'UTF-8')) {
            throw new RuntimeException('The content is not valid UTF-8.');
        }

        $translations = self::withLibraryErrorsAsExceptions(
            static function () use ($content): Translations {
                $loader = new StrictPoLoader();
                $loader->displayErrorLine = true;
                return $loader->loadString($content);
            }
        );
        foreach ($translations->getHeaders()->toArray() as $name => $value) {
            // setHeader() refuses a line break, so such a file could be read but never written back: it counts as broken
            if (strpbrk($value, "\r\n") !== false) {
                throw new RuntimeException(sprintf('The value of header "%s" contains a line break.', $name));
            }
        }
        $content_type = $translations->getHeaders()->get('Content-Type');
        if (
            $content_type !== null
            && preg_match('/charset\s*=\s*"?([^\s;"]+)/i', $content_type, $matches) === 1
            && !in_array(strtolower($matches[1]), ['utf-8', 'utf8'], true)
        ) {
            throw new RuntimeException(sprintf('Unsupported charset "%s" - only UTF-8 is supported.', $matches[1]));
        }
        foreach ($translations->getTranslations() as $translation) {
            if ($translation->isDisabled()) {
                $translations->remove($translation);
            }
        }

        $catalog = new self();
        $catalog->translations = $translations;

        return $catalog;
    }

    /**
     * Reads a `.mo` of toMoString() for reading only: the library drops empty plural forms and forms "0", so
     * later forms move up. Never compile the result again.
     *
     * @throws RuntimeException if $content is not valid `.mo` data
     */
    public static function fromMoString(string $content): self
    {
        $translations = self::withLibraryErrorsAsExceptions(
            static fn(): Translations => (new MoLoader())->loadString($content)
        );
        foreach ($translations->getTranslations() as $translation) {
            if ($translation->getTranslation() === "0\0") {
                $translation->translate('0');
            }
        }

        $catalog = new self();
        $catalog->translations = $translations;

        return $catalog;
    }

    /**
     * @return array<string, string> header name => value, sorted by name
     */
    public function getHeaders(): array
    {
        return $this->translations->getHeaders()->toArray();
    }

    public function getHeader(string $name): ?string
    {
        return $this->translations->getHeaders()->get($name);
    }

    /**
     * The library trims header values.
     *
     * @throws InvalidArgumentException for a value containing a line break: the `.po` header block
     *         is split at line breaks when read, so such a value could not be read back unchanged
     */
    public function setHeader(string $name, string $value): void
    {
        if (strpbrk($value, "\r\n") !== false) {
            throw new InvalidArgumentException(sprintf('The value of header "%s" must not contain a line break.', $name));
        }
        $this->translations->getHeaders()->set($name, $value);
    }

    public function find(?string $context, string $id): ?TranslationEntry
    {
        $translation = $this->translations->find($context, $id);

        return $translation === null ? null : TranslationEntry::fromGettext($translation);
    }

    /**
     * Adds $entry, replacing an entry with the same context and id. $entry stays the same message:
     * changing it afterward changes it in this catalog, too.
     */
    public function add(TranslationEntry $entry): void
    {
        $this->translations->add($entry->toGettext());
    }

    /**
     * add() at the position of $entry's id: before the first entry whose id sorts after it (strcmp(), contexts
     * ignored), at the end if there is none, so a catalog sorted by id stays sorted. An entry with the same
     * context and id is replaced in place, like add() does.
     */
    public function addInIdOrder(TranslationEntry $entry): void
    {
        $translation = $entry->toGettext();
        if ($this->translations->has($translation)) {
            $this->translations->add($translation);
            return;
        }
        $following = [];
        foreach ($this->translations->getTranslations() as $existing) {
            if ($following !== [] || strcmp($existing->getOriginal(), $translation->getOriginal()) > 0) {
                $following[] = $existing;
                $this->translations->remove($existing);
            }
        }
        $this->translations->add($translation);
        foreach ($following as $existing) {
            $this->translations->add($existing);
        }
    }

    /**
     * @return list<TranslationEntry> in the order they were added (or read)
     */
    public function getEntries(): array
    {
        return array_values(array_map(
            static fn(Translation $translation): TranslationEntry => TranslationEntry::fromGettext($translation),
            $this->translations->getTranslations()
        ));
    }

    /**
     * @throws RuntimeException if the catalog holds something the library cannot represent
     */
    public function toPoString(): string
    {
        $translations = $this->prepareForGenerator(false);
        $headers = $translations->getHeaders();
        foreach ($headers->toArray() as $name => $value) {
            $headers->set($name, substr(PoGenerator::encode($value), 1, -1));
        }

        return (new PoGenerator())->generateString($translations);
    }

    /**
     * Deterministic: the same catalog always compiles to the same bytes, so an existing `.mo` can be compared
     * with this output. Unlike `msgfmt`, "fuzzy" messages are compiled too (for converted legacy modules it only
     * marks a never reviewed value, usually the English fallback; omitting it would show "-identifier-").
     * Messages without a translation are left out, like `msgfmt` does.
     *
     * @throws RuntimeException if the catalog holds something the library cannot represent
     */
    public function toMoString(): string
    {
        return (new MoGenerator())->includeHeaders(true)->generateString($this->prepareForGenerator(true));
    }

    /**
     * A copy of the catalog the generators write correctly, see the class docblock.
     */
    private function prepareForGenerator(bool $for_mo): Translations
    {
        $translations = clone $this->translations;
        foreach ($translations as $translation) {
            if (
                in_array($translation->getContext(), ['', '0'], true)
                || in_array($translation->getPlural(), ['', '0'], true)
            ) {
                throw new RuntimeException(sprintf(
                    'The message "%s" has a context or plural id gettext/gettext cannot write.',
                    $translation->getOriginal()
                ));
            }
            if ($for_mo && $translation->getPlural() !== null) {
                self::prepareMoPluralForms($translations, $translation);
            }
            if ($translation->getTranslation() !== '0') {
                continue;
            }
            if ($for_mo && $translation->getPlural() !== null) {
                // In a plural message the NUL would separate an additional, empty plural form
                throw new RuntimeException(sprintf(
                    'The plural message "%s" has the singular translation "0", which gettext/gettext cannot compile.',
                    $translation->getOriginal()
                ));
            }
            $translation->translate("0\0");
        }

        return $translations;
    }

    /**
     * A plural message as MoGenerator needs it: exactly as many forms as PluralForms gives for the language (as
     * at runtime; Germanic rule for a missing or invalid header). A message with no form after msgstr[0] compiles
     * as singular. MoGenerator pads/cuts by the header it parses itself, so where that differs (invalid header,
     * e.g. a huge nplurals) the header of this copy is replaced by the rule actually used.
     *
     * @throws RuntimeException for a msgstr[0] that is empty while another form is not: MoGenerator
     *         would drop the whole message
     */
    private static function prepareMoPluralForms(Translations $translations, Translation $translation): void
    {
        $headers = $translations->getHeaders();
        $plural_forms = PluralForms::fromHeaderOrGermanic($headers->get('Plural-Forms'));
        $library_plural_form = $headers->getPluralForm();
        if (
            $headers->get('Plural-Forms') !== null
            && (!is_array($library_plural_form) || (int) $library_plural_form[0] !== $plural_forms->getCount())
        ) {
            $headers->set('Plural-Forms', $plural_forms->getHeader());
        }
        $surplus = $plural_forms->getCount() - 1;
        $translation->translatePlural(...array_pad(
            array_slice(array_values($translation->getPluralTranslations()), 0, $surplus),
            $surplus,
            ''
        ));
        if (($translation->getTranslation() ?? '') === '' && implode('', $translation->getPluralTranslations()) !== '') {
            throw new RuntimeException(sprintf(
                'The plural message "%s" has an empty msgstr[0] but other forms, which gettext/gettext cannot compile.',
                $translation->getOriginal()
            ));
        }
    }

    /**
     * Runs $load and turns everything the library raises (exceptions, errors and, via a temporary error handler,
     * warnings and notices) into a RuntimeException, so one catch tells "broken file" from "valid file".
     * Deprecations go to the previous handler: they concern the library on this PHP version, not the file.
     *
     * @param callable(): Translations $load
     */
    private static function withLibraryErrorsAsExceptions(callable $load): Translations
    {
        $previous_handler = null;
        $previous_handler = set_error_handler(
            static function (int $severity, string $message, string $file, int $line) use (&$previous_handler): bool {
                if (($severity & (E_DEPRECATED | E_USER_DEPRECATED)) === 0) {
                    throw new ErrorException($message, 0, $severity, $file, $line);
                }

                return $previous_handler !== null && $previous_handler($severity, $message, $file, $line) !== false;
            }
        );
        try {
            return $load();
        } catch (Throwable $t) {
            throw new RuntimeException($t->getMessage(), 0, $t);
        } finally {
            restore_error_handler();
        }
    }
}
