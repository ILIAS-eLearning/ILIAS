<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilAwarenessData
{
	protected $collection;

	/**
	 * Set collection
	 *
	 * @param ilAwarenessUserCollection $a_val collection
	 */
	function setUserCollection($a_val)
	{
		$this->collection = $a_val;
	}

	/**
	 * Get collection
	 *
	 * @return ilAwarenessUserCollection collection
	 */
	function getUserCollection()
	{
		return $this->collection;
	}

	/**
	 * Get data
	 *
	 * @return array array of data objects
	 */
	function getData()
	{
		$user_ids = $this->collection->getUsers();

		$names = ilUserUtil::getNamePresentation($user_ids, true,
			false, "", false, false, true, true);

		$data = array();
		foreach ($names as $n)
		{
			$obj = new stdClass;
			$obj->lastname = $n["lastname"];
			$obj->firstname = $n["firstname"];
			$obj->login = $n["login"];
			$obj->id = $n["id"];
			$obj->img = $n["img"];
			$obj->public_profile = $n["public_profile"];
			$data[] = $obj;
		}

		return $data;
	}

}

?>