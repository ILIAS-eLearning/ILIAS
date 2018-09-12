<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron job for definition for oer harvesting
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilOerHarvesterSettings
{
	const STORAGE_IDENTIFIER = 'meta_oer';

	/**
	 * @var \ilOerHarvesterSettings
	 */
	private static $instance = null;

	/**
	 * @var \ilSetting|null
	 */
	private $storage = null;

	/**
	 * @var int
	 */
	private $target = 0;



	/**
	 * ilOerHarvesterSettings constructor.
	 */
	protected function __construct()
	{
		$this->storage = new ilSetting(self::STORAGE_IDENTIFIER);
		$this->read();
	}

	/**
	 * @return \ilOerHarvesterSettings
	 */
	public static function getInstance()
	{
		if(!self::$instance instanceof ilOerHarvesterSettings)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @param int $a_target
	 */
	public function setTarget($a_target)
	{
		$this->target = $a_target;
	}

	/**
	 * Get target
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * Save settings
	 */
	public function save()
	{
		$this->storage->set('target', $this->getTarget());
	}

	/**
	 * Read settings
	 */
	public function read()
	{
		$this->setTarget($this->storage->get('target'));
	}


}
