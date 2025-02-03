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

/**
 * Audio/Video Player Utility
 *
 * @author Alexander Killing <killing@leifos.de>
 * @deprecated use KS components instead
 */
class ilPlayerUtil
{
    protected static function getLocalMediaElementJsPath(): array
    {
        return [
            "./assets/js/mediaelement-and-player.min.js",
            "./assets/js/vimeo.min.js"
        ];
    }

    /**
     * Get local path of jQuery file
     */
    public static function getLocalMediaElementCssPath(): string
    {
        return "./assets/css/mediaelementplayer.min.css";
    }

    /**
     * Init mediaelement.js scripts
     */
    public static function initMediaElementJs(
        ?ilGlobalTemplateInterface $a_tpl = null
    ): void {
        global $DIC;

        $tpl = $DIC["tpl"];

        if ($a_tpl == null) {
            $a_tpl = $tpl;
        }

        foreach (self::getJsFilePaths() as $js_path) {
            $a_tpl->addJavaScript($js_path);
        }
        foreach (self::getCssFilePaths() as $css_path) {
            $a_tpl->addCss($css_path);
        }
    }

    /**
     * @return string[]
     */
    public static function getCssFilePaths(): array
    {
        return array(self::getLocalMediaElementCssPath());
    }

    /**
     * @return string[]
     */
    public static function getJsFilePaths(): array
    {
        return self::getLocalMediaElementJsPath();
    }

    public static function getMediaPlayerDirectory(): string
    {
        return "public/node_modules/mediaelement/build";
    }

    public static function copyPlayerFilesToTargetDirectory(
        string $a_target_dir
    ): void {
        ilFileUtils::rCopy(
            "./node_modules/mediaelement/build",
            $a_target_dir
        );
    }
}
