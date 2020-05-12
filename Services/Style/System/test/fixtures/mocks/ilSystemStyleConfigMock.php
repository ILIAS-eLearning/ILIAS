<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleConfig.php");

/**
 * ilSystemStyleConfig wraps all 'constants' to ensure the testability of all classes using those 'constants'.
 * This class is injected in all other classes using this dependency.
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 *
 */
class ilSystemStyleConfigMock extends ilSystemStyleConfig
{
    /**
     * @var string
     */
    protected $default_skin_id = "defaultSkin";

    /**
     * @var string
     */
    protected $default_style_id = "defaultStyle";

    /**
     * @var string
     */
    protected $default_template_path = "./Services/Style/System/test/fixtures/skins_temp/defaultSkin/template.xml";

    /**
     * @var string
     */
    protected $delos_path = "./Services/Style/System/test/fixtures/skins_temp/defaultSkin/defaultStyle";

    /**
     * @var string
     */
    protected $rel_delos_path = "./Services/Style/System/test/fixtures/skins_temp/defaultSkin/defaultStyle";

    /**
     * @var string
     */
    protected $default_variables_path = "./Services/Style/System/test/fixtures/skins_temp/defaultSkin/less/variables.less";

    /**
     * Path to images directory of delos
     *
     * @var string
     */
    protected $default_images_path = "./Services/Style/System/test/fixtures/skins_temp/defaultSkin/images/";

    /**
     * Path to fonts directory of delos
     *
     * @var string
     */
    protected $default_fonts_path = "./Services/Style/System/test/fixtures/skins_temp/defaultSkin/fonts/";

    /**
     * Path to sounds directory of delos (currently none given)
     *
     * @var string
     */
    protected $default_sounds_path = "";

    /**
     * Customizing skin path to place folders for custom skins into
     *
     * string
     */
    protected $customizing_skin_path = "./Services/Style/System/test/fixtures/skins_temp/customSkins/";

    public $test_skin_original_path = "./Services/Style/System/test/fixtures/skins/";
    public $test_skin_temp_path = "./Services/Style/System/test/fixtures/skins_temp/";
}
