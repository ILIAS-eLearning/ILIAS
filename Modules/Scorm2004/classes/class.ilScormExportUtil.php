<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Utility class for scorm export
 *
 * @author Alex Kiling <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesScorm2004
 */
class ilScormExportUtil
{
    /**
     * Export lm content css to a directory
     */
    public static function exportContentCSS($a_slm_object, $a_target_dir)
    {
        ilUtil::makeDir($a_target_dir . "/css");
        ilUtil::makeDir($a_target_dir . "/css/images");
        
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $active_css = ilObjStyleSheet::getContentStylePath($a_slm_object->getStyleSheetId());
        $active_css = explode('?', $active_css);
        $css = fread(fopen($active_css[0], 'r'), filesize($active_css[0]));
        preg_match_all("/url\(([^\)]*)\)/", $css, $files);
        $currdir = getcwd();
        chdir(dirname($active_css[0]));
        foreach (array_unique($files[1]) as $fileref) {
            if (is_file($fileref)) {
                copy($fileref, $a_target_dir . "/css/images/" . basename($fileref));
            }
            $css = str_replace($fileref, "images/" . basename($fileref), $css);
        }
        chdir($currdir);
        fwrite(fopen($a_target_dir . '/css/style.css', 'w'), $css);
    }
}
