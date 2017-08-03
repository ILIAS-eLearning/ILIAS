<?php
/**
 * Class Dropzone
 *
 * Basic implementation for dropzones. Provides functionality which are needed
 * for all dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.3
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\Data\DataSize;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Triggerer;

abstract class File implements \ILIAS\UI\Component\Dropzone\File\File {

	use Triggerer;
	use ComponentHelper;

	const DROP_EVENT = "drop";

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var array
	 */
	protected $allowed_file_types = [];

	/**
	 * @var DataSize
	 */
	protected $file_size_limit;

	/**
	 * @var int
	 */
	protected $max_files = 0;

	/**
	 * @var bool
	 */
	protected $custom_file_names = false;

	/**
	 * @var bool
	 */
	protected $file_descriptions = false;

	/**
	 * @var string
	 */
	protected $identifier = 'files';

	/**
	 * @param string $url
	 */
	public function __construct($url) {
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
		$clone->max_files = (int)$max;
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
	public function withFileSizeLimit(DataSize $limit) {
		$this->checkArgInstanceOf('limit', $limit, DataSize::class);
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

	/**
	 * @inheritdoc
	 */
	public function withUserDefinedFileNamesEnabled($state) {
		$clone = clone $this;
		$clone->custom_file_names = (bool)$state;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function allowsUserDefinedFileNames() {
		return $this->custom_file_names;
	}

	/**
	 * @inheritdoc
	 */
	public function withUserDefinedDescriptionEnabled($state) {
		$clone = clone $this;
		$clone->file_descriptions = (bool)$state;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function allowsUserDefinedFileDescriptions() {
		return $this->file_descriptions;
	}

	/**
	 * @inheritdoc
	 */
	public function withIdentifier($identifier) {
		$this->checkStringArg('identifier', $identifier);
		$clone = clone $this;
		$clone->identifier = $identifier;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getIdentifier() {
		return $this->identifier;
	}


	/**
	 * @inheritDoc
	 */
	public function withOnDrop(Signal $signal) {
		return $this->addTriggeredSignal($signal, self::DROP_EVENT);
	}

	/**
	 * @inheritDoc
	 */
	public function withAdditionalDrop(Signal $signal) {
		return $this->appendTriggeredSignal($signal, self::DROP_EVENT);
	}
}