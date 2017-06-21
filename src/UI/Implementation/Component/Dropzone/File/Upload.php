<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Component;

/**
 * Class UploadWrapper
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
class Upload extends Wrapper implements \ILIAS\UI\Component\Dropzone\File\Upload {

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @param Component|Component[] $content
	 * @param string $url
	 */
	public function __construct($content, $url) {
		parent::__construct($content);
		$this->checkStringArg('url', $url);
		$this->url = $url;
	}

	/**
	 * @inheritdoc
	 */
	public function withUploadUrl($url) {
		$this->checkStringArg('url', $url);
		$clone = clone $this;
		$clone->url = $url;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getUploadUrl() {
		return $this->url;
	}
}