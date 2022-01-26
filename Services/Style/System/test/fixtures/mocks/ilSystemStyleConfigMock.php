<?php

declare(strict_types=1);

/**
 * ilSystemStyleConfig wraps all 'constants' to ensure the testability of all classes using those 'constants'.
 * This class is injected in all other classes using this dependency.
 */
class ilSystemStyleConfigMock extends ilSystemStyleConfig
{
    protected string $default_skin_id = 'defaultSkin';
    protected string $default_style_id = 'defaultStyle';
    protected string $default_template_path = './Services/Style/System/test/fixtures/skins_temp/defaultSkin/template.xml';
    protected string $delos_path = './Services/Style/System/test/fixtures/skins_temp/defaultSkin/defaultStyle';
    protected string $rel_delos_path = './Services/Style/System/test/fixtures/skins_temp/defaultSkin/defaultStyle';
    protected string $default_variables_path = './Services/Style/System/test/fixtures/skins_temp/defaultSkin/less/variables.less';
    protected string $default_images_path = './Services/Style/System/test/fixtures/skins_temp/defaultSkin/images/';
    protected string $default_fonts_path = './Services/Style/System/test/fixtures/skins_temp/defaultSkin/fonts/';
    protected string $default_sounds_path = '';
    protected string $customizing_skin_path = './Services/Style/System/test/fixtures/skins_temp/customSkins/';
    public string $test_skin_original_path = './Services/Style/System/test/fixtures/skins/';
    public string $test_skin_temp_path = './Services/Style/System/test/fixtures/skins_temp/';
}
