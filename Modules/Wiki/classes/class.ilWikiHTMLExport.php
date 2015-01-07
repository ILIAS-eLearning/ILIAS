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

		// zip everything
		if (true)
		{
			// zip it all
			$date = time();
			$zip_file = ilExport::_getExportDirectory($this->wiki->getId(), "html", "wiki").
				"/".$date."__".IL_INST_ID."__".
				$this->wiki->getType()."_".$this->wiki->getId().".zip";
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
		foreach ($pages as $page)
		{
			if (ilWikiPage::_exists("wpg", $page["id"]))
			{
				$this->exportPageHTML($page["id"]);
				$this->co_page_html_export->collectPageElements("wpg:pg", $page["id"]);
			}
		}
		$this->co_page_html_export->exportPageElements();
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



}
?>
