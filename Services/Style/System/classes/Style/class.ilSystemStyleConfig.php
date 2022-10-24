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

/**
 * ilSystemStyleConfig wraps all 'constants' to ensure the testability of all classes using those 'constants'.
 * This class is injected in all other classes using this dependency.
 */
class ilSystemStyleConfig
{
    /**
     * Default skin ID in ILIAS
     */
    protected string $default_skin_id = 'default';

    /**
     * Default system style ID in ILIAS
     */
    protected string $default_style_id = 'delos';

    /**
     * Path to default template of ILIAS (skin default, style delos)
     */
    protected string $default_template_path = './templates/default/template.xml';

    /**
     * Path to delos css and less files
     */
    protected string $delos_path = './templates/default/delos';

    /**
     * Relative delos path from Customizing dir to delos css and less files
     */
    protected string $rel_delos_path = '../../../../templates/default/delos';


    /**
     * Path to variables less file of delos
     */
    protected string $default_variables_path = './templates/default/less/variables.less';

    /**
     * Path to images directory of delos
     */
    protected string $default_images_path = './templates/default/images/';

    /**
     * Path to fonts directory of delos
     */
    protected string $default_fonts_path = './templates/default/fonts/';

    /**
     * Path to sounds directory of delos (currently none given)
     */
    protected string $default_sounds_path = '';

    /**
     * Customizing skin path to place folders for custom skins into
     */
    protected string $customizing_skin_path = './Customizing/global/skin/';

    public function getDefaultSkinId(): string
    {
        return $this->default_skin_id;
    }

    public function setDefaultSkinId(string $default_skin_id): void
    {
        $this->default_skin_id = $default_skin_id;
    }

    public function getDefaultStyleId(): string
    {
        return $this->default_style_id;
    }

    public function setDefaultStyleId(string $default_style_id): void
    {
        $this->default_style_id = $default_style_id;
    }

    public function getDefaultTemplatePath(): string
    {
        return $this->default_template_path;
    }

    public function setDefaultTemplatePath(string $default_template_path): void
    {
        $this->default_template_path = $default_template_path;
    }

    public function getDelosPath(): string
    {
        return $this->delos_path;
    }

    public function setDelosPath(string $delos_path): void
    {
        $this->delos_path = $delos_path;
    }

    public function getDefaultVariablesPath(): string
    {
        return $this->default_variables_path;
    }

    public function setDefaultVariablesPath(string $default_variables_path): void
    {
        $this->default_variables_path = $default_variables_path;
    }

    public function getDefaultImagesPath(): string
    {
        return $this->default_images_path;
    }

    public function setDefaultImagesPath(string $default_images_path): void
    {
        $this->default_images_path = $default_images_path;
    }

    public function getDefaultFontsPath(): string
    {
        return $this->default_fonts_path;
    }

    public function setDefaultFontsPath(string $default_fonts_path): void
    {
        $this->default_fonts_path = $default_fonts_path;
    }

    public function getDefaultSoundsPath(): string
    {
        return $this->default_sounds_path;
    }

    public function setDefaultSoundsPath(string $default_sounds_path): void
    {
        $this->default_sounds_path = $default_sounds_path;
    }

    public function getCustomizingSkinPath(): string
    {
        return $this->customizing_skin_path;
    }

    public function getRelDelosPath(): string
    {
        return $this->rel_delos_path;
    }

    public function setRelDelosPath(string $rel_delos_path): void
    {
        $this->rel_delos_path = $rel_delos_path;
    }
}
