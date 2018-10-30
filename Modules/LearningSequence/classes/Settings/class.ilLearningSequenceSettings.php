<?php

declare(strict_types=1);

/**
 * Settings for an LSO (like abstract, extro)
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLearningSequenceSettings
{

	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var string
	 */
	protected $abstract;

	/**
	 * @var string
	 */
	protected $extro;

	/**
	 * @var string|null
	 */
	protected $abstract_image;

	/**
	 * @var string|null
	 */
	protected $extro_image;

	/**
	 * @var array
	 */
	protected $uploads = [];

	/**
	 * @var array
	 */
	protected $deletions = [];

	/**
	 * @var bool
	 */
	protected $online;

	/**
	 * @var bool
	 */
	protected $members_gallery;

	/**
	 * @var string|null
	 */
	protected $activation_start;

	/**
	 * @var string|null
	 */
	protected $activation_end;


	public function __construct(
		int $obj_id,
		string $abstract = '',
		string $extro = '',
		string $abstract_image = null,
		string $extro_image = null,
		bool $online = false,
		bool $members_gallery = false,
		string $activation_start = null,
		string $activation_end = null
	) {
		$this->obj_id = $obj_id;
		$this->abstract = $abstract;
		$this->extro = $extro;
		$this->abstract_image = $abstract_image;
		$this->extro_image = $extro_image;
		$this->online = $online;
		$this->members_gallery = $members_gallery;
		$this->activation_start = $activation_start;
		$this->activation_end = $activation_end;
	}

	public function getObjId(): int
	{
		return $this->obj_id;
	}

	public function getAbstract(): string
	{
		return $this->abstract;
	}

	public function withAbstract(string $abstract): ilLearningSequenceSettings
	{
		$clone = clone $this;
		$clone->abstract = $abstract;
		return $clone;
	}

	public function getExtro(): string
	{
		return $this->extro;
	}

	public function withExtro(string $extro): ilLearningSequenceSettings
	{
		$clone = clone $this;
		$clone->extro = $extro;
		return $clone;
	}

	public function getAbstractImage()
	{
		return $this->abstract_image;
	}

	public function withAbstractImage(string $path=null): ilLearningSequenceSettings
	{
		$clone = clone $this;
		$clone->abstract_image = $path;
		return $clone;
	}

	public function getExtroImage()
	{
		return $this->extro_image;
	}

	public function withExtroImage(string $path=null): ilLearningSequenceSettings
	{
		$clone = clone $this;
		$clone->extro_image = $path;
		return $clone;
	}

	public function getUploads(): array
	{
		return $this->uploads;
	}

	public function withUpload(array $upload_info, string $which): ilLearningSequenceSettings
	{
		$clone = clone $this;
		$clone->uploads[$which] = $upload_info;
		return $clone;
	}

	public function getDeletions(): array
	{
		return $this->deletions;
	}

	public function withDeletion(string $which): ilLearningSequenceSettings
	{
		$clone = clone $this;
		$clone->deletions[] = $which;
		return $clone;
	}

	public function getIsOnline(): bool
	{
		return $this->online;
	}

	public function withIsOnline(bool $online): ilLearningSequenceSettings
	{
		$clone = clone $this;
		$clone->online = $online;
		return $clone;
	}

	public function getActivationStart()
	{
		return $this->activation_start;
	}

	public function withActivationStart(string $activation_start): ilLearningSequenceSettings
	{
		$clone = clone $this;
		$clone->activation_start = $activation_start;
		return $clone;
	}

	public function getActivationEnd()
	{
		return $this->activation_end;

	}

	public function withActivationEnd(string $activation_end): ilLearningSequenceSettings
	{
		$clone = clone $this;
		$clone->activation_end = $activation_end;
		return $clone;
	}

	public function getMembersGallery(): bool
	{
		return $this->members_gallery;
	}

	public function withMembersGallery(bool $members_gallery): ilLearningSequenceSettings
	{
		$clone = clone $this;
		$clone->members_gallery = $members_gallery;
		return $clone;
	}
}
