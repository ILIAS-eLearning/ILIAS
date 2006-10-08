<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* GUI class to export test results as PDF certificates
*
* This class defines the GUI to export test results as PDF
* certificates using XML-FO techniques
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.ilTestCertificateGUI.php
* @modulegroup   Assessment
*/
class ilTestCertificateGUI
{
	/**
	* Question id
	*
	* A unique question id
	*
	* @var integer
	*/
	var $object;
	/**
	* The reference to the ILIAS control class
	*
	* The reference to the ILIAS control class
	*
	* @var ctrl
	*/
	var $ctrl;
	/**
	* The reference to the ILIAS tree class
	*
	* The reference to the ILIAS tree class
	*
	* @var ctrl
	*/
	var $tree;
	/**
	* The reference to the ILIAS class
	*
	* The reference to the ILIAS class
	*
	* @var object
	*/
	var $ilias;

	/**
	* The reference to the Template class
	*
	* The reference to the Template class
	*
	* @var object
	*/
	var $tpl;

	/**
	* The reference to the Language class
	*
	* The reference to the Language class
	*
	* @var object
	*/
	var $lng;

	/**
	* ilTestCertificateGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of 
	* the ilTestCertificateGUI object.
	*
	* @param object $a_object A reference to the test container object
	* @access public
	*/
	function ilTestCertificateGUI(&$a_object)
	{
		global $lng, $tpl, $ilCtrl, $ilias, $tree;

		include_once "./assessment/classes/class.ilTestCertificate.php";
		$this->object = new ilTestCertificate($a_object);
    $this->lng =& $lng;
    $this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ilias =& $ilias;
		$this->tree =& $tree;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->getCommand($cmd);
		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}
		return $ret;
	}

/**
* Retrieves the ilCtrl command
*
* Retrieves the ilCtrl command
*
* @access public
*/
	function getCommand($cmd)
	{
		return $cmd;
	}
	
/**
* Creates a certificate preview
*
* Creates a certificate preview
*
* @access public
*/
	function certificatePreview()
	{
		$this->object->createPreview();
		$this->certificateEditor();
	}

/**
* Removes the background image of a certificate
*
* Removes the background image of a certificate
*
* @access public
*/
	function certificateRemoveBackground()
	{
		$this->object->deleteBackgroundImage();
		$this->certificateEditor();
	}
	
	function getFormFieldsFromPOST()
	{
		$form_fields = array(
			"pageformat" => $_POST["pageformat"],
			"padding_top" => $_POST["padding_top"],
			"margin_body" => $_POST["margin_body"],
			"certificate_text" => $_POST["certificate_text"]
		);
		return $form_fields;
	}

/**
* Deletes the certificate and all it's data
*
* Deletes the certificate and all it's data
*
* @access public
*/
	function certificateDelete()
	{
		$this->object->deleteCertificate();
		$this->ctrl->redirect($this, "certificateEditor");
	}
	
/**
* Saves the certificate
*
* Saves the certificate
*
* @access public
*/
	function certificateSave()
	{
		$this->certificateEditor();
	}

/**
* Uploads the certificate
*
* Uploads the certificate
*
* @access public
*/
	function certificateUpload()
	{
		$this->certificateEditor();
	}

/**
* Shows the certificate editor for ILIAS tests
*
* Shows the certificate editor for ILIAS tests
*
* @access public
*/
	function certificateEditor()
	{
		$form_fields = array();
		if (is_array($_POST))
		{
			if (count($_POST) > 0)
			{
				// handle the form post
				
				// handle the file upload
				if (strlen($_FILES["background"]["tmp_name"]))
				{
					$result = $this->object->uploadBackgroundImage($_FILES["background"]["tmp_name"]);
					if ($result == FALSE)
					{
						sendInfo($this->lng->txt("certificate_error_upload_bgimage"));
					}
				}
				$form_fields = $this->getFormFieldsFromPOST();
			}
			else
			{
				$form_fields = $this->object->processFO2XHTML();
			}
		}

		if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0)
		{
			// try to save the certificate to an XSL-FO document
			
			// 1. run checks on all input fields
			$result = $this->object->checkCertificateInput($form_fields);
			if ($result !== TRUE)
			{
				sendInfo($result);
			}
			else
			{
				$xslfo = $this->object->processXHTML2FO($form_fields);
				$fh = @fopen($this->object->getXSLPath(), "w");
				@fwrite($fh, $xslfo);
				@fclose($fh);
			}
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_certificate_edit.html", true);

		if ($this->object->hasBackgroundImage())
		{
			$this->tpl->setCurrentBlock("background_exists");
			$this->tpl->setVariable("BACKGROUND_THUMBNAIL", $this->object->getBackgroundImagePathWeb() . ".thumb.jpg");
			$this->tpl->setVariable("THUMBNAIL_ALT", $this->lng->txt("preview"));
			$this->tpl->setVariable("DELETE_BUTTON", $this->lng->txt("delete"));
			$this->tpl->parseCurrentBlock();
		}
		
		$pageformats = $this->object->getPageFormats();
		foreach ($pageformats as $pageformat)
		{
			$this->tpl->setCurrentBlock("page_format_row");
			$this->tpl->setVariable("VALUE_PAGE_FORMAT", $pageformat["value"]);
			$this->tpl->setVariable("NAME_PAGE_FORMAT", $pageformat["name"]);
			if (strcmp($form_fields["pageformat"], $pageformat["value"]) == 0)
			{
				$this->tpl->setVariable("SELECTED_PAGE_FORMAT", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TEXT_CERTIFICATE", $this->lng->txt("certificate_edit"));
		$this->tpl->setVariable("TEXT_STATUS", $this->lng->txt("certificate_status"));
		if ($this->object->isComplete() || $this->object->hasBackgroundImage())
		{
			$this->tpl->setVariable("DELETE_BUTTON_CERTIFICATE", $this->lng->txt("certificate_delete"));
		}
		if ($this->object->isComplete())
		{
			$this->tpl->setVariable("VALUE_STATUS", $this->lng->txt("certificate_status_complete"));
			include_once "./classes/class.ilUtil.php";
			$this->tpl->setVariable("HREF_STATUS_IMAGE", ilUtil::getImagePath("icon_ok.gif"));
			$this->tpl->setVariable("ALT_STATUS_IMAGE", $this->lng->txt("certificate_status_complete"));
			$this->tpl->setVariable("PREVIEW_BUTTON_CERTIFICATE", $this->lng->txt("certificate_preview"));
		}
		else
		{
			$this->tpl->setVariable("VALUE_STATUS", $this->lng->txt("certificate_status_incomplete"));
		}
		
		$this->tpl->setVariable("TEXT_PAGE_FORMAT", $this->lng->txt("certificate_page_format"));
		$this->tpl->setVariable("TEXT_BACKGROUND_IMAGE", $this->lng->txt("certificate_background_image"));
		$this->tpl->setVariable("TEXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("TEXT_PADDING_TOP", $this->lng->txt("certificate_padding_top"));
		if (strlen($form_fields["padding_top"]) > 0)
		{
			$this->tpl->setVariable("VALUE_PADDING_TOP", " value=\"".$form_fields["padding_top"]."\"");
		}
		$this->tpl->setVariable("TEXT_MARGIN_BODY", $this->lng->txt("certificate_margin_body"));
		if (strlen($form_fields["margin_body"]) > 0)
		{
			$this->tpl->setVariable("VALUE_MARGIN_BODY", " value=\"".$form_fields["margin_body"]."\"");
		}
		$this->tpl->setVariable("TEXT_CERTIFICATE_TEXT", $this->lng->txt("certificate_text"));
		if (strlen($form_fields["certificate_text"]) > 0)
		{
			$this->tpl->setVariable("VALUE_CERTIFICATE_TEXT", $form_fields["certificate_text"]);
		}
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TEXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TEXT_PREVIEW", $this->lng->txt("preview"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);

		$tags = array(
		"br",
		"em",
		"font",
		"li",
		"ol",
		"p",
		"span",
		"strong",
		"u",
		"ul"			
		);
		$rte->addCustomRTESupport($obj_id, $obj_type, $tags);
		
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Exports the user results as PDF certificates
*
* Exports the user results as PDF certificates using
* XSL-FO via XML:RPC calls
*
* @access public
*/
	function exportCertificate()
	{
		$this->setResultsTabs();
	}
	
	/**
	* set the tabs for the results overview ("results" in the repository)
	*/
	function setResultsTabs()
	{
		global $ilTabs;

		include_once ("./classes/class.ilTabsGUI.php");
		$tabs_gui = new ilTabsGUI();

		// Test results tab
		$tabs_gui->addTarget("tst_results_aggregated",
			$this->ctrl->getLinkTargetByClass("ilTestEvaluationGUI", "eval_a"),
			array("eval_a"),
			"", "");

		$force_active = (is_numeric($_GET["active_id"]) && $_GET["etype"] == "all") ? true	: false;
		$tabs_gui->addTarget("eval_all_users", 
			$this->ctrl->getLinkTargetByClass("ilTestEvaluationGUI", "eval_stat"),
			array("exportCertificate"),	
			"", "", $force_active
		);
		
		if ($this->object->object->getTestType() != TYPE_SELF_ASSESSMENT)
		{
			$force_active = (is_numeric($_GET["active_id"]) && $_GET["etype"] == "selected") ? true	: false;
			$tabs_gui->addTarget("eval_selected_users", 
				$this->ctrl->getLinkTargetByClass("ilTestEvaluationGUI", "evalStatSelected"),
				array("evalStatSelected", "evalSelectedUsers", "searchForEvaluation",
				"addFoundUsersToEval", "removeSelectedUser"),	
				"", "", $force_active
			);
		}
		$ilTabs = $tabs_gui;
	}	
}

?>
