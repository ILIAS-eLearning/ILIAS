<?php

namespace ILIAS\AssessmentQuestion\Common\Entity;

/**
 * Interface IsRevisable
 *
 * @package ILIAS\AssessmentQuestion\Common\Entity
 */
interface IsRevisable
{

	/**
	 * @return RevisionId revision id of object
	 */
	public function getRevisionId(): RevisionId;


	/**
	 * @param RevisionId $id
	 *
	 * Revision id is only to be set by the RevisionFactory when generating a
	 * revision or by the persistance layer when loading an object
	 *
	 * @return mixed
	 */
	public function setRevisionId(RevisionId $id);


	/**
	 * @return string
	 *
	 * Name of the revision used by the RevisionFactory when generating a revision
	 * Using of Creation Date and or an increasing Number are encouraged
	 *
	 */
	public function getRevisionName(): string;


	/**
	 * @return array
	 *
	 * Data used for signing the revision, so this method needs to to collect all
	 * Domain specific data of an object and return it as an array
	 */
	public function getRevisionData(): array;
}