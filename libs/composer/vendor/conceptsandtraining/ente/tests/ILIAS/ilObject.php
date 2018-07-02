<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

/**
 * Essentials of ILIAS object for this framework.
 */
abstract class ilObject {
	/**
	* get object id
	* @access	public
	* @return	integer	object id
	*/
    public function getId() {
        assert(false);
    }

	/**
	* get reference id
	* @access	public
	* @return	integer	reference id
	*/
    public function getRefId() {
        assert(false);
    }

	/**
	* get object type
	* @access	public
	* @return	string		object type
	*/
	public function getType() {
        assert(false);
	}
}
