<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Portfolio HTML exporter class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup Modules/Wiki
 */
class ilPortfolioHTMLExport
{
	protected $portfolio_gui;

	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_portfolio_gui, $a_object)
	{
		$this->portfolio_gui = $a_portfolio_gui;
		$this->object = $a_object;
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
		ilExport::_createExportDirectory($this->object->getId(), "html", "prtf");
		$exp_dir =
			ilExport::_getExportDirectory($this->object->getId(), "html", "prtf");

		$this->subdir = $this->object->getType()."_".$this->object->getId();
		$this->export_dir = $exp_dir."/".$this->subdir;

		// initialize temporary target directory
		ilUtil::delDir($this->export_dir);
		ilUtil::makeDir($this->export_dir);
		
		// system style html exporter
		include_once("./Services/Style/classes/class.ilSystemStyleHTMLExport.php");
		$this->sys_style_html_export = new ilSystemStyleHTMLExport($this->export_dir);
	    // $this->sys_style_html_export->addImage("icon_prtf_b.gif");
		$this->sys_style_html_export->export();

		// init co page html exporter
		include_once("./Services/COPage/classes/class.ilCOPageHTMLExport.php");
		$this->co_page_html_export = new ilCOPageHTMLExport($this->export_dir);
		/* $this->co_page_html_export->setContentStyleId(
			$this->object->getStyleSheetId()); */
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
			$zip_file = ilExport::_getExportDirectory($this->object->getId(), "html", "prtf").
				"/".$date."__".IL_INST_ID."__".
				$this->object->getType()."_".$this->object->getId().".zip";
			ilUtil::zip($this->export_dir, $zip_file);
			ilUtil::delDir($this->export_dir);
		}
		
		return $zip_file;
	}

	/**
	 * Export all pages
	 */
	function exportHTMLPages()
	{
		global $tpl, $ilBench, $ilLocator;

		require_once "Services/Portfolio/classes/class.ilPortfolioPage.php";
		$pages = ilPortfolioPage::getAllPages($this->object->getId());
		
		// single postings
		include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		foreach ($pages as $page)
		{
			if (ilPageObject::_exists("prtf", $page["id"]))
			{
				$this->exportPageHTML($page["id"]);
				$this->co_page_html_export->collectPageElements("prtf:pg", $page["id"]);
			}
		}
		$this->co_page_html_export->exportPageElements();
	}
	
	function writeExportFile($a_file, $a_content, array $a_tabs = null)
	{
		global $lng, $ilTabs;
		
		$this->tpl = $this->co_page_html_export->getPreparedMainTemplate();
		
		$this->tpl->getStandardTemplate();
		$file = $this->export_dir."/".$a_file;
		// return if file is already existing
		if (@is_file($file))
		{
			return;
		}
		
		// export template: page content
		$ep_tpl = new ilTemplate("tpl.export_page.html", true, true,
			"Services/Portfolio");
		$ep_tpl->setVariable("PAGE_CONTENT", $a_content);		
		
		// workaround
		$this->tpl->setVariable("MAINMENU", "<div style='min-height:40px;'></div>");
		$this->tpl->setTitle($this->object->getTitle());
		
		$ilTabs->clearTargets();
		if($a_tabs)
		{			
			foreach($tabs as $caption => $url)
			{
				$ilTabs->addTab($caption, $url);
			}
		}

		$this->tpl->setContent($ep_tpl->get());
		//$this->tpl->fillMainContent();
		$content = $this->tpl->get("DEFAULT", false, false, false,
			true, true, true);

		// open file
		if (!file_put_contents($file, $content))
		{
			die ("<b>Error</b>: Could not open \"".$file."\" for writing".
					" in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
		}

		// set file permissions
		chmod($file, 0770);
		
		return $file;
	}

	/**
	 * Export page html
	 */
	function exportPageHTML($a_post_id)
	{
		global $lng;
		
		// page
		include_once "Services/Portfolio/classes/class.ilPortfolioPageGUI.php";
		$pgui = new ilPortfolioPageGUI($this->object->getId(), $a_post_id);
		$pgui->setOutputMode("offline");
		$page_content = $pgui->showPage();
		
		$this->writeExportFile("blp_".$a_post_id.".html", $page_content);
	}
}

?>