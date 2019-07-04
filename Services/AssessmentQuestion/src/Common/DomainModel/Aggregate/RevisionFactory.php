<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Common;

/**
 * Class RevisionFactory
 *
 * Generates Revision safe Revision id for IsRevisable object
 *
 * @package ILIAS\AssessmentQuestion\Common
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface RevisionFactory {

	/**
	 * @param IsRevisable $entity
	 *
	 * Revisable object will be stamped with a valid RevisionId
	 */
	public static function SetRevisionId(IsRevisable $entity);


	/**
	 * @param IsRevisable $entity
	 *
	 * check if the RevisionId of an object and his data match, if not the object
	 * is corrupt or has been tampered with
	 *
	 * @return bool
	 */
	public static function ValidateRevision(IsRevisable $entity): bool;
}