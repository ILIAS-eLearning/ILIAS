<?php

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

    protected function setUp() : void
    {
        $this->system_style_config = new ilSystemStyleConfigMock();

        if (!file_exists($this->system_style_config->test_skin_temp_path)) {
            mkdir($this->system_style_config->test_skin_temp_path);
        }
        $this->lng = new ilLanguageMock();

        $this->file_system = new ilFileSystemHelper($this->lng);
        $this->file_system->recursiveCopy(
            $this->system_style_config->test_skin_original_path,
            $this->system_style_config->test_skin_temp_path
        );

        $factory = new ilSkinFactory($this->lng, $this->system_style_config);
        $this->container = $factory->skinStyleContainerFromId('skin1');
        $this->style = $this->container->getSkin()->getStyle('style1');
    }

    protected function tearDown() : void
    {
        $this->file_system->recursiveRemoveDir($this->system_style_config->test_skin_temp_path);
    }
}
