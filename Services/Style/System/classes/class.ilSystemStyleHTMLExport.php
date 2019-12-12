<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * HTML export class for system styles
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesStyle
 */
class ilSystemStyleHTMLExport
{
    private $exp_dir = "";
    private $images = [];

    /**
     * Initialisation
     *
     * @param string $a_exp_dir export directory
     */
    public function __construct($a_exp_dir)
    {
        $this->exp_dir = $a_exp_dir;
        $this->style_dir = $a_exp_dir . "/style";
        $this->style_img_dir = $a_exp_dir . "/style/images";
        $this->img_dir = $a_exp_dir . "/images";
        $this->img_browser_dir = $a_exp_dir . "/images/browser";
        
        // add standard images
        $this->addImage("enlarge.svg");
        $this->addImage("browser/blank.png", "/browser/plus.png");
        $this->addImage("browser/blank.png", "/browser/minus.png");
        $this->addImage("browser/blank.png", "/browser/blank.png");
        $this->addImage("spacer.png");
        $this->addImage("icon_st.svg");
        $this->addImage("icon_pg.svg");
        $this->addImage("icon_lm.svg");
        $this->addImage("nav_arr_L.png");
        $this->addImage("nav_arr_R.png");
    }

    /**
     * Create directories
     */
    public function createDirectories()
    {
        ilUtil::makeDir($this->style_dir);
        ilUtil::makeDir($this->img_dir);
        ilUtil::makeDir($this->img_browser_dir);
    }
    
    /**
     * Add (icon) image to the list of images to be exported
     *
     * @param $a_file
     * @param string $a_exp_file_name
     */
    public function addImage($a_file, $a_exp_file_name = "")
    {
        $this->images[] = ["file" => $a_file,
            "exp_file_name" => $a_exp_file_name];
    }

    /**
     * Export
     */
    public function export()
    {
        global $ilUser;
        
        $this->createDirectories();

        // export system style sheet
        $location_stylesheet = ilUtil::getStyleSheetLocation("filesystem");
        foreach (
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(dirname($location_stylesheet), \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                mkdir($this->style_dir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $this->style_dir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }

        // export (icon) images
        foreach ($this->images as $im) {
            $from = $to = $im["file"];
            if ($im["exp_file_name"] != "") {
                $to = $im["exp_file_name"];
            }
            copy(
                ilUtil::getImagePath($from, false, "filesystem"),
                $this->img_dir . "/" . $to
            );
        }
    }
}
