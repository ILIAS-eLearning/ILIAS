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

require_once('vendor/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use ILIAS\Tests\Refinery\ilLanguageMock;
use ILIAS\Language\Language;

abstract class ilSystemStyleBaseFS extends TestCase
{
    protected ilSystemStyleConfigMock $system_style_config;
    protected ilSkinStyleContainer $container;
    protected ilSkinStyle $style;
    protected ?ILIAS\DI\Container $save_dic = null;
    protected Language $lng;
    protected ilSystemStyleMessageStack $message_stack;

    protected function setUp(): void
    {
        global $DIC;
        $DIC['tpl'] = $this->getMockBuilder(ilGlobalTemplateInterface::class)->getMock();
        $this->system_style_config = new ilSystemStyleConfigMock();
        $this->message_stack = new ilSystemStyleMessageStack($DIC['tpl']);

        /** @noRector */
        $this->lng = new ilLanguageMock();



        $factory = new ilSkinFactory($this->lng, $this->system_style_config);
        $this->container = $factory->skinStyleContainerFromId('skin1', $this->message_stack);
        $this->style = $this->container->getSkin()->getStyle('style1');
    }

    protected function getAllContentOfFolder(string $directory): string
    {
        $files = scandir($directory);
        $content = "";

        foreach ($files as $file) {
            if (is_file($directory . '/' . $file)) {
                $content .= file_get_contents($directory . '/' . $file);
            }
        }

        return $content;
    }
}
