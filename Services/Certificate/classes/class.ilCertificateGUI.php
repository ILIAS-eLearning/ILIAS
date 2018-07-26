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

include_once("./Services/Certificate/classes/class.ilCertificate.php");

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
	 * @var ilCertificate
	 */
	protected $certifcateObject;

	/**
	* The reference to the ILIAS control class
	*
	* @var object
	*/
	protected $ctrl;

	/**
	* The reference to the ILIAS tree class
	*
	* @var object
	*/
	protected $tree;

	/**
	* The reference to the ILIAS class
	*
	* @var object
	*/
	protected $ilias;

	/**
	* The reference to the Template class
	*
	* @var object
	*/
	protected $tpl;

	/**
	* The reference to the Language class
	*
	* @var object
	*/
	protected $lng;
	
	/**
	* The reference ID of the object
	*
	* @var object
	*/
	protected $ref_id;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilCertificateTemplateRepository
	 */
	private $templateRepository;

	/**
	 * @var ilXlsFoParser
	 */
	private $xlsFoParser;

	/**
	 * @var ilCertificatePlaceholderDescription
	 */
	private $placeholderDescriptionObject;

	/**
	 * @var integer
	 */
	private $objectId;

	/**
	 * @var ilCertificateSettingsFormFactory|null
	 */
	private $settingsFormFactory;

	/**
	 * @var ilCertificatePlaceholderValues
	 */
	private $placeholderValuesObject;

	/**
	 * ilCertificateGUI constructor
	 * @param ilCertificateAdapter $adapter A reference to the test container object
	 * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
	 * @param ilCertificatePlaceholderValues $placeholderValuesObject
	 * @param $objectId
	 * @param $certificatePath
	 * @param ilCertificateSettingsFormFactory|null $settingsFormFactory
	 * @param ilCertificateTemplateRepository|null $templateRepository
	 * @param ilXlsFoParser|null $xlsFoParser
	 * @access public
	 */
	public function __construct(
		ilCertificateAdapter $adapter,
		ilCertificatePlaceholderDescription $placeholderDescriptionObject,
		ilCertificatePlaceholderValues $placeholderValuesObject,
		$objectId,
		$certificatePath,
		ilCertificateSettingsFormFactory $settingsFormFactory = null,
		ilCertificateTemplateRepository $templateRepository = null,
		ilXlsFoParser $xlsFoParser = null
	) {
		global $DIC;

		$this->certifcateObject = new ilCertificate(
			$adapter,
			$placeholderDescriptionObject,
			$placeholderValuesObject,
			$objectId,
			$certificatePath
		);

		$this->lng     = $DIC['lng'];
		$this->tpl     = $DIC['tpl'];
		$this->ctrl    = $DIC['ilCtrl'];
		$this->ilias   = $DIC['ilias'];
		$this->tree    = $DIC['tree'];
		$this->tree    = $DIC['tree'];
		$this->access = $DIC['ilAccess'];
		$this->toolbar = $DIC['ilToolbar'];

		$this->ref_id = (int)$_GET['ref_id'];

		$this->placeholderDescriptionObject = $placeholderDescriptionObject;

		$this->placeholderValuesObject = $placeholderValuesObject;

		$this->objectId = $objectId;

		if (null === $settingsFormFactory) {
			$settingsFormFactory = new ilCertificateSettingsFormFactory(
				$this->lng,
				$this->tpl,
				$this->ctrl,
				$this->access,
				$this->toolbar,
				$placeholderDescriptionObject
			);
		}
		$this->settingsFormFactory = $settingsFormFactory;

		if (null === $templateRepository) {
			$templateRepository = new ilCertificateTemplateRepository($DIC->database());
		}
		$this->templateRepository = $templateRepository;

		if (null === $xlsFoParser) {
			$xlsFoParser = new ilXlsFoParser($adapter);
		}
		$this->xlsFoParser = $xlsFoParser;

		$this->lng->loadLanguageModule('certificate');
	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->getCommand($cmd);
		switch($next_class)
		{
			default:
				$ret = $this->$cmd();
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
		$this->certifcateObject->createPreview();
	}

	/**
	* Exports the certificate
	*/
	public function certificateExportFO()
	{
		$this->certifcateObject->deliverExportFileXML();
	}

	/**
	* Removes the background image of a certificate
	*/
	public function certificateRemoveBackground()
	{
		$this->certifcateObject->deleteBackgroundImage();
		$this->certificateEditor();
	}
	
	/**
	* Deletes the certificate and all its data
	*/
	public function certificateDelete()
	{		
		// display confirmation message
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this, "certificateEditor"));
		$cgui->setHeaderText($this->lng->txt("certificate_confirm_deletion_text"));
		$cgui->setCancel($this->lng->txt("no"), "certificateEditor");
		$cgui->setConfirm($this->lng->txt("yes"), "certificateDeleteConfirm");
		
		$this->tpl->setContent($cgui->getHTML());
	}
	
	/**
	* Deletes the certificate and all its data
	*/
	public function certificateDeleteConfirm()
	{
		$this->certifcateObject->deleteCertificate();
		$this->ctrl->redirect($this, "certificateEditor");
	}
	
	/**
	* Saves the certificate
	*/
	function certificateSave()
	{
		$form_fields = $this->getFormFieldsFromPOST();

		$form = $this->settingsFormFactory->create(
			$this,
			$this->certifcateObject,
			$form_fields
		);

		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());

		$this->saveCertificate($form, $form_fields, $this->objectId);

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
	protected function getFormFieldsFromPOST()
	{
		$form_fields = array(
			"pageformat" => ilUtil::stripSlashes($_POST["pageformat"]),
			"margin_body_top" => $this->certifcateObject->formatNumberString(ilUtil::stripSlashes($_POST["margin_body"]["top"])),
			"margin_body_right" => $this->certifcateObject->formatNumberString(ilUtil::stripSlashes($_POST["margin_body"]["right"])),
			"margin_body_bottom" => $this->certifcateObject->formatNumberString(ilUtil::stripSlashes($_POST["margin_body"]["bottom"])),
			"margin_body_left" => $this->certifcateObject->formatNumberString(ilUtil::stripSlashes($_POST["margin_body"]["left"])),
			"certificate_text" => ilUtil::stripSlashes($_POST["certificate_text"], FALSE),
			"pageheight" => $this->certifcateObject->formatNumberString(ilUtil::stripSlashes($_POST["pageheight"])),
			"pagewidth" => $this->certifcateObject->formatNumberString(ilUtil::stripSlashes($_POST["pagewidth"])),
			"active" => ilUtil::stripSlashes($_POST["active"])
		);

		$this->certifcateObject->getAdapter()->addFormFieldsFromPOST($form_fields);

		return $form_fields;
	}
	
	/**
	* Get the form values from the certificate xsl-fo
	*/
	protected function getFormFieldsFromFO()
	{
		$form_fields = $this->certifcateObject->getFormFieldsFromFO();
		$form_fields["active"] = $this->certifcateObject->readActive();
		$this->certifcateObject->getAdapter()->addFormFieldsFromObject($form_fields);
		return $form_fields;
	}

	/**
	* Shows the certificate editor for ILIAS tests
	*/
	public function certificateEditor()
	{
		$certificate = $this->templateRepository->fetchCurrentlyActiveCertificate($this->objectId);
		$content = $certificate->getCertificateContent();

		$form_fields = $this->xlsFoParser->parse($content);
		$form_fields["active"] = $this->certifcateObject->readActive();

		$form = $this->settingsFormFactory->create(
			$this,
			$this->certifcateObject,
			$form_fields
		);

		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}

	/**
	 * @param ilPropertyFormGUI $form
	 * @param array $form_fields
	 * @param $objId
	 */
	private function saveCertificate(ilPropertyFormGUI $form, array $form_fields, $objId)
	{
		$certificate = $this->templateRepository->fetchPreviousCertificate($objId);
		$currentVersion = $certificate->getVersion();
		$nextVersion = $currentVersion + 1;

		if ($_POST["background_delete"]) {
			$this->certifcateObject->deleteBackgroundImage($currentVersion);
		}

		if ($form->checkInput()) {
			try {
				$xslfo = $this->certifcateObject->processXHTML2FO($form_fields, $nextVersion);
				$this->certifcateObject->getAdapter()->saveFormFields($form_fields);

				$templateValues = $this->placeholderDescriptionObject->getPlaceholderDescriptions();

				$backgroundImagePath = $certificate->getBackgroundImagePath();
				if (count($_POST)) {
					// handle the background upload
					$temporaryFileName = $_FILES['background']['tmp_name'];
					if (strlen($temporaryFileName)) {
						try {
							$backgroundImagePath = $this->certifcateObject->uploadBackgroundImage($temporaryFileName, $nextVersion);
						} catch (ilException $exception) {
							$form->getFileUpload('background')->setAlert($this->lng->txt("certificate_error_upload_bgimage"));
						}
					}
				}

				$certificateTemplate = new ilCertificateTemplate(
					$objId,
					$xslfo,
					md5($xslfo),
					json_encode($templateValues),
					$nextVersion,
					ILIAS_VERSION_NUMERIC,
					time(),
					true,
					$backgroundImagePath,
					$form_fields['active']
				);

				$this->templateRepository->save($certificateTemplate);

				$this->certifcateObject->writeActive($form_fields['active']);

				ilUtil::sendSuccess($this->lng->txt("saved_successfully"), TRUE);
				$this->ctrl->redirect($this, "certificateEditor");
			} catch (Exception $e) {
				ilUtil::sendFailure($e->getMessage());
			}
		}
	}
}
