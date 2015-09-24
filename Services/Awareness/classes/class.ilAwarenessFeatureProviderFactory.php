<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for awareness feature providers
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessFeatureProviderFactory
{
	protected static $providers = array(
		array (
			"component" => "Services/Contact/BuddySystem",
			"class" => "ilAwarenessContactsFeatureProvider"
		),
		array (
			"component" => "Services/Awareness",
			"class" => "ilAwarenessMailFeatureProvider"
		),
		array (
			"component" => "Services/Awareness",
			"class" => "ilAwarenessUserFeatureProvider"
		),
		array (
			"component" => "Services/Awareness",
			"class" => "ilAwarenessWorkspaceFeatureProvider"
		),
		array (
			"component" => "Services/Awareness",
			"class" => "ilAwarenessChatFeatureProvider"
		)

	);

	/**
	 * Get all awareness providers
	 *
	 * @return array of ilAwarenessProvider all providers
	 */
	static function getAllProviders()
	{
		$providers = array();

		foreach (self::$providers as $p)
		{
			$dir = (isset($p["dir"]))
				? $p["dir"]
				: "classes";
			include_once("./".$p["component"]."/".$dir."/class.".$p["class"].".php");
			$providers[] = new $p["class"]();
		}

		return $providers;
	}

}

?>