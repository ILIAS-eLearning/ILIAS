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
	 * @var array
	 */
	protected $allowed_file_types = [];

	/**
	 * @var int
	 */
	protected $file_size_limit = 0;

	/**
	 * @var int
	 */
	protected $max_files = 0;

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

	/**
	 * @inheritdoc
	 */
	public function withAllowedFileTypes(array $types) {
		$clone = clone $this;
		$clone->allowed_file_types = $types;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getAllowedFileTypes() {
		return $this->allowed_file_types;
	}

	/**
	 * @inheritdoc
	 */
	public function withMaxFiles($max) {
		$this->checkIntArg('max', $max);
		$clone = clone $this;
		$clone->max_files = (int) $max;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getMaxFiles() {
		return $this->max_files;
	}

	/**
	 * @inheritdoc
	 */
	public function withFileSizeLimit($limit) {
		$this->checkIntArg('limit', $limit);
		$clone = clone $this;
		$clone->file_size_limit = $limit;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getFileSizeLimit() {
		return $this->file_size_limit;
	}
}