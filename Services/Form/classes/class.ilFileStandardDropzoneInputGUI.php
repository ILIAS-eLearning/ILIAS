<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilFileStandardDropzoneInputGUI
 *
 * A ilFileStandardDropzoneInputGUI is used in a (legacy) Form to upload Files using the Dropzone
 * of the UI-Framework introduced with ILIAS 5.3. In some cases this can be used as a
 * Drop-In-Replacement of the ilFileInputGUI, but check your usecase after. If you need an example
 * how to use it, see e.g. in UI/examples/Dropzone/File/Standard/with_usage_in_legacy_form.php
 *
 * Why make it a Drop-In-Replacement and not just replace ilFileInputGUI?
 * - There are a lot of different ways a form is handled in ILIAS, sometimes only checkInput is
 * called, sometime developers send their own error-messages and so on. The
 * ilFileStandardDropzoneInputGUI excepts some standard-behavior and would fail in some cases when
 * just replacing the ilFileInputGUI
 * - There are a lot of options in ilFileInputGUI which would be difficult to reimplement in
 * ilFileStandardDropzoneInputGUI without discussing them with all devs.
 * - Beside ilFileInputGUI there are many other File-InputGUIs with different functionality. We
 * should consolidate their use-cases first.
 *
 * Attention: This ilFileStandardDropzoneInputGUI changes the behaviour of your form when used: The
 * Form will be sent asynchronously due to limitations of dropped files (see
 * https://stackoverflow.com/questions/1017224/dynamically-set-value-of-a-file-input )
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
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
	protected $upload_url = '';
	/**
	 * @var int
	 */
	protected $max_files = 1;
	/**
	 * @var \ILIAS\Data\DataSize
	 */
	protected $max_file_size;
	/**
	 * @var string
	 */
	protected $dropzone_message = '';


	/**
	 * @return string
	 */
	public function getUploadUrl() {
		return $this->upload_url;
	}


	/**
	 * @param string $upload_url
	 *
	 * @return $this
	 */
	public function setUploadUrl($upload_url) {
		$this->upload_url = $upload_url;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getMaxFiles() {
		return $this->max_files;
	}


	/**
	 * @param int $max_files
	 */
	public function setMaxFiles($max_files) {
		$this->max_files = $max_files;
	}


	/**
	 * @return \ILIAS\Data\DataSize
	 */
	public function getMaxFilesize() {
		return $this->max_file_size;
	}


	/**
	 * @param \ILIAS\Data\DataSize $max_file_size
	 */
	public function setMaxFilesize(\ILIAS\Data\DataSize $max_file_size) {
		$this->max_file_size = $max_file_size;
	}


	/**
	 * @return string
	 */
	public function getDropzoneMessage() {
		return $this->dropzone_message;
	}


	/**
	 * @param string $dropzone_message
	 */
	public function setDropzoneMessage($dropzone_message) {
		$this->dropzone_message = $dropzone_message;
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

		$dropzone = $f->dropzone()
		              ->file()
		              ->standard($this->getUploadUrl())
		              ->withIdentifier($this->getPostVar())
		              ->withMaxFiles($this->getMaxFiles())
		              ->withMessage($this->getDropzoneMessage())
		              ->withAllowedFileTypes($this->getSuffixes());
		$dropzone = $this->handleMaxFileSize($dropzone);
		if ($this->isFileNameSelectionEnabled()) {
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


	/**
	 * @return bool
	 */
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

			$str_replace = str_replace("&amp;", "&", $parentWrapper->getFormAction());
			$this->setUploadUrl($str_replace . "&" . self::ASYNC_FILEUPLOAD . "=true");
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
		if ($this->getMaxFilesize()) {
			$dropzone = $dropzone->withFileSizeLimit($this->getMaxFilesize());
		}

		return $dropzone;
	}
}
