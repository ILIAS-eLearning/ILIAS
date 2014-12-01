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
	private $images = array();

	/**
	 * Initialisation
	 *
	 * @param string $a_exp_dir export directory
	 */
	function __construct($a_exp_dir)
	{
		$this->exp_dir = $a_exp_dir;
		$this->style_dir = $a_exp_dir."/style";
		$this->style_img_dir = $a_exp_dir."/style/images";
		$this->img_dir = $a_exp_dir."/images";
		$this->img_browser_dir = $a_exp_dir."/images/browser";
		
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
	function createDirectories()
	{
		ilUtil::makeDir($this->style_dir);
		ilUtil::makeDir($this->style_img_dir);
		ilUtil::makeDir($this->img_dir);
		ilUtil::makeDir($this->img_browser_dir);
	}
	
	/**
	 * Add (icon) image to the list of images to be exported
	 *
	 * @param
	 * @return
	 */
	function addImage($a_file, $a_exp_file_name = "")
	{
		$this->images[] = array("file" => $a_file,
			"exp_file_name" => $a_file);
	}
	
	/**
	 * Export
	 *
	 * @param
	 * @return
	 */
	function export()
	{
		global $ilUser;
		
		$this->createDirectories();
		
		// export system style sheet
		$location_stylesheet = ilUtil::getStyleSheetLocation("filesystem");
		$style_name = $ilUser->prefs["style"].".css";
		copy($location_stylesheet, $this->style_dir."/".$style_name);
		$fh = fopen($location_stylesheet, "r");
		$css = fread($fh, filesize($location_stylesheet));
		preg_match_all("/url\(([^\)]*)\)/",$css,$files);
		foreach (array_unique($files[1]) as $fileref)
		{
			$fileref = dirname($location_stylesheet)."/".$fileref;
			if (is_file($fileref))
			{
				copy($fileref, $this->style_img_dir."/".basename($fileref));
			}
		}
		fclose($fh);
		
		// export (icon) images
		foreach ($this->images as $im)
		{
			$from = $to = $im["file"];
			if ($im["exp_file_name"] != "")
			{
				$to = $im["exp_file_name"];
			}
			copy(ilUtil::getImagePath($from, false, "filesystem"),
				$this->img_dir."/".$to);
		}
	}

}

?>