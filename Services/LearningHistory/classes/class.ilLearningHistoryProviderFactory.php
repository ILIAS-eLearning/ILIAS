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
	protected static $providers = array(
		array (
			"component" => "Services/Tracking",
			"class" => "ilTrackingLearningHistoryProvider"
		)
	);
	
	/**
	 * Constructor
	 */
	protected function __construct()
	{
		
	}

	/**
	 * Get all learning history providers
	 *
	 * @param bool $a_active_only
	 * @return ilLearningHistoryProviderInterface[]
	 */
	public function getAllProviders($a_active_only = false)
	{
		$providers = array();

		foreach (self::$providers as $p)
		{
			$dir = (isset($p["dir"]))
				? $p["dir"]
				: "classes";
			include_once("./".$p["component"]."/".$dir."/class.".$p["class"].".php");

			/** @var ilLearningHistoryProviderInterface $p */
			$p = new $p["class"]();
			if (!$a_active_only || $p->isActive())
			{
				$providers[] = $p;
			}
		}

		return $providers;
	}

}

?>