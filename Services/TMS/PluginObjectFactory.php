<?php

/**
 * This factory give the oportunity to get an instance of plugin objects.
 * Feel free to complete.
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-ans-training.de>
 */
trait PluginObjectFactory {
	/**
	 * Get an instance of course creation plugin
	 *
	 * @return ilCourseCreationPlugin | null
	 */
	protected function getCourseCreationPlugin() {
		return $this->getPluginFor("xccr");
	}

	/**
	 * Returns an instance of requested plugin
	 *
	 * @param string 	$plugin_id
	 *
	 * @return \ilPlugin | null
	 */
	private function getPluginFor($plugin_id) {
		require_once("Services/Component/classes/class.ilPluginAdmin.php");
		if (!\ilPluginAdmin::isPluginActive($plugin_id)) {
			return null;
		}

		return \ilPluginAdmin::getPluginObjectById($plugin_id);
	}
}