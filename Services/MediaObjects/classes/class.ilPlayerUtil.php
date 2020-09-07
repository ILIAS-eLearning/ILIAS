<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Audio/Video Player Utility
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilPlayerUtil
{
    /**
     * Get local path of jQuery file
     */
    public static function getLocalMediaElementJsPath()
    {
        return "./libs/bower/bower_components/mediaelement/build/mediaelement-and-player.min.js";
    }

    /**
     * Get local path of jQuery file
     */
    public static function getLocalMediaElementCssPath()
    {
        return "./libs/bower/bower_components/mediaelement/build/mediaelementplayer.min.css";
    }

    /**
     * Init mediaelement.js scripts
     */
    public static function initMediaElementJs($a_tpl = null)
    {
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
     * Get css file paths
     *
     * @param
     * @return
     */
    public static function getCssFilePaths()
    {
        return array(self::getLocalMediaElementCssPath());
    }
    
    /**
     * Get js file paths
     *
     * @param
     * @return
     */
    public static function getJsFilePaths()
    {
        return array(self::getLocalMediaElementJsPath());
    }
    

    /**
     * Get flash video player directory
     *
     * @return
     */
    public static function getFlashVideoPlayerDirectory()
    {
        return "libs/bower/bower_components/mediaelement/build";
    }
    
    
    /**
     * Get flash video player file name
     *
     * @return
     */
    public static function getFlashVideoPlayerFilename($a_fullpath = false)
    {
        $file = "flashmediaelement.swf";
        if ($a_fullpath) {
            return self::getFlashVideoPlayerDirectory() . "/" . $file;
        }
        return $file;
    }
    
    /**
     * Copy css files to target dir
     *
     * @param
     * @return
     */
    public static function copyPlayerFilesToTargetDirectory($a_target_dir)
    {
        ilUtil::rCopy(
            "./libs/bower/bower_components/mediaelement/build",
            $a_target_dir
        );
    }
}
