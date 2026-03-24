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

namespace ILIAS\Component\Dependencies\Mocks;

/**
 * @internal This class can only be used in Bootstrap
 */
final class FileLightMockBuilder extends AbstractLightMockBuilder implements MockBuilder
{
    private const string GENERATED_CLASSES_DIR = './artifacts/mocks';

    protected function loadGeneratedCode(string $code, string $generated_class): void
    {
        if (!mkdir(self::GENERATED_CLASSES_DIR, 0777, true) && !is_dir(self::GENERATED_CLASSES_DIR)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', 'generated_classes'));
        }
        $str = self::GENERATED_CLASSES_DIR . '/' . $generated_class . '.php';
        file_put_contents($str, "<?php\n\n" . $code);
        require_once $str;
    }

}
