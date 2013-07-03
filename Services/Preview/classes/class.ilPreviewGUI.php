<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Preview/classes/class.ilPreviewSettings.php");

/**
 * User interface class for previewing objects.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 * 
 * @package ServicesPreview
 */
class ilPreviewGUI
{
	private $ref_id = null;
	private $ctrl = null;
	private $lng = null;
	
	private static $initialized = false;
	
	/**
	 * Creates a new preview GUI.
	 */
	public function __construct($a_ref_id = null) 
	{
		global $ilCtrl, $lng;
		
        if ($a_ref_id == null && isset($_GET["ref_id"]))
            $this->ref_id = $_GET["ref_id"];
        else
			$this->ref_id = $a_ref_id;
		
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		
		ilPreviewGUI::initPreview();
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd("getPreviewHTML");
		$next_class = $this->ctrl->getNextClass($this);

		switch($next_class)
		{
			default:
				return $this->$cmd();
				break;
		}
	}
	
	
	public function getJSCall($a_html_id)
	{
		// build the url
		$link = "ilias.php?baseClass=ilPreviewGUI&ref_id={$this->ref_id}&cmdMode=asynch";
		return "il.Preview.toggle(event, '{$this->ref_id}', { htmlId: '#{$a_html_id}', url: '$link' });";	
	}
	
	public function getPreviewHTML()
	{
		global $ilAccess;
		
		require_once("./Services/Preview/classes/class.ilPreview.php");
		
		// load the template
		$tmpl = new ilTemplate("tpl.preview.html", true, true, "Services/Preview");
		
		// has read access?
		$obj_id = ilObject::_lookupObjId($this->ref_id);
		if ($ilAccess->checkAccess("read", "", $this->ref_id))
		{
			// get the preview
			$preview = new ilPreview($obj_id);
			
			// preview images available?
			$images = $preview->getImages();
			if (count($images) > 0)
			{
				foreach ($images as $image)
				{
					$tmpl->setCurrentBlock("preview_item");
					$tmpl->setVariable("IMG_URL", $image["url"]);
					$tmpl->setVariable("WIDTH", $image["width"]);
					$tmpl->setVariable("HEIGHT", $image["height"]);
					$tmpl->parseCurrentBlock();
				}
			}
			else
			{
				$tmpl->setCurrentBlock("no_preview");
				
				// set text depending on the status
				switch ($preview->getRenderStatus())
				{
					case ilPreview::RENDER_STATUS_PENDING:
						$tmpl->setVariable("TXT_NO_PREVIEW", $this->lng->txt("preview_status_pending"));
						break;
					
					case ilPreview::RENDER_STATUS_FAILED:
						$tmpl->setVariable("TXT_NO_PREVIEW", $this->lng->txt("preview_status_failed"));
						break;
					
					default:
						$tmpl->setVariable("TXT_NO_PREVIEW", $this->lng->txt("preview_status_missing"));
						break;				
				}
				
				$tmpl->parseCurrentBlock();
			}
		}
		else
		{
			// display error message
			$tmpl->setVariable("TXT_NO_PREVIEW", $this->lng->txt("no_access_item"));
		}
		
		// output
		if ($this->ctrl->isAsynch())
		{
			echo $tmpl->getAsynch();
			exit;
		}
		else
		{
			return $tmpl->get();
		}
	}
    
    public function getInlineHTML()
    {
		$tmpl = new ilTemplate("tpl.preview_inline.html", true, true, "Services/Preview");
        $tmpl->setVariable("PREVIEW", $this->getPreviewHTML());
        return $tmpl->get();
    }

	/**
	 * Initializes the preview and loads the needed javascripts and styles.
	 */
	private static function initPreview()
	{
		if (self::$initialized)
			return;
		
		global $tpl, $lng, $ilCtrl;
		
		// asynch call?
		if ($ilCtrl->isAsynch())
			return;
		
		// jquery
		include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		iljQueryUtil::initjQuery();
		
		// load qtip
		$tpl->addJavaScript("./Services/UIComponent/Tooltip/lib/qtip_2_0_1/jquery.qtip.min.js");
		$tpl->addCss("./Services/UIComponent/Tooltip/lib/qtip_2_0_1/jquery.qtip.min.css");
		
		// needed scripts & styles
		$tpl->addJavaScript("./Services/Preview/js/jquery.mousewheel.js");
		$tpl->addJavaScript("./Services/Preview/js/ilPreview.js");
		$tpl->addCss(ilUtil::getStyleSheetLocation("filesystem", "preview.css", "Services/Preview"));
		
		// add default texts and values
		$tpl->addOnLoadCode("il.Preview.texts.preview = \"" . $lng->txt("preview") . "\";");
		$tpl->addOnLoadCode("il.Preview.texts.loading = \"" . $lng->txt("preview_loading") . "\";");
		$tpl->addOnLoadCode("il.Preview.texts.close = \"" . $lng->txt("close") . "\";");
		$tpl->addOnLoadCode("il.Preview.previewSize  = " . ilPreviewSettings::getImageSize() . ";");
		$tpl->addOnLoadCode("il.Preview.init();");
		
		self::$initialized = true;
	}
}
?>