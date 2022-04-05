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

require_once('libs/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

abstract class ilSystemStyleBaseFSTest extends TestCase
{
    protected ilSystemStyleConfigMock $system_style_config;
    protected ilSkinStyleContainer $container;
    protected ilSkinStyle $style;
    protected ilFileSystemHelper $file_system;
    protected ?ILIAS\DI\Container $save_dic = null;
    protected ilLanguage $lng;
    protected ilSystemStyleMessageStack $message_stack;
    
    protected function setUp() : void
    {
        global $DIC;
        $DIC['tpl'] = $this->getMockBuilder(ilGlobalTemplateInterface::class)->getMock();
        $this->system_style_config = new ilSystemStyleConfigMock();
        $this->message_stack = new ilSystemStyleMessageStack($DIC['tpl']);

        if (!file_exists($this->system_style_config->test_skin_temp_path)) {
            mkdir($this->system_style_config->test_skin_temp_path);
        }
        $this->lng = new ilLanguageMock();

        $this->file_system = new ilFileSystemHelper($this->lng, $this->message_stack);
        $this->file_system->recursiveCopy(
            $this->system_style_config->test_skin_original_path,
            $this->system_style_config->test_skin_temp_path
        );

        $factory = new ilSkinFactory($this->lng, $this->system_style_config);
        $this->container = $factory->skinStyleContainerFromId('skin1', $this->message_stack);
        $this->style = $this->container->getSkin()->getStyle('style1');
    }

    protected function tearDown() : void
    {
        $this->file_system->recursiveRemoveDir($this->system_style_config->test_skin_temp_path);
    }
}
