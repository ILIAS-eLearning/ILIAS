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

use ILIAS\Filesystem\Filesystem;

/**
 * Class ilSimpleSAMLphpConfigTemplateHandler
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilSimpleSAMLphpConfigTemplateHandler
{
    public function __construct(private readonly Filesystem $fs)
    {
    }

    /**
     * @param array $placeholders A key/value map where the key is the name of a placeholder, and the value is a primitive type or a callable
     */
    public function copy(string $sourcePath, string $destinationPath, array $placeholders = []): void
    {
        if (!$this->fs->has($destinationPath)) {
            $templateContents = file_get_contents($sourcePath);

            foreach ($placeholders as $placeholder => $value) {
                if (is_callable($value)) {
                    $value = $value();
                }

                $templateContents = str_replace('[[' . $placeholder . ']]', $value, $templateContents);
            }

            // Does not work because of .sec renaming of PHP files
            //$this->fs->put($destinationPath, $templateContents);
            file_put_contents(ilFileUtils::getDataDir() . '/' . $destinationPath, $templateContents);
        }
    }
}
