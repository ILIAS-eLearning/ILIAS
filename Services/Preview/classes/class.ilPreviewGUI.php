<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Preview/classes/class.ilPreviewSettings.php");
require_once("./Services/Preview/classes/class.ilPreview.php");

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
	private $node_id = null;
	private $obj_id = null;
	private $preview = null;
	private $access_handler = null;
	private $context = null;
	private $ctrl = null;
	private $lng = null;
	
	private static $initialized = false;

	const CONTEXT_REPOSITORY = 1;
	const CONTEXT_WORKSPACE = 2;
	
	/**
	 * Creates a new preview GUI.
	 * @param int $a_node_id The node id.
	 * @param int $a_context The context of the preview.
	 * @param int $a_obj_id The object id.
	 * @param object $a_access_handler The access handler to use.
	 */
	public function __construct($a_node_id = null, $a_context = self::CONTEXT_REPOSITORY, $a_obj_id = null, $a_access_handler = null) 
	{
		global $ilCtrl, $lng, $ilAccess;
		
		// if we are the base class, get the id's from the query string
		if (strtolower($_GET["baseClass"]) == "ilpreviewgui")
		{
			$this->node_id = (int)$_GET["node_id"];
			$this->context = (int)$_GET["context"];
            $a_obj_id = (int)$_GET['obj_id'];
		}
		else
		{
			$this->node_id = $a_node_id;
			$this->context = $a_context;
		}
		
		// assign values
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		
		// access handler NOT provided?
		if ($a_access_handler == null)
		{
			if ($this->context == self::CONTEXT_WORKSPACE)
			{
				include_once("./Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php");
				$a_access_handler = new ilWorkspaceAccessHandler();
			}
			else
			{
				$a_access_handler = $ilAccess;
			}
		}
		$this->access_handler = $a_access_handler;
		
		// object id NOT provided?
		if ($a_obj_id == null)
		{
			if ($this->context == self::CONTEXT_WORKSPACE)
				$a_obj_id = $this->access_handler->getTree()->lookupObjectId($this->node_id);
			else
				$a_obj_id = ilObject::_lookupObjId($this->node_id);
		}
		$this->obj_id = $a_obj_id;
		
		// create preview object
		$this->preview = new ilPreview($this->obj_id);
		
		// if the call is NOT async initialize our stuff
		if (!$ilCtrl->isAsynch())
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
	
	/**
	 * Gets the JavaScript code to show the preview.
	 * @param $a_html_id string The id of the HTML element that contains the preview.
	 * @return string The JavaScript code to show the preview.
	 */
	public function getJSCall($a_html_id)
	{
		$status = $this->preview->getRenderStatus();
		$command = $status == ilPreview::RENDER_STATUS_NONE ? "renderPreview" : "";
		$loading_text = self::jsonSafeString($this->lng->txt($status == ilPreview::RENDER_STATUS_NONE ? "preview_status_creating" : "preview_loading"));
		
		// build the url
		$link = $this->buildUrl($command);
		return "il.Preview.toggle(event, { id: '{$this->node_id}', htmlId: '{$a_html_id}', url: '$link', status: '$status', loadingText: '$loading_text' });";	
	}
	
	/**
	 * Gets the HTML that displays the preview.
	 * @return string The HTML that displays the preview.
	 */
	public function getPreviewHTML()
	{
		// load the template
		$tmpl = new ilTemplate("tpl.preview.html", true, true, "Services/Preview");
		$tmpl->setVariable("PREVIEW_ID", $this->getHtmlId());
		
		// check for read access and get object id
		$preview_status = $this->preview->getRenderStatus();
		
		// has read access?
		if ($this->access_handler->checkAccess("read", "", $this->node_id))
		{
			// preview images available?
			$images = $this->preview->getImages();
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
				// set text depending on the status
				$tmpl->setCurrentBlock("no_preview");
				switch ($preview_status)
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
			include_once("./Services/JSON/classes/class.ilJsonUtil.php");
			
			$response = new stdClass();	
			$response->html = $tmpl->get();
			$response->status = $preview_status;
			
			// send response object (don't use 'application/json' as IE wants to download it!)
			header('Vary: Accept');
			header('Content-type: text/plain');
			echo ilJsonUtil::encode($response);

			// no further processing!
			exit;
		}
		else
		{
			return $tmpl->get();
		}
	}
    
    /**
     * Gets the HTML that is used for displaying the preview inline.
	 * @return string The HTML that is used for displaying the preview inline.
     */
    public function getInlineHTML()
    {
		$tmpl = new ilTemplate("tpl.preview_inline.html", true, true, "Services/Preview");
        $tmpl->setVariable("PREVIEW", $this->getPreviewHTML());
		
		// rendering allowed?
		if ($this->access_handler->checkAccess("read", "", $this->node_id))
		{
			$this->renderCommand(
				$tmpl, 
				"render", 
				"preview_create", 
				"preview_status_creating",
				array(ilPreview::RENDER_STATUS_NONE, ilPreview::RENDER_STATUS_FAILED));
		}
			
		// delete allowed?
		if ($this->access_handler->checkAccess("write", "", $this->node_id))
		{
			$this->renderCommand(
				$tmpl, 
				"delete", 
				"preview_delete", 
				"preview_status_deleting",
				array(ilPreview::RENDER_STATUS_CREATED));
		}
		
        return $tmpl->get();
    }
	
	/**
	 * Renders a command to the specified template.
	 * @param $tmpl object The template.
	 * @param $a_cmd string The command to create.
	 * @param $btn_topic string The topic to get the button text.
	 * @param $loading_topic string The topic to get the loading text.
	 * @param $a_display_status array An array containing the statuses when the command should be visible.
	 */
	private function renderCommand($tmpl, $a_cmd, $btn_topic, $loading_topic, $a_display_status)
	{
		$preview_html_id = $this->getHtmlId();
		$preview_status = $this->preview->getRenderStatus();
		$loading_text = self::jsonSafeString($this->lng->txt($loading_topic));
		
		$link = $this->buildUrl($a_cmd . "Preview");
		$script_args = "event, { id: '{$this->node_id}', htmlId: '$preview_html_id', url: '$link', loadingText: '$loading_text' }";
		
		$action_class = "";
		if (!is_array($a_display_status) || !in_array($preview_status, $a_display_status))
			$action_class = "ilPreviewActionHidden";
		
		$tmpl->setCurrentBlock("preview_action");
		$tmpl->setVariable("CLICK_ACTION", "il.Preview.$a_cmd($script_args);");
		$tmpl->setVariable("ACTION_CLASS", "$action_class");
		$tmpl->setVariable("ACTION_ID", "preview_{$a_cmd}_" . $this->node_id);
		$tmpl->setVariable("TXT_ACTION", $this->lng->txt($btn_topic));
		$tmpl->parseCurrentBlock();
	}
	
	/**
	 * Renders the preview and returns the HTML code that displays the preview.
	 * @return string The HTML code that displays the preview.
	 */
	public function renderPreview()
	{
		// has read access?
		if ($this->access_handler->checkAccess("read", "", $this->node_id))
		{
			// get the object
			$obj = ilObjectFactory::getInstanceByObjId($this->obj_id);
			$this->preview->create($obj);
		}

		return $this->getPreviewHTML();
	}
	
	/**
	 * Deletes the preview and returns the HTML code that displays the preview.
	 * @return string The HTML code that displays the preview.
	 */
	public function deletePreview()
	{
		// has read access?
		if ($this->access_handler->checkAccess("write", "", $this->node_id))
		{
			// get the preview
			require_once("./Services/Preview/classes/class.ilPreview.php");
			$this->preview->delete();
		}

		return $this->getPreviewHTML();
	}
	
	/**
	 * Gets the HTML id for the preview.
	 * @return string The HTML id to use for the preview.
	 */
	private function getHtmlId()
	{
		return "preview_" . $this->node_id;	
	}
	
	/**
	 * Builds the URL to call the preview GUI.
	 * @param $a_cmd string The command to call.
	 * @param $a_async bool true, to create a URL to call asynchronous; otherwise, false.
	 * @return string The created URL.
	 */
	private function buildUrl($a_cmd = "", $a_async = true)
	{
		$link = "ilias.php?baseClass=ilPreviewGUI&node_id={$this->node_id}&context={$this->context}&obj_id={$this->obj_id}";
		
		if ($a_async)
			$link .= "&cmdMode=asynch";
		
		if (!empty($a_cmd))
			$link .= "&cmd=$a_cmd";
		
		return $link;
	}

	/**
	 * Initializes the preview and loads the needed javascripts and styles.
	 */
	private static function initPreview()
	{
		if (self::$initialized)
			return;
		
		global $tpl, $lng, $ilCtrl;
		
		
		// jquery
		include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		iljQueryUtil::initjQuery();
		
		// load qtip
		include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
		ilTooltipGUI::initLibrary();
		
		// needed scripts & styles
		$tpl->addJavaScript("./Services/Preview/js/jquery.mousewheel.js");
		$tpl->addJavaScript("./Services/Preview/js/ilPreview.js");
		$tpl->addCss(ilUtil::getStyleSheetLocation("filesystem", "preview.css", "Services/Preview"));
		
		// create loading template
		$tmpl = new ilTemplate("tpl.preview.html", true, true, "Services/Preview");
		$tmpl->setCurrentBlock("no_preview");
		$tmpl->setVariable("TXT_NO_PREVIEW", "%%0%%");
		$tmpl->parseCurrentBlock();
		
		$initialHtml = str_replace(array("\r\n", "\r"), "\n", $tmpl->get());
		$lines = explode("\n", $initialHtml);
		$new_lines = array();
		foreach ($lines as $i => $line) 
		{
			if(!empty($line))
				$new_lines[] = trim($line);
		}
		$initialHtml = implode($new_lines);		
		
		// add default texts and values
		include_once("./Services/JSON/classes/class.ilJsonUtil.php");
		$tpl->addOnLoadCode("il.Preview.texts.preview = \"" . self::jsonSafeString($lng->txt("preview")) . "\";");
		$tpl->addOnLoadCode("il.Preview.texts.showPreview = \"" . self::jsonSafeString($lng->txt("preview_show")) . "\";");
		$tpl->addOnLoadCode("il.Preview.texts.close = \"" . self::jsonSafeString($lng->txt("close")) . "\";");
		$tpl->addOnLoadCode("il.Preview.previewSize = " . ilPreviewSettings::getImageSize() . ";");
		$tpl->addOnLoadCode("il.Preview.initialHtml = " . ilJsonUtil::encode($initialHtml) . ";");
		$tpl->addOnLoadCode("il.Preview.highlightClass = \"ilContainerListItemOuterHighlight\";");
		$tpl->addOnLoadCode("il.Preview.init();");
		
		self::$initialized = true;
	}
	
	/**
	 * Makes the specified string safe for JSON.
	 * 
	 * @param string $text The text to make JSON safe.
	 * @return The JSON safe text.
	 */
	private static function jsonSafeString($text)
	{
		if (!is_string($text))
			return $text;
		
		$text = htmlentities($text, ENT_COMPAT | ENT_HTML401, "UTF-8");
		$text = str_replace("'", "&#039;", $text);
		return $text;
	}
}
?>