<?php

namespace ILIAS\Changelog\Infrastructure\AR;


use ActiveRecord;

/**
 * Class EventAR
 * @package ILIAS\Changelog\Infrastructure\AR
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class EventAR extends ActiveRecord {

	const TABLE_NAME = 'changelog_events';


	protected $id;

	protected $type;

	protected $user_id;

	protected $timestamp;
}