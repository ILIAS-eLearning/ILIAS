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
* GUI class to create PDF certificates
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup Services
*/
class ilCertificateGUI
{
	/**
	* ilCertificate object reference
	*
	* @var integer
	*/
	var $object;

	/**
	* The reference to the ILIAS control class
	*
	* @var object
	*/
	var $ctrl;

	/**
	* The reference to the ILIAS tree class
	*
	* @var object
	*/
	var $tree;

	/**
	* The reference to the ILIAS class
	*
	* @var object
	*/
	var $ilias;

	/**
	* The reference to the Template class
	*
	* @var object
	*/
	var $tpl;

	/**
	* The reference to the Language class
	*
	* @var object
	*/
	var $lng;
	
	/**
	* The reference ID of the object
	*
	* @var object
	*/
	var $ref_id;

	/**
	* ilCertificateGUI constructor
	*
	* @param object $a_object A reference to the test container object
	* @access public
	*/
	function ilCertificateGUI($adapter)
	{
		global $lng, $tpl, $ilCtrl, $ilias, $tree;

		include_once "./Services/Certificate/classes/class.ilCertificate.php";
		$this->object = new ilCertificate($adapter);
		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ilias =& $ilias;
		$this->tree =& $tree;
		$this->ref_id = $_GET["ref_id"];
		$this->lng->loadLanguageModule("certificate");
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
	*/
	public function getCommand($cmd)
	{
		return $cmd;
	}
	
	/**
	* Import a certificate from a ZIP archive
	*/
	public function certificateImport()
	{
		$this->certificateEditor();
	}
	
	/**
	* Creates a certificate preview
	*/
	public function certificatePreview()
	{
		$this->object->createPreview();
	}

	/**
	* Exports the certificate
	*/
	public function certificateExportFO()
	{
		$this->object->deliverExportFileXML();
	}

	/**
	* Removes the background image of a certificate
	*/
	public function certificateRemoveBackground()
	{
		$this->object->deleteBackgroundImage();
		$this->certificateEditor();
	}
	
	/**
	* Deletes the certificate and all its data
	*/
	public function certificateDelete()
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
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "certificateEditor"));
	}
	
	/**
	* Deletes the certificate and all its data
	*/
	public function certificateDeleteConfirm()
	{
		$this->object->deleteCertificate();
		$this->ctrl->redirect($this, "certificateEditor");
	}
	
	/**
	* Saves the certificate
	*/
	function certificateSave()
	{
		$this->certificateEditor();
	}

	/**
	* Uploads the certificate
	*/
	public function certificateUpload()
	{
		$this->certificateEditor();
	}
	
	/**
	* Get the form values from an HTTP POST
	*/
	private function getFormFieldsFromPOST()
	{
		$form_fields = array(
			"pageformat" => ilUtil::stripSlashes($_POST["pageformat"]),
			"padding_top" => ilUtil::stripSlashes($_POST["padding_top"]),
			"margin_body_top" => ilUtil::stripSlashes($_POST["margin_body"]["top"]),
			"margin_body_right" => ilUtil::stripSlashes($_POST["margin_body"]["right"]),
			"margin_body_bottom" => ilUtil::stripSlashes($_POST["margin_body"]["bottom"]),
			"margin_body_left" => ilUtil::stripSlashes($_POST["margin_body"]["left"]),
			"certificate_text" => ilUtil::stripSlashes($_POST["certificate_text"], FALSE),
			"pageheight" => ilUtil::stripSlashes($_POST["pageheight"]),
			"pagewidth" => ilUtil::stripSlashes($_POST["pagewidth"])
		);
		$this->object->getAdapter()->addFormFieldsFromPOST($form_fields);
		return $form_fields;
	}
	
	/**
	* Get the form values from the certificate xsl-fo
	*/
	private function getFormFieldsFromFO()
	{
		$form_fields = $this->object->getFormFieldsFromFO();
		$this->object->getAdapter()->addFormFieldsFromObject($form_fields);
		return $form_fields;
	}

	/**
	* Shows the certificate editor for ILIAS tests
	*/
	public function certificateEditor()
	{
		global $ilAccess;
		
		$form_fields = array();
		if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0)
		{
			$form_fields = $this->getFormFieldsFromPOST();
		}
		else
		{
			$form_fields = $this->getFormFieldsFromFO();
		}
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("certificate_edit"));
		$form->setMultipart(TRUE);
		$form->setTableWidth("100%");
		$form->setId("certificate");

		$import = new ilFileInputGUI($this->lng->txt("import"), "certificate_import");
		$import->setRequired(FALSE);
		$import->setSuffixes(array("zip"));
		// handle the certificate import
		if (strlen($_FILES["certificate_import"]["tmp_name"]))
		{
			if ($import->checkInput())
			{
				$result = $this->object->importCertificate($_FILES["certificate_import"]["tmp_name"], $_FILES["certificate_import"]["name"]);
				if ($result == FALSE)
				{
					$import->setAlert($this->lng->txt("certificate_error_import"));
				}
				else
				{
					$this->ctrl->redirect($this, "certificateEditor");
				}
			}
		}
		$form->addItem($import);
		
		$pageformat = new ilSelectInputGUI($this->lng->txt("certificate_page_format"), "pageformat");
		$pageformats = $this->object->getPageFormats();
		$pageformat->setValue($form_fields["pageformat"]);
		$options = array();
		foreach ($pageformats as $format)
		{
			$options[$format["value"]] = $format["name"];
		}
		$pageformat->setRequired(TRUE);
		$pageformat->setOptions($options);
		if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0) $pageformat->checkInput();
		
		if (strcmp($form_fields["pageformat"], "custom") == 0)
		{
			$pageheight = new ilTextInputGUI($this->lng->txt("certificate_pageheight"), "pageheight");
			$pageheight->setValue($form_fields["pageheight"]);
			$pageheight->setSize(6);
			$pageheight->setValidationRegexp("/[0123456789\\.](cm|mm|in|pt|pc|px|em)/is");
			$pageheight->setInfo($this->lng->txt("certificate_unit_description"));
			if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0) $pageheight->checkInput();
			$pageformat->addSubitem($pageheight);

			$pagewidth = new ilTextInputGUI($this->lng->txt("certificate_pagewidth"), "pagewidth");
			$pagewidth->setValue($form_fields["pagewidth"]);
			$pagewidth->setSize(6);
			$pagewidth->setValidationRegexp("/[0123456789\\.](cm|mm|in|pt|pc|px|em)/is");
			$pagewidth->setInfo($this->lng->txt("certificate_unit_description"));
			if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0) $pagewidth->checkInput();
			$pageformat->addSubitem($pagewidth);
		}
		$form->addItem($pageformat);

		$bgimage = new ilImageFileInputGUI($this->lng->txt("certificate_background_image"), "background");
		$bgimage->setRequired(FALSE);
		$dbimage->setUseCache(false);
		if (count($_POST)) 
		{
			// handle the background upload
			if (strlen($_FILES["background"]["tmp_name"]))
			{
				if ($bgimage->checkInput())
				{
					$result = $this->object->uploadBackgroundImage($_FILES["background"]["tmp_name"]);
					if ($result == FALSE)
					{
						$bgimage->setAlert($this->lng->txt("certificate_error_upload_bgimage"));
					}
				}
			}
		}
		if (!$this->object->hasBackgroundImage())
		{
			include_once "./Services/Certificate/classes/class.ilObjCertificateSettingsAccess.php";
			if (ilObjCertificateSettingsAccess::hasBackgroundImage())
			{
				$bgimage->setImage(ilObjCertificateSettingsAccess::getBackgroundImageThumbPathWeb());
			}
		}
		else
		{
			$bgimage->setImage($this->object->getBackgroundImageThumbPathWeb());
		}
		$form->addItem($bgimage);
		
		$padding_top = new ilTextInputGUI($this->lng->txt("certificate_padding_top"), "padding_top");
		$padding_top->setRequired(TRUE);
		$padding_top->setValue($form_fields["padding_top"]);
		$padding_top->setSize(6);
		$padding_top->setValidationRegexp("/[0123456789\\.](cm|mm|in|pt|pc|px|em)/is");
		$padding_top->setInfo($this->lng->txt("certificate_unit_description"));
		if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0) $padding_top->checkInput();
		$form->addItem($padding_top);
		
		$rect = new ilCSSRectInputGUI($this->lng->txt("certificate_margin_body"), "margin_body");
		$rect->setRequired(TRUE);
		$rect->setUseUnits(TRUE);
		$rect->setTop($form_fields["margin_body_top"]);
		$rect->setBottom($form_fields["margin_body_bottom"]);
		$rect->setLeft($form_fields["margin_body_left"]);
		$rect->setRight($form_fields["margin_body_right"]);
		$rect->setInfo($this->lng->txt("certificate_unit_description"));
		if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0) $rect->checkInput();
		$form->addItem($rect);
		
		$certificate = new ilTextAreaInputGUI($this->lng->txt("certificate_text"), "certificate_text");
		$certificate->setValue($form_fields["certificate_text"]);
		$certificate->setRequired(TRUE);
		$certificate->setRows(20);
		$certificate->setCols(80);
		$certificate->setInfo($this->object->getAdapter()->getCertificateVariablesDescription());
		$certificate->setUseRte(TRUE);
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
		$certificate->setRteTags($tags);
		if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0) $certificate->checkInput();
		$form->addItem($certificate);

		$this->object->getAdapter()->addAdditionalFormElements($form, $form_fields);

		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			if ($this->object->isComplete() || $this->object->hasBackgroundImage())
			{
				$form->addCommandButton("certificatePreview", $this->lng->txt("certificate_preview"));
				$form->addCommandButton("certificateExportFO", $this->lng->txt("certificate_export"));
				$form->addCommandButton("certificateDelete", $this->lng->txt("delete"));
			}
			$form->addCommandButton("certificateSave", $this->lng->txt("save"));
		}

		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());

		if (strcmp($this->ctrl->getCmd(), "certificateSave") == 0)
		{
			if ($_POST["background_delete"])
			{
				$this->object->deleteBackgroundImage();
			}
			if ($form->checkInput())
			{
				try
				{
					$xslfo = $this->object->processXHTML2FO($form_fields);
					$this->object->getAdapter()->saveFormFields($form_fields);
					$this->object->saveCertificate($xslfo);
					ilUtil::sendSuccess($this->lng->txt("saved_successfully"), TRUE);
					$this->ctrl->redirect($this, "certificateEditor");
				}
				catch (Exception $e)
				{
					ilUtil::sendFailure($e->getMessage());
				}
			}
		}
	}
}

?>
