<?php
/**
* class RbacAdminH
* extensions for hierachical Rbac (maybe later)
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends RbacAdmin
* @package rbac
*/
class RbacAdminH extends RbacAdmin
{
	/**
	* database handle
	* @param object db
	*/
    function RbacAdminH(&$dbhandle)
    {
		$this->RbacAdmin($dbhandle);
    }

    /** 
    * @access public
	*/
    function addInheritance()
    {
    }
	
    /**
	* @access public
	*/
    function deleteInheritance()
    {
    }

    /**
	* @access public
	*/
    function addAscendant()
    {
    }

    /**
	* @access public
	*/
    function addDescendant()
    {
    }
} // END class.RbacAdminH
?>