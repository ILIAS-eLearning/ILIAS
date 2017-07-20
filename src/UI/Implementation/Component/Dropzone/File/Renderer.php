<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

/**
 * Class Renderer
 *
 * Renderer implementation for file dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
class Renderer extends AbstractComponentRenderer {

	/**
	 * @var $renderer DefaultRenderer
	 */
	private $renderer;


	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			\ILIAS\UI\Component\Dropzone\File\Standard::class,
			\ILIAS\UI\Component\Dropzone\File\Wrapper::class,
		);
	}


	/**
	 * @inheritdoc
	 */
	public function render(Component $component, \ILIAS\UI\Renderer $default_renderer) {
		$this->checkComponent($component);
		$this->renderer = $default_renderer;
		if ($component instanceof \ILIAS\UI\Component\Dropzone\File\Wrapper) {
			return $this->renderWrapper($component);
		}
		if ($component instanceof \ILIAS\UI\Component\Dropzone\File\Standard) {
			return $this->renderStandard($component);
		}
	}


	/**
	 * @inheritDoc
	 */
	public function registerResources(ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register("./src/UI/templates/js/libs/jquery.dragster.js");
		$registry->register("./libs/npm/node_modules/fine-uploader/fine-uploader/fine-uploader.core.js");
		$registry->register("./src/UI/templates/js/Dropzone/File/uploader.js");
		$registry->register("./src/UI/templates/js/Dropzone/File/dropzone.js");
	}


	/**
	 * @param \ILIAS\UI\Component\Dropzone\File\Standard $dropzone
	 * @return string
	 */
	private function renderStandard(\ILIAS\UI\Component\Dropzone\File\Standard $dropzone) {
		$dropzoneId = $this->createId();
		$tpl = $this->getTemplate("tpl.standard-dropzone.html", true, true);
		$tpl->setVariable("ID", $dropzoneId);
		// Set default message if empty
		$message = ($dropzone->getMessage()) ? $dropzone->getMessage() : $this->txt('drag_files_here');
		$tpl->setVariable("MESSAGE", $message);
		$button = $dropzone->getUploadButton();
		if ($button) {
			$button = $button->withUnavailableAction()->withOnLoadCode(function ($id) use ($dropzoneId) {
				return "$ (function() {il.UI.uploader.bindUploadButton('{$dropzoneId}', $('#{$id}'));});";
			});
			$tpl->setCurrentBlock('with_upload_button');
			$tpl->setVariable('BUTTON', $this->renderer->render($button));
			$tpl->parseCurrentBlock();
		}
		$tplUploadFileList = $this->getFileListTemplate($dropzoneId, $dropzone);
		$tpl->setVariable('FILELIST', $tplUploadFileList->get());
		$tpl->setVariable('DIVIDER_MESSAGE', $this->txt('logic_or'));
		$tpl->setVariable('SELECT_FILES_LABEL', $this->txt('select_files_from_computer'));
		$this->initializeJavascript($dropzone, $dropzoneId);
		return $tpl->get();
	}

	/**
	 * @param \ILIAS\UI\Component\Dropzone\File\Wrapper $dropzone
	 * @return string
	 */
	private function renderWrapper(\ILIAS\UI\Component\Dropzone\File\Wrapper $dropzone) {
		$dropzoneId = $this->createId();
		$tplUploadFileList = $this->getFileListTemplate($dropzoneId, $dropzone);
		// Create the roundtrip modal which displays the uploaded files
		$uploadButton = $this->getUIFactory()->button()->primary($this->txt('upload'), '')
			->withUnavailableAction()
			->withOnLoadCode(function ($id) use ($dropzoneId) {
				return "$ (function() {il.UI.uploader.bindUploadButton('{$dropzoneId}', $('#{$id}'));});";
			});
		$modal = $this->getUIFactory()->modal()
			->roundtrip($this->txt('upload'), $this->getUIFactory()->legacy($tplUploadFileList->get()))
			->withActionButtons([$uploadButton]);
		$tpl = $this->getTemplate("tpl.wrapper-dropzone.html", true, true);
		$tpl->setVariable('ID', $dropzoneId);
		$tpl->setVariable('CONTENT', $this->renderer->render($dropzone->getContent()));
		$tpl->setVariable('MODAL', $this->renderer->render($modal));
		$dropzone = $dropzone->withOnDrop($modal->getShowSignal());
		$this->initializeJavascript($dropzone, $dropzoneId);
		return $tpl->get();
	}

	/**
	 * @param \ILIAS\UI\Component\Dropzone\File\File $dropzone
	 * @param $id
	 */
	private function initializeJavascript(\ILIAS\UI\Component\Dropzone\File\File $dropzone, $id) {
		$signals = array_map(function ($triggeredSignal) {
			/** @var $triggeredSignal TriggeredSignalInterface */
			return array(
				'id' => $triggeredSignal->getSignal()->getId(),
				'options' => $triggeredSignal->getSignal()->getOptions()
			);
		}, $dropzone->getTriggeredSignals());

		$options = json_encode([
			'id' => $id,
			'registeredSignals' => $signals,
			'uploadUrl' => $dropzone->getUploadUrl(),
			'allowedFileTypes' => $dropzone->getAllowedFileTypes(),
			'fileSizeLimit' => $dropzone->getFileSizeLimit(),
			'maxFiles' => $dropzone->getMaxFiles(),
			'identifier' => $dropzone->getIdentifier(),
		]);
		$type = get_class($dropzone);
		$this->getJavascriptBinding()->addOnLoadCode(
			"il.UI.dropzone.initializeDropzone('{$type}', JSON.parse('{$options}'));"
		);
	}

	/**
	 * @param string $uploadId
	 * @param \ILIAS\UI\Component\Dropzone\File\File $dropzone
	 * @return \ILIAS\UI\Implementation\Render\Template
	 */
	private function getFileListTemplate($uploadId, \ILIAS\UI\Component\Dropzone\File\File $dropzone) {
		$tplUploadFileList = $this->getTemplate('tpl.upload-file-list.html', true, true);
		$tplUploadFileList->setVariable('UPLOAD_ID', $uploadId);
		if ($this->renderMetaData($dropzone)) {
			$tplUploadFileList->touchBlock('with_edit_button');
			$tplUploadFileList->setCurrentBlock('with_metadata');
			if ($dropzone->allowCustomFileNames()) {
				$tplUploadFileList->touchBlock('with_filename');
			}
			if ($dropzone->allowFileDescriptions()) {
				$tplUploadFileList->touchBlock('with_description');
			}
			$tplUploadFileList->parseCurrentBlock();
		}
		return $tplUploadFileList;
	}

	/**
	 * @param \ILIAS\UI\Component\Dropzone\File\File $dropzone
	 * @return bool
	 */
	private function renderMetaData(\ILIAS\UI\Component\Dropzone\File\File $dropzone) {
		return ($dropzone->allowCustomFileNames() || $dropzone->allowFileDescriptions());
	}

}