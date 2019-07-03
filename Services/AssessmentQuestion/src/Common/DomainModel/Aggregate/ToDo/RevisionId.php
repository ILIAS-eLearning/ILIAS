<?php

namespace ILIAS\AssessmentQuestion\Common\Entity;

/**
 * Class RevisionId
 *
 * Is a RevisionId where the key has the value name#:#hash where generation of
 * a new key at the factory with the same data and name will generate the same
 * key, so the revisionId allows to validate that the data of the object is
 * valid for that revision
 *
 * @package ILIAS\AssessmentQuestion\Common\Entity
 */
class RevisionId {
	/** @var string */
	private $key;

	public function __construct(string $key)
	{
		$this->key = $key;
	}


	public function GetKey() : string
	{
		return $this->key;
	}
}