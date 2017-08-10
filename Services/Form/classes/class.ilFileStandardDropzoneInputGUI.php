<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php';
include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");

/**
 * This class represents a file property in a property form.
 *
 * @author     Alex Killing <alex.killing@gmx.de>
 * @version    $Id$
 * @ingroup    ServicesForm
 */
class ilFileStandardDropzoneInputGUI extends ilFileInputGUI implements ilToolbarItem {

	const ASYNC_FILEUPLOAD = "async_fileupload";
	/**
	 * @var int
	 */
	static $count = 0;
	/**
	 * @var string
	 */
	protected $uploadUrl = '';
	/**
	 * @var int
	 */
	protected $maxFiles = 1;
	/**
	 * @var \ILIAS\Data\DataSize
	 */
	protected $maxFileSize;
	/**
	 * @var string
	 */
	protected $dropzoneMessage = '';


	/**
	 * @return string
	 */
	public function getUploadUrl() {
		return $this->uploadUrl;
	}


	/**
	 * @param string $uploadUrl
	 *
	 * @return $this
	 */
	public function setUploadUrl($uploadUrl) {
		$this->uploadUrl = $uploadUrl;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getMaxFiles() {
		return $this->maxFiles;
	}


	/**
	 * @param int $maxFiles
	 */
	public function setMaxFiles($maxFiles) {
		$this->maxFiles = $maxFiles;
	}


	/**
	 * @return \ILIAS\Data\DataSize
	 */
	public function getMaxFileSize() {
		return $this->maxFileSize;
	}


	/**
	 * @param \ILIAS\Data\DataSize $maxFileSize
	 */
	public function setMaxFileSize(\ILIAS\Data\DataSize $maxFileSize) {
		$this->maxFileSize = $maxFileSize;
	}


	/**
	 * @return string
	 */
	public function getDropzoneMessage() {
		return $this->dropzoneMessage;
	}


	/**
	 * @param string $dropzoneMessage
	 */
	public function setDropzoneMessage($dropzoneMessage) {
		$this->dropzoneMessage = $dropzoneMessage;
	}


	/**
	 * @param string $a_mode
	 *
	 * @return string
	 */
	public function render($a_mode = "") {
		global $DIC;

		$this->handleUploadURL();
		$this->handleSuffixes();

		$f = $DIC->ui()->factory();
		$r = $DIC->ui()->renderer();

		$dropzone = $f->dropzone()->file()->standard($this->getUploadUrl())->withIdentifier($this->getPostVar())->withMaxFiles($this->getMaxFiles())->withMessage($this->getDropzoneMessage())->withAllowedFileTypes($this->getSuffixes());
		$dropzone = $this->handleMaxFileSize($dropzone);
		if($this->isFileNameSelectionEnabled()) {
			$dropzone = $dropzone->withUserDefinedFileNamesEnabled(true);
		}

		$render = $r->render($dropzone);

		$n = ++ self::$count;
		$out = "<div id='ilFileStandardDropzoneInputGUIWrapper{$n}'>" . $render . '</div>';
		// We need some javascript magic
		/** @var ilTemplate $tpl */
		$tpl = $DIC['tpl'];
		$tpl->addJavaScript('./src/UI/examples/Dropzone/File/Standard/ilFileStandardDropzoneInputGUI.js');
		$tpl->addOnLoadCode("ilFileStandardDropzoneInputGUI.init('ilFileStandardDropzoneInputGUIWrapper{$n}');");

		return $out;
	}


	public function checkInput() {
		global $DIC;

		$hasUploads = $DIC->upload()->hasUploads();
		if ($this->getRequired() && !$hasUploads) {
			return false; // No file uploaded but is was required
		}

		if ($hasUploads) {
			try {
				//				$DIC->upload()->process();
				$_POST[$this->getPostVar()] = $_FILES[$this->getPostVar()];
			} catch (Exception $e) {
				return false;
			}

			return true;
		}

		return true;
	}


	protected function handleUploadURL() {
		if (!$this->getUploadUrl()) {
			$parentWrapper = $this;
			while (!$parentWrapper instanceof ilPropertyFormGUI && $parentWrapper !== null) {
				$parentWrapper = $parentWrapper->getParent();
			}

			$this->setUploadUrl(str_replace("&amp;", "&", $parentWrapper->getFormAction())
			                    . "&async=true");
		}
	}


	protected function handleSuffixes() {
		if (!is_array($this->getSuffixes())) {
			$this->setSuffixes(array());
		}
	}


	/**
	 * @param $dropzone
	 *
	 * @return ILIAS\UI\Component\Dropzone\File\Standard
	 */
	protected function handleMaxFileSize($dropzone) {
		if ($this->getMaxFileSize()) {
			$dropzone = $dropzone->withFileSizeLimit($this->getMaxFileSize());
		}

		return $dropzone;
	}
}
