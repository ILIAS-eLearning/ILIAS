<?php
/**
 * class RbacAdminH
 * extensions for hierachical Rbac (maybe later)
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$ 
 * 
*/
class RbacAdminH extends RbacAdmin
{
    function RbacAdminH(&$dbhandle)
    {
	$this->RbacAdmin($dbhandle);
    }
    // 
    // @access public
    // @params void
    // @return
    function addInheritance()
    {
    }
    // 
    // @access public
    // @params void
    // @return
    function deleteInheritance()
    {
    }
    // 
    // @access public
    // @params void
    // @return
    function addAscendant()
    {
    }
    // 
    // @access public
    // @params void
    // @return
    function addDescendant()
    {
    }
} // END CLASS RbacAdminH
?>