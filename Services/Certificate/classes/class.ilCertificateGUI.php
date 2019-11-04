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
* @ilCtrl_Calls: ilCertificateGUI: ilPropertyFormGUI
*/
class ilCertificateGUI
{
	/**
	 * @var \ILIAS\Filesystem\Filesystem
	 */
	private $fileSystem;

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
	private $formFieldParser;

	/**
	 * @var ilCertificatePlaceholderDescription
	 */
	private $placeholderDescriptionObject;

	/**
	 * @var integer
	 */
	private $objectId;

	/**
	 * @var ilCertificateSettingsFormRepository|null
	 */
	private $settingsFormFactory;

	/**
	 * @var ilCertificatePlaceholderValues
	 */
	private $placeholderValuesObject;

	/**
	 * @var ilXlsFoParser|null
	 */
	private $xlsFoParser;

	/**
	 * @var ilCertificateDeleteAction
	 */
	private $deleteAction;

	/**
	 * @var ilCertificateTemplateExportAction|null
	 */
	private $exportAction;

	/**
	 * @var ilCertificateBackgroundImageUpload
	 */
	private $backgroundImageUpload;

	/**
	 * @var ilCertificateTemplatePreviewAction|null
	 */
	private $previewAction;

	/**
	 * @var ilCertificateThumbnailImageUpload|null
	 */
	private $thumbnailImageUpload;

	/**
	 * @var \ILIAS\FileUpload\FileUpload|null
	 */
	private $fileUpload;

	/**
	 * @var string
	 */
	private $certificatePath;

	/**
	 * @var ilSetting
	 */
	private $settings;

	/**
	 * @var ilPageFormats|null
	 */
	private $pageFormats;

	/**
	 * ilCertificateGUI constructor
	 * @param ilCertificateAdapter $adapter A reference to the test container object
	 * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
	 * @param ilCertificatePlaceholderValues $placeholderValuesObject
	 * @param $objectId
	 * @param $certificatePath
	 * @param ilCertificateFormRepository $settingsFormFactory
	 * @param ilCertificateDeleteAction $deleteAction
	 * @param ilCertificateTemplateRepository|null $templateRepository
	 * @param ilPageFormats|null $pageFormats
	 * @param ilXlsFoParser|null $xlsFoParser
	 * @param ilFormFieldParser $formFieldParser
	 * @param ilCertificateTemplateExportAction|null $exportAction
	 * @param ilCertificateBackgroundImageUpload|null $upload
	 * @param ilCertificateTemplatePreviewAction|null $previewAction
	 * @param \ILIAS\FileUpload\FileUpload|null $fileUpload
	 * @param ilSetting|null $setting
	 * @access public
	 */
	public function __construct(
		ilCertificateAdapter $adapter,
		ilCertificatePlaceholderDescription $placeholderDescriptionObject,
		ilCertificatePlaceholderValues $placeholderValuesObject,
		$objectId,
		$certificatePath,
		ilCertificateFormRepository $settingsFormFactory = null,
		ilCertificateDeleteAction $deleteAction = null,
		ilCertificateTemplateRepository $templateRepository = null,
		ilPageFormats $pageFormats = null,
		ilXlsFoParser $xlsFoParser = null,
		ilFormFieldParser $formFieldParser = null,
		ilCertificateTemplateExportAction $exportAction = null,
		ilCertificateBackgroundImageUpload $upload = null,
		ilCertificateTemplatePreviewAction $previewAction = null,
		\ILIAS\FileUpload\FileUpload $fileUpload = null,
		ilSetting $settings = null,
		\ILIAS\Filesystem\Filesystem $fileSystem = null
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
		$this->access  = $DIC['ilAccess'];
		$this->toolbar = $DIC['ilToolbar'];

		$this->lng->loadLanguageModule('certificate');
		$this->lng->loadLanguageModule('cert');
		$this->lng->loadLanguageModule("trac");

		$this->ref_id = (int)$_GET['ref_id'];

		$this->placeholderDescriptionObject = $placeholderDescriptionObject;

		$this->placeholderValuesObject = $placeholderValuesObject;

		$this->objectId = $objectId;

		$logger = $DIC->logger()->cert();

		if (null === $settingsFormFactory) {
			$settingsFormFactory = new ilCertificateSettingsFormRepository(
				$this->objectId,
				$certificatePath,
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
			$templateRepository = new ilCertificateTemplateRepository($DIC->database(), $logger);
		}
		$this->templateRepository = $templateRepository;

		if (null === $deleteAction) {
			$deleteAction = new ilCertificateTemplateDeleteAction($templateRepository);
		}
		$this->deleteAction = $deleteAction;

		if (null === $formFieldParser) {
			$formFieldParser = new ilFormFieldParser();
		}
		$this->formFieldParser = $formFieldParser;

		if (null === $pageFormats) {
			$pageFormats = new ilPageFormats($DIC->language());
		}
		$this->pageFormats = $pageFormats;

		if (null === $xlsFoParser) {
			$xlsFoParser = new ilXlsFoParser($DIC->settings(), $pageFormats);
		}
		$this->xlsFoParser = $xlsFoParser;

		if (null === $upload) {
			$upload = new ilCertificateBackgroundImageUpload(
				$DIC->upload(),
				$certificatePath,
				$DIC->language(),
				$logger
			);
		}
		$this->backgroundImageUpload = $upload;

		if (null === $exportAction) {
			$exportAction = new ilCertificateTemplateExportAction(
				$this->objectId,
				$certificatePath,
				$this->templateRepository,
				$DIC->filesystem()->web()
			);
		}
		$this->exportAction = $exportAction;

		if (null === $previewAction) {
			$previewAction = new ilCertificateTemplatePreviewAction($templateRepository, $placeholderValuesObject);
		}
		$this->previewAction = $previewAction;

		if (null === $fileUpload) {
			global $DIC;
			$fileUpload = $DIC->upload();
		}
		$this->fileUpload = $fileUpload;

		$this->certificatePath = $certificatePath;

		if (null === $settings) {
			$settings = new ilSetting('certificate');
		}
		$this->settings = $settings;

		if (null === $fileSystem) {
			$fileSystem = $DIC->filesystem()->web();
		}
		$this->fileSystem = $fileSystem;
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
			case 'ilpropertyformgui':
				$form = $this->getEditorForm();
				$this->ctrl->forwardCommand($form);
				break;

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
		try {
			$this->previewAction->createPreviewPdf($this->objectId);
		} catch (Exception $exception) {
			ilUtil::sendFailure($this->lng->txt('error_creating_certificate_pdf', true));
			$this->certificateEditor();
		}
	}

	/**
	* Exports the certificate
	*/
	public function certificateExportFO()
	{
		$this->exportAction->export();
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
		$template = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);
		$templateId = $template->getId();

		$this->deleteAction->delete($templateId, $this->objectId);
		$this->ctrl->redirect($this, "certificateEditor");
	}

	/**
	* Saves the certificate
	*/
	function certificateSave()
	{
		global $DIC;

		$form = $this->settingsFormFactory->createForm(
			$this,
			$this->certifcateObject
		);

		$form->setValuesByPost();

		$request = $DIC->http()->request();

		$formFields = $request->getParsedBody();

		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML());

		$this->saveCertificate($form, $formFields, $this->objectId);
	}

	/**
	* Uploads the certificate
	*/
	public function certificateUpload()
	{
		$this->certificateEditor();
	}

	/**
	 * @return ilPropertyFormGUI
	 * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
	 * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 * @throws ilDatabaseException
	 * @throws ilException
	 * @throws ilWACException
	 */
	private function getEditorForm(): \ilPropertyFormGUI
	{
		$certificateTemplate = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);

		$form = $this->settingsFormFactory->createForm(
			$this,
			$this->certifcateObject
		);

		$formFields = $this->createFormatArray($certificateTemplate);

		$formFields['active'] = $certificateTemplate->isCurrentlyActive();

		$form->setValuesByArray($formFields);

		return $form;
	}

	/**
	* Shows the certificate editor for ILIAS tests
	*/
	public function certificateEditor()
	{
		$form = $this->getEditorForm();
		$enabledGlobalLearningProgress = \ilObjUserTracking::_enabledLearningProgress();

		$messageBoxHtml = '';
		if ($enabledGlobalLearningProgress) {
			$objectLearningProgressSettings = new ilLPObjSettings($this->objectId);
			$mode = $objectLearningProgressSettings->getMode();

			/** @var ilObject $object */
			$object = ilObjectFactory::getInstanceByObjId($this->objectId);
			if (ilLPObjSettings::LP_MODE_DEACTIVATED == $mode && $object->getType() !== 'crs') {
				global $DIC;

				$renderer = $DIC->ui()->renderer();
				$messageBox = $DIC->ui()
					->factory()
					->messageBox()
					->info($this->lng->txt('learning_progress_deactivated'));

				$messageBoxHtml = $renderer->render($messageBox);
				$form->clearCommandButtons();
			}
		}

		$formHtml = $form->getHTML();

		$this->tpl->setVariable("ADM_CONTENT", $messageBoxHtml. $formHtml);
	}

	/**
	 * @param ilPropertyFormGUI $form
	 * @param array $form_fields
	 * @param $objId
	 */
	private function saveCertificate(ilPropertyFormGUI $form, array $form_fields, $objId)
	{
		$previousCertificateTemplate = $this->templateRepository->fetchPreviousCertificate($objId);
		$currentVersion = $previousCertificateTemplate->getVersion();
		$nextVersion = $currentVersion + 1;

		if ($_POST["background_delete"]) {
			$this->certifcateObject->deleteBackgroundImage($currentVersion);
		}

		if ($form->checkInput()) {
			try {
				$this->settingsFormFactory->save($form_fields);

				$templateValues = $this->placeholderDescriptionObject->getPlaceholderDescriptions();

				// handle the background upload
				$backgroundImagePath = '';
				$temporaryFileName = $_FILES['background']['tmp_name'];
				if (strlen($temporaryFileName)) {
					try {
						$backgroundImagePath = $this->backgroundImageUpload->uploadBackgroundImage($temporaryFileName, $nextVersion);
					} catch (ilException $exception) {
						$form->getFileUpload('background')->setAlert($this->lng->txt("certificate_error_upload_bgimage"));
					}
					if (false === $this->fileSystem->has($backgroundImagePath)) {
						$form->getFileUpload('background')->setAlert($this->lng->txt("certificate_error_upload_bgimage"));
						$backgroundImagePath = '';
					}
				}
				if($backgroundImagePath === '') {
					if ($_POST['background_delete']) {
						$globalBackgroundImagePath = ilObjCertificateSettingsAccess::getBackgroundImagePath(true);
						$backgroundImagePath = str_replace('[CLIENT_WEB_DIR]', '', $globalBackgroundImagePath);
					} else {
						$backgroundImagePath = $previousCertificateTemplate->getBackgroundImagePath();
					}
				}

				// handle the card thumbnail upload
				$cardThumbnailImagePath = '';
				$temporaryFileName = $_FILES['certificate_card_thumbnail_image']['tmp_name'];
				if (strlen($temporaryFileName) && $this->fileUpload->hasUploads()) {
					try {
						if (false === $this->fileUpload->hasBeenProcessed()) {
							$this->fileUpload->process();
						}

						/** @var \ILIAS\FileUpload\DTO\UploadResult $result */
						$uploadResults = $this->fileUpload->getResults();
						$result = $uploadResults[$temporaryFileName];
						if ($result->getStatus() == \ILIAS\FileUpload\DTO\ProcessingStatus::OK) {
							$cardThumbnailFileName = 'card_thumbnail_image_' . $nextVersion . '.svg';

							$this->fileUpload->moveOneFileTo(
								$result,
								$this->certificatePath,
								\ILIAS\FileUpload\Location::WEB,
								$cardThumbnailFileName,
								true
							);

							$cardThumbnailImagePath = $this->certificatePath . $cardThumbnailFileName;
						}
					} catch (ilException $exception) {
						$form->getFileUpload('certificate_card_thumbnail_image')->setAlert($this->lng->txt("certificate_error_upload_ctimage"));
					}
					if (false === $this->fileSystem->has($cardThumbnailImagePath)) {
						$form->getFileUpload('certificate_card_thumbnail_image')->setAlert($this->lng->txt("certificate_error_upload_ctimage"));
                        $cardThumbnailImagePath = '';
					}
				}
				if($cardThumbnailImagePath === '' && !$_POST['certificate_card_thumbnail_image_delete']) {
					$cardThumbnailImagePath = $previousCertificateTemplate->getThumbnailImagePath();
				}

				$jsonEncodedTemplateValues = json_encode($templateValues);

				$xslfo = $this->xlsFoParser->parse($form_fields);

				$newHashValue = hash(
					'sha256',
					implode('', array(
						$xslfo,
						$backgroundImagePath,
						$jsonEncodedTemplateValues,
						$cardThumbnailImagePath
					))
				);

				$active = (bool) $form_fields['active'];

				if ($newHashValue !== $previousCertificateTemplate->getCertificateHash()) {
					$certificateTemplate = new ilCertificateTemplate(
						$objId,
						ilObject::_lookupType($objId),
						$xslfo,
						$newHashValue,
						$jsonEncodedTemplateValues,
						$nextVersion,
						ILIAS_VERSION_NUMERIC,
						time(),
						$active,
						$backgroundImagePath,
						$cardThumbnailImagePath
					);

					$this->templateRepository->save($certificateTemplate);
					ilUtil::sendSuccess($this->lng->txt("saved_successfully"), true);
					$this->ctrl->redirect($this, "certificateEditor");
				}

				if ($previousCertificateTemplate->getId() !== null && $previousCertificateTemplate->isCurrentlyActive() !== $active) {
					$this->templateRepository->updateActivity($previousCertificateTemplate, $active);
					ilUtil::sendInfo($this->lng->txt('certificate_change_active_status'), true);
					$this->ctrl->redirect($this, "certificateEditor");
				}

				ilUtil::sendInfo($this->lng->txt('certificate_same_not_saved'), true);
				$this->ctrl->redirect($this, "certificateEditor");
			} catch (Exception $e) {
				ilUtil::sendFailure($e->getMessage());
			}
		}

		$form->setValuesByPost();

		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}

	/**
	 * @param $content
	 * @param $certificate
	 * @param $form
	 */
	private function setTemplateContent(ilCertificateTemplate $certificate, ilPropertyFormGUI $form)
	{
		$form_fields = $this->settingsFormFactory->fetchFormFieldData($certificate->getCertificateContent());
		$form_fields['active'] = $certificate->isCurrentlyActive();

		$form->setValuesByArray($form_fields);

		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}

	/**
	 * @param ilCertificateTemplate $certificateTemplate
	 * @return array|mixed
	 */
	private function createFormatArray(ilCertificateTemplate $certificateTemplate)
	{
		if ('' === $certificateTemplate->getCertificateHash()) {
			$format = $this->settings->get('pageformat');
			$formats = $this->pageFormats->fetchPageFormats();

			$formFieldArray = array(
				'pageformat'         => $format,
				'pagewidth'          => $formats['width'],
				'pageheight'         => $formats['height'],
				'margin_body_top'    => ilPageFormats::DEFAULT_MARGIN_BODY_TOP,
				'margin_body_right'  => ilPageFormats::DEFAULT_MARGIN_BODY_RIGHT,
				'margin_body_bottom' => ilPageFormats::DEFAULT_MARGIN_BODY_BOTTOM,
				'margin_body_left'   => ilPageFormats::DEFAULT_MARGIN_BODY_LEFT,
				'certificate_text'   => $certificateTemplate->getCertificateContent()
			);

			return $formFieldArray;
		}
		return $this->settingsFormFactory->fetchFormFieldData($certificateTemplate->getCertificateContent());
	}

}
