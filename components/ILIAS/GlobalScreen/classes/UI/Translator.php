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

namespace ILIAS\GlobalScreen_\UI;

use ILIAS\DI\Container;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Translator
{
    /**
     * @var string
     */
    private const LANG_PHP = 'lang.php';

    public function __construct(private readonly Container $dic)
    {
        $dic->language()->loadLanguageModule('gsfo');
    }

    public function translate(string $identifier, ?string $prefix = null): string
    {
        $key = $prefix !== null ? $prefix . '_' . $identifier : $identifier;

        return $this->dic->language()->txt($key);
    }

    private function internal(string $translation): string
    {
        if ($translation === '-' . $key . '-') {
            $file = __DIR__ . '/' . self::LANG_PHP;
            // touch($file);
            $current = (array) ((@include $file) ?? []);
            if (!isset($current[$key])) {
                $current[$key] = $key;
                file_put_contents($file, '<?php return ' . var_export($current, true) . ';');
            } else {
                $translation = $current[$key];
            }
        }
        return $translation;
    }
}
