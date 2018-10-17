<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history providers factory
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
class ilLearningHistoryProviderFactory
{
	/**
	 * @var ilLearningHistoryService
	 */
	protected $service;

	/**
	 * @var array
	 */
	protected static $providers = array(
		array (
			"component" => "Services/Tracking",
			"dir" => "classes/learning_history",
			"class" => "ilTrackingLearningHistoryProvider"
		),
		array (
			"component" => "Services/Badge",
			"dir" => "LearningHistory/classes",
			"class" => "ilBadgeLearningHistoryProvider"
		),
		array (
			"component" => "Services/Skill",
			"dir" => "LearningHistory/classes",
			"class" => "ilSkillLearningHistoryProvider"
		),
		array (
			"component" => "Modules/Course",
			"dir" => "classes/LearningHistory",
			"class" => "ilCourseLearningHistoryProvider"
		),
		array (
			"component" => "Services/Certificate",
			"dir" => "classes/LearningHistory",
			"class" => "ilCertificateLearningHistoryProvider"
		)
	);
	
	/**
	 * Constructor
	 */
	public function __construct($service)
	{
		$this->service = $service;
	}

	/**
	 * Get all learning history providers
	 *
	 * @param bool $active_only get only active providers
	 * @param int $user_id get instances for user with user id
	 * @return ilLearningHistoryProviderInterface[]
	 */
	public function getAllProviders($active_only = false, $user_id = null)
	{
		$providers = array();

		if ($user_id == 0)
		{
			$user_id = $this->service->user()->getId();
		}

		foreach (self::$providers as $p)
		{
			$dir = (isset($p["dir"]))
				? $p["dir"]
				: "classes";
			include_once("./".$p["component"]."/".$dir."/class.".$p["class"].".php");

			/** @var ilLearningHistoryProviderInterface $p */
			$p = new $p["class"]($user_id, $this->service->factory(), $this->service->language());
			if (!$active_only || $p->isActive())
			{
				$providers[] = $p;
			}
		}

		return $providers;
	}

}

?>
