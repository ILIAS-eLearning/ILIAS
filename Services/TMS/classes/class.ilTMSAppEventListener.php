<?php
/* Copyright (c) 2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author Richard Klees <richard.klees@concepts-and-training.de>
*/
class ilTMSAppEventListener
{
	/**
	 * Handle an event in a listener.
	 *
	 * @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
	 * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
	 * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
	 */
	static function handleEvent($a_component, $a_event, $a_parameter)
	{
		if ($a_component === "Modules/Course") {
			if ($a_event === "create") {
				self::createUnboundCourseProvider($a_parameter["object"]);
			}
			elseif ($a_event === "delete") {
				self::deleteUnboundCourseProvider($a_parameter["object"]);
			}
		}
	}

	static public function createUnboundCourseProvider(\ilObject $crs) {
		require_once(__DIR__."/UnboundCourseProvider.php");
		$provider_db = self::getProviderDB();
		$provider_db->createSeparatedUnboundProvider($crs, "crs", UnboundCourseProvider::class, __DIR__."/UnboundCourseProvider.php");
	}

	static public function deleteUnboundCourseProvider(\ilObject $crs) {
		$provider_db = self::getProviderDB();
		$unbound_providers = $provider_db->unboundProvidersOf($crs);
		foreach ($unbound_providers as $unbound_provider) {
			$provider_db->delete($unbound_provider, $crs);
		}
	}

	static protected function getProviderDB() {
		static $provider_db = null;

		if ($provider_db === null) {
			global $DIC;
			$provider_db = new \CaT\Ente\ILIAS\ilProviderDB
				( $DIC["ilDB"]
				, $DIC["tree"]
				, $DIC["ilObjDataCache"]
				, $DIC
				);
		}
		return $provider_db;
	}
}
