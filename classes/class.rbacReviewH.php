<?php
/**
* class RbacreviewH
* extensions for hierachical Rbac (maybe later)
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$ 
* @package rbac
* @extends RbacReview
*/
class RbacReviewH extends RbacReview
{
	/**
	* Constructor
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
	* @param void
	* @access public
	*/
    function userPermissions()
    {
    }

} // end class
?>