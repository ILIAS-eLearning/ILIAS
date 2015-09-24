<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Represents a set of collected features
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessFeatureCollection
{
	protected $features = array();

	/**
	 * Get instance
	 *
	 * @return ilAwarenessUserCollection user collection
	 */
	static function getInstance()
	{
		return new ilAwarenessFeatureCollection();
	}

	/**
	 * Add feature
	 *
	 * @param ilAwarenessFeature $a_feature feature object
	 */
	function addFeature(ilAwarenessFeature $a_feature)
	{
		$this->features[] = $a_feature;
	}

	/**
	 * Get users
	 *
	 * @return array array of user ids (integer)
	 */
	function getFeatures()
	{
		return $this->features;
	}


}

?>