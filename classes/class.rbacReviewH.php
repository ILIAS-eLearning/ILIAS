<?php
/**
 * class RbacreviewH
 * extensions for hierachical Rbac (maybe later)
 * @author Stefan Meyer <smeyer@databay.de> 
 * @version $Id$ 
 * @package rbac
*/
class RbacReviewH extends RbacReview
{
	/**
	* constructor ??
	* @param object db
	*/
    function RbacReviewH(&$dbhandle)
    {
        $this->RbacReview($dbhandle);
    }

    /**
    * @access public
	*/
    function authorizedUsers()
    {
    }
    /** 
    * @access public
	*/
    function authorizedRoles()
    {
    }
	
	/**
    *
    * @access public
	*/
    function rolePermissions()
    {
    }

    /**
	* @access public
	*/
    function userPermissions()
    {
    }

} // end class
?>