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

namespace ILIAS\GlobalScreen\GUI\I18n;

use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslationsRepository;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal Please do not use outside GlobalScreen
 */
class Translator
{
    private bool $internal = false;
    private const LANG_PHP = 'lang.php';

    private MultiLanguage $multi_language;
    private string $prefix;

    public function __construct(
        private \ilLanguage $lng,
        ?TranslationsRepository $translations_repository = null,
        string ...$module
    ) {
        $this->prefix = $module[0] ?? '';
        foreach ($module as $m) {
            $this->lng->loadLanguageModule($m);
        }
        $this->multi_language = new MultiLanguage($translations_repository);
    }

    public function t(string $identifier, ?string $prefix = null, array $subsitutions = []): string
    {
        return $this->translate($identifier, $prefix, $subsitutions);
    }

    /**
     * @deprecated use t() instead
     */
    public function txt(string $identifier, array $subsitutions = []): string
    {
        return $this->translate($identifier, null, $subsitutions);
    }

    public function translate(string $identifier, ?string $prefix = null, array $subsitutions = []): string
    {
        $key = $prefix !== null ? $prefix . '_' . $identifier : $identifier;

        if (!empty($this->prefix)) {
            $key = $this->prefix . '_' . $key;
        }

        if ($subsitutions !== []) {
            return sprintf($this->internal($key, $this->lng->txt($key)), ...array_values($subsitutions));
        }

        return $this->internal($key, $this->lng->txt($key));
    }

    public function ml(): MultiLanguage
    {
        return $this->multi_language;
    }

    private function internal(string $key, string $translation): string
    {
        if (!$this->internal) {
            return $translation;
        }

        $file = __DIR__ . '/' . self::LANG_PHP;
        touch($file);
        $current = (array) ((@include $file) ?? []);
        $untranslated = ($translation === '-' . $key . '-') || ($translation === $key);

        // Missing
        if (!($current[$key] ?? false)) {
            $current[$key] = $translation = $key;
            asort($current);
            file_put_contents($file, '<?php return ' . var_export($current, true) . ';');
            return $translation;
        }

        if ($untranslated || ($current[$key] ?? $translation) !== $translation) {
            return $current[$key] ?? $translation;
        }

        return $translation;
    }

}
