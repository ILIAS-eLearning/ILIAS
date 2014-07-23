<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * interface for modular web access checker
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
 *
 * @ingroup ServicesWebAccessChecker
 */
interface ilComponentWebAccessChecker
{
	/**
	 * Check if current (image) path is valid
	 * 
	 * @param array $a_path
	 * @return bool
	 */
	public function isValidPath(array $a_path);
	
	/**
	 * Get repository object id from path if any
	 * 
	 * If an object id is returned a simple RBAC-based access check is done
	 * 
	 * @return int
	 */
	public function getRepositoryObjectId();
	
	/**
	 * Custom access method
	 * 
	 * @param array $a_user_ids
	 * @return bool
	 */
	public function checkAccess(array $a_user_ids);
}

