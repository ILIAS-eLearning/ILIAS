<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract learning history provider
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
abstract class ilAbstractLearningHistoryProvider
{
	/**
	 * User id. This is the user the history will be retrieved for.
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var ilLearningHistoryFactory
	 */
	protected $factory;

	/**
	 * Constructor
	 * @param int $user_id
	 * @param ilLearningHistoryFactory $factory
	 * @param iLLanguage $lng
	 */
	public function __construct($user_id, $factory, $lng)
	{
		$this->user_id = $user_id;
		$this->factory = $factory;
		$this->lng = $lng;
	}

	/**
	 * Get user id
	 *
	 * @param
	 * @return
	 */
	protected function getUserId()
	{
		return $this->user_id;
	}

	/**
	 * Get factory
	 *
	 * @return ilLearningHistoryFactory
	 */
	protected function getFactory()
	{
		return $this->factory;
	}

	/**
	 * Get language object
	 *
	 * @return ilLanguage
	 */
	protected function getLanguage()
	{
		return $this->lng;
	}



}