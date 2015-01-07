<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Portfolio HTML exporter class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ModulesPortfolio
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
		$exp_dir = ilExport::_getExportDirectory($this->object->getId(), "html", "prtf");

		$this->subdir = $this->object->getType()."_".$this->object->getId();
		$this->export_dir = $exp_dir."/".$this->subdir;

		// initialize temporary target directory
		ilUtil::delDir($this->export_dir);
		ilUtil::makeDir($this->export_dir);
		
		// system style html exporter
		include_once("./Services/Style/classes/class.ilSystemStyleHTMLExport.php");
		$this->sys_style_html_export = new ilSystemStyleHTMLExport($this->export_dir);
	    // $this->sys_style_html_export->addImage("icon_prtf.svg");
		$this->sys_style_html_export->export();

		// init co page html exporter
		include_once("./Services/COPage/classes/class.ilCOPageHTMLExport.php");
		$this->co_page_html_export = new ilCOPageHTMLExport($this->export_dir);
		$this->co_page_html_export->setContentStyleId($this->object->getStyleSheetId()); 
		$this->co_page_html_export->createDirectories();
		$this->co_page_html_export->exportStyles();
		$this->co_page_html_export->exportSupportScripts();
		
		// banner 
		$prfa_set = new ilSetting("prfa");
		if($prfa_set->get("banner"))
		{		
			$banner = $this->object->getImageFullPath();
			copy($banner, $this->export_dir."/".basename($banner));
		}
		// page element: profile picture
		$ppic = ilObjUser::_getPersonalPicturePath($this->object->getOwner(), "big", true, true);
		if($ppic)
		{
			$ppic = array_shift(explode("?", $ppic));
			copy($ppic, $this->export_dir."/".basename($ppic));
		}	
		// header image: profile picture
		$ppic = ilObjUser::_getPersonalPicturePath($this->object->getOwner(), "xsmall", true, true);
		if($ppic)
		{
			$ppic = array_shift(explode("?", $ppic));
			copy($ppic, $this->export_dir."/".basename($ppic));
		}	
		
		// export pages
		$this->exportHTMLPages();
		
		// add js/images/file to zip
		$images = $files = $js_files = array();
		foreach($this->export_material as $items)
		{
			$images = array_merge($images, $items["images"]);
			$files = array_merge($files, $items["files"]);
			$js_files = array_merge($js_files, $items["js"]);
		}
		foreach(array_unique($images) as $image)
		{
			if(is_file($image))
			{
				copy($image, $this->export_dir."/images/".basename($image));
			}
		}
		foreach(array_unique($js_files) as $js_file)
		{
			if(is_file($js_file))
			{
				copy($js_file, $this->export_dir."/js/".basename($js_file));
			}
		}
		foreach(array_unique($files) as $file)
		{
			if(is_file($file))
			{
				copy($file, $this->export_dir."/files/".basename($file));
			}
		}
		
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

		require_once "Modules/Portfolio/classes/class.ilPortfolioPage.php";
		$pages = ilPortfolioPage::getAllPages($this->object->getId());
			
		$this->tabs = array();
		foreach($pages as $page)
		{
			// substitute blog id with title
			if($page["type"] == ilPortfolioPage::TYPE_BLOG)
			{
				include_once "Modules/Blog/classes/class.ilObjBlog.php";
				$page["title"] = ilObjBlog::_lookupTitle((int)$page["title"]);
			}
			
			$this->tabs[$page["id"]] = $page["title"];
		}
				
		// for sub-pages, e.g. blog postings
		$tpl_callback = array($this, "buildExportTemplate");
		
		include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		include_once("./Modules/Portfolio/classes/class.ilPortfolioPage.php");
		$has_index = false;
		foreach ($pages as $page)
		{						
			if (ilPortfolioPage::_exists("prtf", $page["id"]))
			{
				$this->active_tab = "user_page_".$page["id"];
				
				if($page["type"] == ilPortfolioPage::TYPE_BLOG)
				{										
					$link_template = "prtf_".$page["id"]."_bl{TYPE}_{ID}.html";
					
					include_once "Modules/Blog/classes/class.ilObjBlogGUI.php";
					$blog = new ilObjBlogGUI((int)$page["title"], ilObject2GUI::WORKSPACE_OBJECT_ID);					
					$blog->exportHTMLPages($this->export_dir."/", $link_template, $tpl_callback, $this->co_page_html_export, "prtf_".$page["id"].".html");
				}
				else
				{
					$this->exportPageHTML($page["id"]);
					$this->co_page_html_export->collectPageElements("prtf:pg", $page["id"]);
				}
				
				 if(!$has_index)
				 {
					 copy($this->export_dir."/prtf_".$page["id"].".html", 
						$this->export_dir."/index.html");
					 $has_index = true;
				 }
			}
		}
		$this->co_page_html_export->exportPageElements();
	}
	
	function buildExportTemplate(array $a_js_files = null)
	{
		global $ilTabs;
		
		$this->tpl = $this->co_page_html_export->getPreparedMainTemplate();		
		$this->tpl->getStandardTemplate();
		$this->tpl->addOnLoadCode('il.Tooltip.init();', 3);
		
		// js files
		if(is_array($a_js_files))
		{						
			foreach($a_js_files as $js_file)
			{				
				$this->tpl->setCurrentBlock("js_file");
				$this->tpl->setVariable("JS_FILE", !stristr($js_file, "://")
					? "./js/".basename($js_file)
					: $js_file);
				$this->tpl->parseCurrentBlock();
			}		
		}
		
		// workaround
		$this->tpl->setVariable("MAINMENU", "<div style='min-height:40px;'></div>");
		$this->tpl->setTitle($this->object->getTitle());
		
		$ilTabs->clearTargets();
		if($this->tabs)
		{			
			foreach($this->tabs as $id => $caption)
			{
				$ilTabs->addTab("user_page_".$id, $caption, "prtf_".$id.".html");
			}
			
			$ilTabs->activateTab($this->active_tab);
		}
		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolioGUI.php";
		ilObjPortfolioGUI::renderFullscreenHeader($this->object, $this->tpl, $this->object->getOwner(), true);
		
		return $this->tpl;
	}
	
	function writeExportFile($a_file, $a_content, $a_onload = null, $a_js_files = null)
	{
		$file = $this->export_dir."/".$a_file;
		// return if file is already existing
		if (@is_file($file))
		{
			return;
		}
		
		// export template: page content
		$ep_tpl = new ilTemplate("tpl.export_page.html", true, true,
			"Modules/Portfolio");
		$ep_tpl->setVariable("PAGE_CONTENT", $a_content);		
		
		$this->buildExportTemplate($a_js_files);	
		$this->tpl->setContent($ep_tpl->get());
		
		if(is_array($a_onload))
		{
			foreach($a_onload as $item)
			{
				$this->tpl->addOnLoadCode($item);
			}
		}
				

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
		include_once "Modules/Portfolio/classes/class.ilPortfolioPageGUI.php";
		$pgui = new ilPortfolioPageGUI($this->object->getId(), $a_post_id);
		$pgui->setOutputMode("offline");
		$pgui->setFullscreenLink("fullscreen.html"); // #12930 - see page.xsl
		$page_content = $pgui->showPage();
		
		$material = $pgui->getExportMaterial();
		$this->export_material[] = $material;
		
		$this->writeExportFile("prtf_".$a_post_id.".html", $page_content, $pgui->getJsOnloadCode(), $material["js"]);
	}
}

?>