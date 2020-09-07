<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * ilSystemStyleConfig wraps all 'constants' to ensure the testability of all classes using those 'constants'.
 * This class is injected in all other classes using this dependency.
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 *
 */
class ilSystemStyleConfig
{
    /**
     * Default skin ID in ILIAS
     *
     * @var string
     */
    protected $default_skin_id = "default";

    /**
     * Default system style ID in ILIAS
     *
     * @var string
     */
    protected $default_style_id = "delos";

    /**
     * Path to default template of ILIAS (skin default, style delos)
     *
     * @var string
     */
    protected $default_template_path = "./templates/default/template.xml";

    /**
     * Path to delos css and less files
     *
     * @var string
     */
    protected $delos_path = "./templates/default/delos";

    /**
     * Relative delos path from Customizing dir to delos css and less files
     *
     * @var string
     */
    protected $rel_delos_path = "../../../../templates/default/delos";


    /**
     * Path to variables less file of delos
     *
     * @var string
     */
    protected $default_variables_path = "./templates/default/less/variables.less";

    /**
     * Path to images directory of delos
     *
     * @var string
     */
    protected $default_images_path = "./templates/default/images/";

    /**
     * Path to fonts directory of delos
     *
     * @var string
     */
    protected $default_fonts_path = "./templates/default/fonts/";

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
    protected $customizing_skin_path = "./Customizing/global/skin/";

    /**
     * @return string
     */
    public function getDefaultSkinId()
    {
        return $this->default_skin_id;
    }

    /**
     * @param string $default_skin_id
     */
    public function setDefaultSkinId($default_skin_id)
    {
        $this->default_skin_id = $default_skin_id;
    }

    /**
     * @return string
     */
    public function getDefaultStyleId()
    {
        return $this->default_style_id;
    }

    /**
     * @param string $default_style_id
     */
    public function setDefaultStyleId($default_style_id)
    {
        $this->default_style_id = $default_style_id;
    }

    /**
     * @return string
     */
    public function getDefaultTemplatePath()
    {
        return $this->default_template_path;
    }

    /**
     * @param string $default_template_path
     */
    public function setDefaultTemplatePath($default_template_path)
    {
        $this->default_template_path = $default_template_path;
    }

    /**
     * @return string
     */
    public function getDelosPath()
    {
        return $this->delos_path;
    }

    /**
     * @param string $delos_path
     */
    public function setDelosPath($delos_path)
    {
        $this->delos_path = $delos_path;
    }

    /**
     * @return string
     */
    public function getDefaultVariablesPath()
    {
        return $this->default_variables_path;
    }

    /**
     * @param string $default_variables_path
     */
    public function setDefaultVariablesPath($default_variables_path)
    {
        $this->default_variables_path = $default_variables_path;
    }

    /**
     * @return string
     */
    public function getDefaultImagesPath()
    {
        return $this->default_images_path;
    }

    /**
     * @param string $default_images_path
     */
    public function setDefaultImagesPath($default_images_path)
    {
        $this->default_images_path = $default_images_path;
    }

    /**
     * @return string
     */
    public function getDefaultFontsPath()
    {
        return $this->default_fonts_path;
    }

    /**
     * @param string $default_fonts_path
     */
    public function setDefaultFontsPath($default_fonts_path)
    {
        $this->default_fonts_path = $default_fonts_path;
    }

    /**
     * @return string
     */
    public function getDefaultSoundsPath()
    {
        return $this->default_sounds_path;
    }

    /**
     * @param string $default_sounds_path
     */
    public function setDefaultSoundsPath($default_sounds_path)
    {
        $this->default_sounds_path = $default_sounds_path;
    }

    /**
     * @return mixed
     */
    public function getCustomizingSkinPath()
    {
        return $this->customizing_skin_path;
    }

    /**
     * @param mixed $customizing_skin_path
     */
    public function setCustomizingSkinPath($customizing_skin_path)
    {
        $this->customizing_skin_path = $customizing_skin_path;
    }

    /**
     * @return string
     */
    public function getRelDelosPath()
    {
        return $this->rel_delos_path;
    }

    /**
     * @param string $rel_delos_path
     */
    public function setRelDelosPath($rel_delos_path)
    {
        $this->rel_delos_path = $rel_delos_path;
    }
}
