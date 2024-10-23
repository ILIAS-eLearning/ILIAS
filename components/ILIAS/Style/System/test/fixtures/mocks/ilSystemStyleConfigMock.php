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
    protected string $default_template_path = __DIR__ . '/../skins/defaultSkin/template.xml';
    protected string $delos_path = __DIR__ . '/../skins/defaultSkin/defaultStyle';
    protected string $rel_delos_path = __DIR__ . '/../skins/defaultSkin/defaultStyle';
    protected string $default_settings_path = __DIR__ . '/../skins/defaultSkin/010-settings';
    protected string $default_images_path = __DIR__ . '/../skins/defaultSkin/images/';
    protected string $default_fonts_path = __DIR__ . '/../skins/defaultSkin/fonts/';
    protected string $default_sounds_path = '';
    protected string $customizing_skin_path = __DIR__ . '/../skins/customSkins/';
    public string $test_skin_original_path = __DIR__ . '/../skins/';
    public string $test_skin_temp_path = __DIR__ . '/../skins/';
}
