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
* @ingroup ModulesTest
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
	var $ref_id;

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

		include_once "./Modules/Test/classes/class.ilTestCertificate.php";
		$this->object = new ilTestCertificate($a_object);
    $this->lng =& $lng;
    $this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ilias =& $ilias;
		$this->tree =& $tree;
		$this->ref_id = $_GET["ref_id"];
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
* Import a certificate from a ZIP archive
*
* Import a certificate from a ZIP archive
*
* @access public
*/
	function certificateImport()
	{
		$this->certificateEditor();
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
* Exports the user results as PDF certificates
*
* Exports the user results as PDF certificates using
* XSL-FO via XML:RPC calls
*
* @access public
*/
	function exportCertificate()
	{
		$this->object->outCertificates($_GET["g_userfilter"], $_GET["g_passedonly"]);
	}
	
/**
* Exports the certificate
*
* Exports the certificate
*
* @access public
*/
	function certificateExportFO()
	{
		$this->object->deliverExportFileXML();
	}
	

	/**
* Creates a certificate output for a given active id
*
* Creates a certificate output for a given active id
*
* @access public
*/
	function certificateOutput()
	{
		$this->object->outCertificate($_GET["active_id"], $_GET["pass"]);
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
			"pageformat" => ilUtil::stripSlashes($_POST["pageformat"]),
			"padding_top" => ilUtil::stripSlashes($_POST["padding_top"]),
			"margin_body_top" => ilUtil::stripSlashes($_POST["margin_body_top"]),
			"margin_body_right" => ilUtil::stripSlashes($_POST["margin_body_right"]),
			"margin_body_bottom" => ilUtil::stripSlashes($_POST["margin_body_bottom"]),
			"margin_body_left" => ilUtil::stripSlashes($_POST["margin_body_left"]),
			"certificate_text" => ilUtil::stripSlashes($_POST["certificate_text"], FALSE),
			"pageheight" => ilUtil::stripSlashes($_POST["pageheight"]),
			"pagewidth" => ilUtil::stripSlashes($_POST["pagewidth"]),
			"certificate_visibility" => ilUtil::stripSlashes($_POST["certificate_visibility"])
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/Test");

		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT", $this->lng->txt("confirmation"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("table_row");
		$this->tpl->setVariable("CSS_ROW", "tblrow1");
		$this->tpl->setVariable("TEXT_CONTENT", $this->lng->txt("certificate_confirm_deletion_text"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("operation_btn");
		$this->tpl->setVariable("BTN_NAME", "certificateDeleteConfirm");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("yes"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("operation_btn");
		$this->tpl->setVariable("BTN_NAME", "certificateEditor");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("no"));
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Deletes the certificate and all it's data
*
* Deletes the certificate and all it's data
*
* @access public
*/
	function certificateDeleteConfirm()
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
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}
		$form_fields = array();
		if (is_array($_POST))
		{
			if (count($_POST) > 0)
			{
				// handle the form post
				
				// handle the certificate import
				if (strlen($_FILES["certificate_import"]["tmp_name"]))
				{
					$result = $this->object->importCertificate($_FILES["certificate_import"]["tmp_name"], $_FILES["certificate_import"]["name"]);
					if ($result == FALSE)
					{
						ilUtil::sendInfo($this->lng->txt("certificate_error_import"));
					}
					else
					{
						$this->ctrl->redirect($this, "certificateEditor");
					}
				}
				
				// handle the file upload
				if (strlen($_FILES["background"]["tmp_name"]))
				{
					$result = $this->object->uploadBackgroundImage($_FILES["background"]["tmp_name"]);
					if ($result == FALSE)
					{
						ilUtil::sendInfo($this->lng->txt("certificate_error_upload_bgimage"));
					}
				}
				$form_fields = $this->getFormFieldsFromPOST();
			}
			else
			{
				$form_fields = $this->object->processFO2XHTML();
			}
		}

		if ((strcmp($this->ctrl->getCmd(), "certificateSave") == 0) || (strcmp($this->ctrl->getCmd(), "certificateRemoveBackground") == 0))
		{
			// try to save the certificate to an XSL-FO document
			// 1. run checks on all input fields
			$result = $this->object->checkCertificateInput($form_fields);
			if ($result !== TRUE)
			{
				ilUtil::sendInfo($result);
			}
			else
			{
				$xslfo = $this->object->processXHTML2FO($form_fields);
				$this->object->saveCertificateVisibility($form_fields["certificate_visibility"]);
				$this->object->saveCertificate($xslfo);
			}
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_certificate_edit.html", "Modules/Test");

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
		
		if (strcmp($form_fields["pageformat"], "custom") == 0)
		{
			$this->tpl->setCurrentBlock("custom_format");
			$this->tpl->setVariable("TEXT_PAGE_UNIT_DESCRIPTION", $this->lng->txt("certificate_unit_description"));
			$this->tpl->setVariable("TEXT_PAGEHEIGHT", $this->lng->txt("certificate_pageheight"));
			$this->tpl->setVariable("TEXT_PAGEWIDTH", $this->lng->txt("certificate_pagewidth"));
			if (strlen($form_fields["pageheight"]))
			{
				$this->tpl->setVariable("VALUE_PAGEHEIGHT", " value=\"".$form_fields["pageheight"]."\"");
			}
			if (strlen($form_fields["pagewidth"]))
			{
				$this->tpl->setVariable("VALUE_PAGEWIDTH", " value=\"".$form_fields["pagewidth"]."\"");
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
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			$this->tpl->setVariable("HREF_STATUS_IMAGE", ilUtil::getImagePath("icon_ok.gif"));
			$this->tpl->setVariable("ALT_STATUS_IMAGE", $this->lng->txt("certificate_status_complete"));
			$this->tpl->setVariable("PREVIEW_BUTTON_CERTIFICATE", $this->lng->txt("certificate_preview"));
			$this->tpl->setVariable("PREVIEW_URL", $this->ctrl->getLinkTarget($this, "certificatePreview"));
			$this->tpl->setVariable("IMG_PREVIEW", ilUtil::getImagePath("icon_preview.gif"));
			$this->tpl->setVariable("IMG_EXPORT", ilUtil::getImagePath("icon_file.gif"));
			$this->tpl->setVariable("CERTIFICATE_EXPORT", $this->lng->txt("certificate_export"));
			$this->tpl->setVariable("EXPORT_URL", $this->ctrl->getLinkTarget($this, "certificateExportFO"));
		}
		else
		{
			$this->tpl->setVariable("VALUE_STATUS", $this->lng->txt("certificate_status_incomplete"));
		}
		
		$this->tpl->setVariable("TEXT_CERTIFICATE_IMPORT", $this->lng->txt("import"));
		
		$this->tpl->setVariable("BUTTON_SET_PAGEFORMAT", $this->lng->txt("change"));
		$this->tpl->setVariable("TEXT_PAGE_FORMAT", $this->lng->txt("certificate_page_format"));
		$this->tpl->setVariable("TEXT_BACKGROUND_IMAGE", $this->lng->txt("certificate_background_image"));
		$this->tpl->setVariable("TEXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("TEXT_PADDING_TOP", $this->lng->txt("certificate_padding_top"));
		if (strlen($form_fields["padding_top"]) > 0)
		{
			$this->tpl->setVariable("VALUE_PADDING_TOP", " value=\"".$form_fields["padding_top"]."\"");
		}
		$this->tpl->setVariable("TEXT_MARGIN_BODY", $this->lng->txt("certificate_margin_body"));
		$this->tpl->setVariable("TEXT_MARGIN_BODY_TOP", $this->lng->txt("certificate_top"));
		$this->tpl->setVariable("TEXT_MARGIN_BODY_RIGHT", $this->lng->txt("certificate_right"));
		$this->tpl->setVariable("TEXT_MARGIN_BODY_BOTTOM", $this->lng->txt("certificate_bottom"));
		$this->tpl->setVariable("TEXT_MARGIN_BODY_LEFT", $this->lng->txt("certificate_left"));
		if (strlen($form_fields["margin_body_top"]) > 0)
		{
			$this->tpl->setVariable("VALUE_MARGIN_BODY_TOP", " value=\"".$form_fields["margin_body_top"]."\"");
		}
		if (strlen($form_fields["margin_body_right"]) > 0)
		{
			$this->tpl->setVariable("VALUE_MARGIN_BODY_RIGHT", " value=\"".$form_fields["margin_body_right"]."\"");
		}
		if (strlen($form_fields["margin_body_bottom"]) > 0)
		{
			$this->tpl->setVariable("VALUE_MARGIN_BODY_BOTTOM", " value=\"".$form_fields["margin_body_bottom"]."\"");
		}
		if (strlen($form_fields["margin_body_left"]) > 0)
		{
			$this->tpl->setVariable("VALUE_MARGIN_BODY_LEFT", " value=\"".$form_fields["margin_body_left"]."\"");
		}
		$this->tpl->setVariable("TEXT_CERTIFICATE_TEXT", $this->lng->txt("certificate_text"));
		if (strlen($form_fields["certificate_text"]) > 0)
		{
			$this->tpl->setVariable("VALUE_CERTIFICATE_TEXT", $form_fields["certificate_text"]);
		}
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TEXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "certificateSave"));
		
		$this->tpl->setVariable("PH_INTRODUCTION", $this->lng->txt("certificate_ph_introduction"));
		$this->tpl->setVariable("PH_USER_FULLNAME", $this->lng->txt("certificate_ph_fullname"));
		$this->tpl->setVariable("PH_USER_FIRSTNAME", $this->lng->txt("certificate_ph_firstname"));
		$this->tpl->setVariable("PH_USER_LASTNAME", $this->lng->txt("certificate_ph_lastname"));
		$this->tpl->setVariable("PH_RESULT_PASSED", $this->lng->txt("certificate_ph_passed"));
		$this->tpl->setVariable("PH_RESULT_POINTS", $this->lng->txt("certificate_ph_resultpoints"));
		$this->tpl->setVariable("PH_RESULT_PERCENT", $this->lng->txt("certificate_ph_resultpercent"));
		$this->tpl->setVariable("PH_USER_TITLE", $this->lng->txt("certificate_ph_title"));
		$this->tpl->setVariable("PH_USER_STREET", $this->lng->txt("certificate_ph_street"));
		$this->tpl->setVariable("PH_USER_INSTITUTION", $this->lng->txt("certificate_ph_institution"));
		$this->tpl->setVariable("PH_USER_DEPARTMENT", $this->lng->txt("certificate_ph_department"));
		$this->tpl->setVariable("PH_USER_CITY", $this->lng->txt("certificate_ph_city"));
		$this->tpl->setVariable("PH_USER_ZIPCODE", $this->lng->txt("certificate_ph_zipcode"));
		$this->tpl->setVariable("PH_USER_COUNTRY", $this->lng->txt("certificate_ph_country"));
		$this->tpl->setVariable("PH_MAX_POINTS", $this->lng->txt("certificate_ph_maxpoints"));
		$this->tpl->setVariable("PH_RESULT_MARK_SHORT", $this->lng->txt("certificate_ph_markshort"));
		$this->tpl->setVariable("PH_RESULT_MARK_LONG", $this->lng->txt("certificate_ph_marklong"));
		$this->tpl->setVariable("PH_TEST_TITLE", $this->lng->txt("certificate_ph_testtitle"));
		$this->tpl->setVariable("PH_DATE", $this->lng->txt("certificate_ph_date"));
		$this->tpl->setVariable("PH_DATETIME", $this->lng->txt("certificate_ph_datetime"));
		
		$this->tpl->setVariable("TEXT_UNIT_DESCRIPTION", $this->lng->txt("certificate_unit_description"));
		$this->tpl->setVariable("TEXT_CERTIFICATE_VISIBILITY", $this->lng->txt("certificate_visibility"));
		$this->tpl->setVariable("TEXT_CERTIFICATE_VISIBILITY_INTRODUCTION", $this->lng->txt("certificate_visibility_introduction"));
		$this->tpl->setVariable("TEXT_VISIBILITY_ALWAYS", $this->lng->txt("certificate_visibility_always"));
		$this->tpl->setVariable("TEXT_VISIBILITY_NEVER", $this->lng->txt("certificate_visibility_never"));
		$this->tpl->setVariable("TEXT_VISIBILITY_PASSED", $this->lng->txt("certificate_visibility_passed"));
		switch ($form_fields["certificate_visibility"])
		{
			case 1:
				$this->tpl->setVariable("CHECKED_CV_1", " checked=\"checked\"");
				break;
			case 2:
				$this->tpl->setVariable("CHECKED_CV_2", " checked=\"checked\"");
				break;
			case 0:
			default:
				$this->tpl->setVariable("CHECKED_CV_0", " checked=\"checked\"");
				break;
		}

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
	* Output of a test certificate
	*
	* Output of a test certificate
	*
	* @access public
	*/
	function outCertificate()
	{
		global $ilUser;

		$active_id = $this->object->object->getTestSession()->getActiveId();
		$counted_pass = ilObjTest::_getResultPass($active_id);
		$this->ctrl->setParameterByClass("iltestcertificategui","active_id", $active_id);
		$this->ctrl->setParameterByClass("iltestcertificategui","pass", $counted_pass);
		$this->ctrl->redirectByClass("iltestcertificategui", "certificateOutput");
	}
}

?>
