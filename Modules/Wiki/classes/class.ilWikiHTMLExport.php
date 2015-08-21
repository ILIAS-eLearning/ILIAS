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
	protected $wiki;
	const MODE_DEFAULT = "html";
	const MODE_USER = "user_html";
	protected $mode = self::MODE_DEFAULT;

	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_wiki)
	{
		$this->wiki = $a_wiki;
	}
	
	/**
	 * Set mode 
	 *
	 * @param int $a_val MODE_DEFAULT|MODE_USER	
	 */
	function setMode($a_val)
	{
		$this->mode = $a_val;
	}
	
	/**
	 * Get mode 
	 *
	 * @return int MODE_DEFAULT|MODE_USER
	 */
	function getMode()
	{
		return $this->mode;
	}

	/**
	 * Build export file
	 *
	 * @param
	 * @return
	 */
	function buildExportFile()
	{
		if ($this->getMode() == self::MODE_USER)
		{
			global $ilDB, $ilUser;
			include_once("./Modules/Wiki/classes/class.ilWikiUserHTMLExport.php");
			$this->user_html_exp = new ilWikiUserHTMLExport($this->wiki, $ilDB, $ilUser);
		}

		$ascii_name = str_replace(" ", "_", ilUtil::getASCIIFilename($this->wiki->getTitle()));

		// create export file
		include_once("./Services/Export/classes/class.ilExport.php");
		ilExport::_createExportDirectory($this->wiki->getId(), $this->getMode(), "wiki");
		$exp_dir =
			ilExport::_getExportDirectory($this->wiki->getId(), $this->getMode(), "wiki");

		if ($this->getMode() == self::MODE_USER)
		{
			ilUtil::delDir($exp_dir, true);
		}

		if ($this->getMode() == self::MODE_USER)
		{
			$this->subdir = $ascii_name;
		}
		else
		{
			$this->subdir = $this->wiki->getType()."_".$this->wiki->getId();
		}
		$this->export_dir = $exp_dir."/".$this->subdir;
//echo "+".$this->export_dir."+";
		// initialize temporary target directory
		ilUtil::delDir($this->export_dir);
		ilUtil::makeDir($this->export_dir);
		
		// system style html exporter
		include_once("./Services/Style/classes/class.ilSystemStyleHTMLExport.php");
		$this->sys_style_html_export = new ilSystemStyleHTMLExport($this->export_dir);
		$this->sys_style_html_export->addImage("icon_wiki.svg");
		$this->sys_style_html_export->export();

		// init co page html exporter
		include_once("./Services/COPage/classes/class.ilCOPageHTMLExport.php");
		$this->co_page_html_export = new ilCOPageHTMLExport($this->export_dir);
		$this->co_page_html_export->setContentStyleId(
			$this->wiki->getStyleSheetId());
		$this->co_page_html_export->createDirectories();
		$this->co_page_html_export->exportStyles();
		$this->co_page_html_export->exportSupportScripts();

		// export pages
		$this->exportHTMLPages();

		$date = time();
		$zip_file_name = ($this->getMode() == self::MODE_USER)
			? $ascii_name.".zip"
			: $date."__".IL_INST_ID."__".$this->wiki->getType()."_".$this->wiki->getId().".zip";

		// zip everything
		if (true)
		{
			// zip it all
			$zip_file = ilExport::_getExportDirectory($this->wiki->getId(), $this->getMode(), "wiki").
				"/".$zip_file_name;
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

		include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$cnt = 0;
		foreach ($pages as $page)
		{
			if (ilWikiPage::_exists("wpg", $page["id"]))
			{
				$this->exportPageHTML($page["id"]);
				$this->co_page_html_export->collectPageElements("wpg:pg", $page["id"]);
			}
//sleep(2);
			if ($this->getMode() == self::MODE_USER)
			{
				$cnt++;
				$this->user_html_exp->updateStatus((int) (50 / count($pages) * $cnt) ,ilWikiUserHTMLExport::RUNNING);
			}

		}
		$this->co_page_html_export->exportPageElements($this->updateUserHTMLStatusForPageElements);
	}

	/**
	 * Callback for updating the export status during elements export (media objects, files, ...)
	 *
	 * @param
	 */
	function updateUserHTMLStatusForPageElements($a_total, $a_cnt)
	{
		if ($this->getMode() == self::MODE_USER)
		{
			$this->user_html_exp->updateStatus((int) 50 + (50 / count($a_total) * $a_cnt) ,ilWikiUserHTMLExport::RUNNING);
		}
	}


	/**
	 * Export page html
	 */
	function exportPageHTML($a_page_id)
	{
		global $ilUser, $lng, $ilTabs;

		$ilTabs->clearTargets();
		
		$this->tpl = $this->co_page_html_export->getPreparedMainTemplate();
		
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
//		$this->tpl->setVariable("MAINMENU", "<div style='min-height:40px;'></div>");
		$this->tpl->setVariable("MAINMENU", "");

		$this->tpl->setTitle($this->wiki->getTitle());
		$this->tpl->setTitleIcon("./images/icon_wiki.svg",
			$lng->txt("obj_wiki"));

		$this->tpl->setContent($ep_tpl->get());
		//$this->tpl->fillMainContent();
		$content = $this->tpl->get("DEFAULT", false, false, false,
			true, true, true);

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
	 * Get user export file
	 *
	 * @param
	 * @return
	 */
	function getUserExportFile()
	{
		include_once("./Services/Export/classes/class.ilExport.php");
		$exp_dir =
			ilExport::_getExportDirectory($this->wiki->getId(), $this->getMode(), "wiki");

		foreach (new DirectoryIterator($exp_dir) as $fileInfo)
		{
			if (pathinfo($fileInfo->getFilename(),PATHINFO_EXTENSION) == "zip")
			{
				return $exp_dir."/".$fileInfo->getFilename();
			}
		}
		return false;
	}


}
?>
