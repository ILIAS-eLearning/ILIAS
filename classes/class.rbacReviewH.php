<?php
/**
* class RbacreviewH
* extensions for hierachical Rbac (maybe later)
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends RbacReview
* @package rbac
*/
class RbacReviewH extends RbacReview
{
	/**
	* Constructor
	* @access	public
	*/
	function RbacReviewH()
	{
		$this->RbacReview();
	}

	/**
	* DESCRIPTION MISSING
	* @access	public
	*/
	function authorizedUsers()
	{

	}

	/**
	* DESCRIPTION MISSING
	* @access	public
	*/
	function authorizedRoles()
	{

	}
	
	/**
	* DESCRIPTION MISSING
	* @access	public
	*/
	function rolePermissions()
	{

	}
} // END class RbacReviewH
?>