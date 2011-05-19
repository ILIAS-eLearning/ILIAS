<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wiki HTML exporter class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup Modules/Wiki
 */
class ilWikiHTMLExport
{
	protected $wiki_gui;

	/**
	 * Constructir
	 *
	 * @param
	 * @return
	 */
	function __construct($a_wiki_gui)
	{
		$this->wiki_gui = $a_wiki_gui;
		$this->wiki = $a_wiki_gui->object;
	}

	/**
	 * Build export file
	 *
	 * @param
	 * @return
	 */
	function buildExportFile()
	{
		global $ilias;

		// create export file
		include_once("./Services/Export/classes/class.ilExport.php");
		ilExport::_createExportDirectory($this->wiki->getId(), "html", "wiki");
		$exp_dir =
			ilExport::_getExportDirectory($this->wiki->getId(), "html", "wiki");

		$this->subdir = $this->wiki->getType()."_".$this->wiki->getId();
		$this->export_dir = $exp_dir."/".$this->subdir;

		// initialize temporary target directory
		ilUtil::delDir($this->export_dir);
		ilUtil::makeDir($this->export_dir);

		$mob_dir = $this->export_dir."/mobs";
		ilUtil::makeDir($mob_dir);
		$file_dir = $this->export_dir."/files";
		ilUtil::makeDir($file_dir);
		$teximg_dir = $this->export_dir."/teximg";
		ilUtil::makeDir($teximg_dir);
		$style_dir = $this->export_dir."/style";
		ilUtil::makeDir($style_dir);
		$style_img_dir = $this->export_dir."/style/images";
		ilUtil::makeDir($style_img_dir);
		$content_style_dir = $this->export_dir."/content_style";
		ilUtil::makeDir($content_style_dir);
		$content_style_img_dir = $this->export_dir."/content_style/images";
		ilUtil::makeDir($content_style_img_dir);
		$GLOBALS["teximgcnt"] = 0;

		// export system style sheet
		$location_stylesheet = ilUtil::getStyleSheetLocation("filesystem");
		$style_name = $ilias->account->prefs["style"].".css";
		copy($location_stylesheet, $style_dir."/".$style_name);
		$fh = fopen($location_stylesheet, "r");
		$css = fread($fh, filesize($location_stylesheet));
		preg_match_all("/url\(([^\)]*)\)/",$css,$files);
		foreach (array_unique($files[1]) as $fileref)
		{
			$fileref = dirname($location_stylesheet)."/".$fileref;
			if (is_file($fileref))
			{
				copy($fileref, $style_img_dir."/".basename($fileref));
			}
		}
		fclose($fh);
		$location_stylesheet = ilUtil::getStyleSheetLocation();

		// export content style sheet
		if ($this->wiki->getStyleSheetId() < 1)
		{
			$cont_stylesheet = "./Services/COPage/css/content.css";

			$css = fread(fopen($cont_stylesheet,'r'),filesize($cont_stylesheet));
			preg_match_all("/url\(([^\)]*)\)/",$css,$files);
			foreach (array_unique($files[1]) as $fileref)
			{
				if (is_file(str_replace("..", ".", $fileref)))
				{
					copy(str_replace("..", ".", $fileref), $content_style_img_dir."/".basename($fileref));
				}
				$css = str_replace($fileref, "images/".basename($fileref),$css);
			}
			fwrite(fopen($content_style_dir."/content.css",'w'),$css);
		}
		else
		{
			$style = new ilObjStyleSheet($this->wiki->getStyleSheetId());
			$style->writeCSSFile($content_style_dir."/content.css", "images");
			$style->copyImagesToDir($content_style_img_dir);
		}

		// export syntax highlighting style
		$syn_stylesheet = ilObjStyleSheet::getSyntaxStylePath();
		copy($syn_stylesheet, $this->export_dir."/syntaxhighlight.css");

		// export pages
		$this->exportHTMLPages($lm_gui, $this->export_dir);

		// export all media objects
		$linked_mobs = array();
		foreach ($this->offline_mobs as $mob)
		{
			if (ilObject::_exists($mob) && ilObject::_lookupType($mob) == "mob")
			{
				$this->exportHTMLMOB($mob, $linked_mobs);
			}
		}
		$linked_mobs2 = array();				// mobs linked in link areas
		foreach ($linked_mobs as $mob)
		{
			if (ilObject::_exists($mob))
			{
				$this->exportHTMLMOB($mob, $linked_mobs2);
			}
		}

		// export all file objects
		foreach ($this->offline_files as $file)
		{
			$this->exportHTMLFile($this->export_dir, $file);
		}

		$image_dir = $this->export_dir."/images";
		ilUtil::makeDir($image_dir);
		ilUtil::makeDir($image_dir."/browser");
		copy(ilUtil::getImagePath("enlarge.gif", false, "filesystem"),
			$image_dir."/enlarge.gif");
		copy(ilUtil::getImagePath("browser/blank.gif", false, "filesystem"),
			$image_dir."/browser/plus.gif");
		copy(ilUtil::getImagePath("browser/blank.gif", false, "filesystem"),
			$image_dir."/browser/minus.gif");
		copy(ilUtil::getImagePath("browser/blank.gif", false, "filesystem"),
			$image_dir."/browser/blank.gif");
		copy(ilUtil::getImagePath("spacer.gif", false, "filesystem"),
			$image_dir."/spacer.gif");
		copy(ilUtil::getImagePath("icon_st.gif", false, "filesystem"),
			$image_dir."/icon_st.gif");
		copy(ilUtil::getImagePath("icon_pg.gif", false, "filesystem"),
			$image_dir."/icon_pg.gif");
		copy(ilUtil::getImagePath("icon_st_s.gif", false, "filesystem"),
			$image_dir."/icon_st_s.gif");
		copy(ilUtil::getImagePath("icon_pg_s.gif", false, "filesystem"),
			$image_dir."/icon_pg_s.gif");
		copy(ilUtil::getImagePath("icon_lm.gif", false, "filesystem"),
			$image_dir."/icon_lm.gif");
		copy(ilUtil::getImagePath("icon_lm_s.gif", false, "filesystem"),
			$image_dir."/icon_lm_s.gif");
		copy(ilUtil::getImagePath("nav_arr_L.gif", false, "filesystem"),
			$image_dir."/nav_arr_L.gif");
		copy(ilUtil::getImagePath("nav_arr_R.gif", false, "filesystem"),
			$image_dir."/nav_arr_R.gif");
		copy(ilUtil::getImagePath("browser/forceexp.gif", false, "filesystem"),
			$image_dir."/browser/forceexp.gif");

		copy(ilUtil::getImagePath("download.gif", false, "filesystem"),
			$image_dir."/download.gif");
		
		copy(ilUtil::getImagePath("icon_wiki_b.gif", false, "filesystem"),
			$image_dir."/icon_wiki_b.gif");

		// export flv/mp3 player
		$services_dir = $this->export_dir."/Services";
		ilUtil::makeDir($services_dir);
		$media_service_dir = $services_dir."/MediaObjects";
		ilUtil::makeDir($media_service_dir);
		$flv_dir = $media_service_dir."/flash_flv_player";
		ilUtil::makeDir($flv_dir);
		$mp3_dir = $media_service_dir."/flash_mp3_player";
		ilUtil::makeDir($mp3_dir);
		copy("./Services/MediaObjects/flash_flv_player/flvplayer.swf",
			$flv_dir."/flvplayer.swf");
		copy("./Services/MediaObjects/flash_mp3_player/mp3player.swf",
			$mp3_dir."/mp3player.swf");

		// accordion stuff
		ilUtil::makeDir($this->export_dir.'/js');
		ilUtil::makeDir($this->export_dir.'/js/yahoo');
		ilUtil::makeDir($this->export_dir.'/css');
		
		// page presentation js
		copy('./Services/JavaScript/js/Basic.js',$this->export_dir.'/js/Basic.js');

		
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		copy(ilYuiUtil::getLocalPath('yahoo/yahoo-min.js'), $this->export_dir.'/js/yahoo/yahoo-min.js');
		copy(ilYuiUtil::getLocalPath('yahoo-dom-event/yahoo-dom-event.js'), $this->export_dir.'/js/yahoo/yahoo-dom-event.js');
		copy(ilYuiUtil::getLocalPath('animation/animation-min.js'), $this->export_dir.'/js/yahoo/animation-min.js');
		copy('./Services/Accordion/js/accordion.js',$this->export_dir.'/js/accordion.js');
		copy('./Services/Accordion/css/accordion.css',$this->export_dir.'/css/accordion.css');
		
		// page presentation js
		copy('./Services/COPage/js/ilCOPagePres.js',$this->export_dir.'/js/ilCOPagePres.js');

		// zip everything
		if (true)
		{
			// zip it all
			$date = time();
			$zip_file = ilExport::_getExportDirectory($this->wiki->getId(), "html", "wiki").
				"/".$date."__".IL_INST_ID."__".
				$this->wiki->getType()."_".$this->wiki->getId().".zip";
//echo $this->export_dir; exit;
			ilUtil::zip($this->export_dir, $zip_file);
			ilUtil::delDir($this->export_dir);
		}
	}

	/**
	 * Export all pages
	 */
	function exportHTMLPages()
	{
		global $tpl, $ilBench, $ilLocator;

		$pages = ilWikiPage::getAllPages($this->wiki->getId());

		// iterate all wiki pages
		$mobs = array();
		$int_links = array();
		$this->offline_files = array();

		include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		foreach ($pages as $page)
		{
			if (ilPageObject::_exists("wpg", $page["id"]))
			{
				$this->exportPageHTML($page["id"]);

				// get all snippets of page
				/*
				$pcs = ilPageContentUsage::getUsagesOfPage($page["id"], $this->getType().":pg");
				foreach ($pcs as $pc)
				{
					if ($pc["type"] == "incl")
					{
						$incl_mobs = ilObjMediaObject::_getMobsOfObject("mep:pg", $pc["id"]);
						foreach($incl_mobs as $incl_mob)
						{
							$mobs[$incl_mob] = $incl_mob;
						}
					}
				}*/

				// get all media objects of page
				$pg_mobs = ilObjMediaObject::_getMobsOfObject("wpg:pg", $page["id"]);
				foreach($pg_mobs as $pg_mob)
				{
					$mobs[$pg_mob] = $pg_mob;
				}

				// get all internal links of page
/*
				$pg_links = ilInternalLink::_getTargetsOfSource($this->getType().":pg", $page["id"]);
				$int_links = array_merge($int_links, $pg_links);

				// get all files of page
				include_once("./Modules/File/classes/class.ilObjFile.php");
				$pg_files = ilObjFile::_getFilesOfObject($this->getType().":pg", $page["id"]);
				$this->offline_files = array_merge($this->offline_files, $pg_files);
*/
			}
		}
		$this->offline_mobs = $mobs;
		$this->offline_int_links = $int_links;
	}

	/**
	 * Export page html
	 */
	function exportPageHTML($a_page_id)
	{
		global $ilUser, $lng;

		// template workaround: reset of template
		$this->tpl = new ilTemplate("tpl.main.html", true, true);
		//$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

		
		// scripts needed
		$scripts = array("./js/yahoo/yahoo-min.js", "./js/yahoo/yahoo-dom-event.js",
			"./js/yahoo/container_core-min.js", "./js/yahoo/animation-min.js",
			"./js/Basic.js",
			"./js/ilOverlay.js", "./js/accordion.js", "./js/ilCOPagePres.js");
		foreach ($scripts as $script)
		{
			$this->tpl->setCurrentBlock("js_file");
			$this->tpl->setVariable("JS_FILE", $script);
			$this->tpl->parseCurrentBlock();
		}

		// css files needed
		$css_files = array("./css/accordion.css");
		foreach ($css_files as $css)
		{
			$this->tpl->setCurrentBlock("css_file");
			$this->tpl->setVariable("CSS_FILE", $css);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content_style/content.css");
		$this->tpl->parseCurrentBlock();
		$style_name = $ilUser->prefs["style"].".css";;
		$this->tpl->setVariable("LOCATION_STYLESHEET","./style/".$style_name);
		$this->tpl->getStandardTemplate();

		$file = $this->export_dir."/wpg_".$a_page_id.".html";

		// return if file is already existing
		if (@is_file($file))
		{
			return;
		}

		// page
		include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
		$wpg_gui = new ilWikiPageGUI($a_page_id);
		$wpg_gui->setOutputMode("offline");
		$wpg_gui->setPageToc($this->wiki->getPageToc());
		$page_content = $wpg_gui->showPage();

		// export template: page content
		$ep_tpl = new ilTemplate("tpl.export_page.html", true, true,
			"Modules/Wiki");
		$ep_tpl->setVariable("PAGE_CONTENT", $page_content);
		
		// export template: right content
		include_once("./Modules/Wiki/classes/class.ilWikiImportantPagesBlockGUI.php");
		$bl = new ilWikiImportantPagesBlockGUI();
		$ep_tpl->setVariable("RIGHT_CONTENT", $bl->getHTML(true));

		// workaround
		$this->tpl->setVariable("MAINMENU", "<div style='min-height:40px;'></div>");
		$this->tpl->setTitle($this->wiki->getTitle());
		$this->tpl->setTitleIcon("./images/icon_wiki_b.gif",
			$lng->txt("obj_wiki"));

		$this->tpl->setContent($ep_tpl->get());
		//$this->tpl->fillMainContent();
		$content = $this->tpl->get("DEFAULT", false, false, false,
			true, true, false);

		//echo htmlentities($content); exit;
		// open file
		if (!($fp = @fopen($file,"w+")))
		{
			die ("<b>Error</b>: Could not open \"".$file."\" for writing".
					" in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
		}

		// set file permissions
		chmod($file, 0770);

		// write xml data into the file
		fwrite($fp, $content);

		// close file
		fclose($fp);

		if ($this->wiki->getStartPage() == $wpg_gui->getPageObject()->getTitle())
		{
			copy($file, $this->export_dir."/index.html");
		}
	}


	/**
	 * Export media object to html
	 */
	function exportHTMLMOB($a_mob_id, &$a_linked_mobs)
	{
		global $tpl;

		$mob_dir = $this->export_dir."/mobs";

		$source_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$a_mob_id;
		if (@is_dir($source_dir))
		{
			ilUtil::makeDir($mob_dir."/mm_".$a_mob_id);
			ilUtil::rCopy($source_dir, $mob_dir."/mm_".$a_mob_id);
		}

/*		$tpl = new ilTemplate("tpl.main.html", true, true);
		$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
//		$_GET["obj_type"]  = "MediaObject";
//		$_GET["mob_id"]  = $a_mob_id;
//		$_GET["cmd"] = "";
$content =& $a_lm_gui->media();
		$file = $this->export_dir."/media_".$a_mob_id.".html";

		// open file
		if (!($fp = @fopen($file,"w+")))
		{
			die ("<b>Error</b>: Could not open \"".$file."\" for writing".
				" in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
		}
		chmod($file, 0770);
		fwrite($fp, $content);
		fclose($fp);*/

		// fullscreen
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mob_obj = new ilObjMediaObject($a_mob_id);
		if ($mob_obj->hasFullscreenItem())
		{
			$tpl = new ilTemplate("tpl.main.html", true, true);
			$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
			//$_GET["obj_type"]  = "";
			//$_GET["mob_id"]  = $a_mob_id;
			//$_GET["cmd"] = "fullscreen";
//			$content =& $a_lm_gui->fullscreen();
			$file = $this->export_dir."/fullscreen_".$a_mob_id.".html";

			// open file
			if (!($fp = @fopen($file,"w+")))
			{
				die ("<b>Error</b>: Could not open \"".$file."\" for writing".
					" in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
			}
			chmod($file, 0770);
			fwrite($fp, $content);
			fclose($fp);
		}
		$linked_mobs = $mob_obj->getLinkedMediaObjects();
		$a_linked_mobs = array_merge($a_linked_mobs, $linked_mobs);
	}

}
?>
