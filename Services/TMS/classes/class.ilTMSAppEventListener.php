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

		if ($a_component === "Modules/Category") {
			if ($a_event === "tms_update") {
				if ($a_parameter["show_in_cockpit"] === true
					&& self::noExistingProvider($a_parameter["object"])
				) {
					self::createUnboundCategoryProvider($a_parameter["object"]);
				}

				if ($a_parameter["show_in_cockpit"] === false
					&& !self::noExistingProvider($a_parameter["object"])
				) {
					self::deleteUnboundCategoryProvider($a_parameter["object"]);
				}
			} elseif ($a_event === "delete"){
				self::deleteUnboundCategoryProvider($a_parameter["object"]);
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

	static public function createUnboundCategoryProvider(\ilObject $cat) {
		require_once(__DIR__."/UnboundCategoryProvider.php");
		$provider_db = self::getProviderDB();
		$provider_db->createSeparatedUnboundProvider($cat, "root", UnboundCategoryProvider::class, __DIR__."/UnboundCategoryProvider.php");
	}

	static public function deleteUnboundCategoryProvider(\ilObject $cat) {
		$provider_db = self::getProviderDB();
		$unbound_providers = $provider_db->unboundProvidersOf($cat);
		foreach ($unbound_providers as $unbound_provider) {
			$provider_db->delete($unbound_provider, $cat);
		}
	}

	static protected function noExistingProvider($cat) {
		require_once(__DIR__."/UnboundCategoryProvider.php");
		$provider_db = self::getProviderDB();
		$unbound_providers = $provider_db->unboundProvidersOf($cat);

		foreach ($unbound_providers as $unbound_provider) {
			if($unbound_provider instanceof UnboundCategoryProvider) {
				return false;
			}
		}

		return true;
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
