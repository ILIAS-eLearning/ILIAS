<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history entry
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
class ilLearningHistoryEntry
{
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $icon_path;

	/**
	 * @var int
	 */
	protected $ts;

	/**
	 * Constructor
	 * @param string $title
	 * @param string $icon_path
	 * @param int $ts unix timestamp
	 */
	public function __construct($title, $icon_path, $ts)
	{

	}

}