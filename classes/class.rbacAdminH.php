<?php
// CLASS Rbac
// 
// Admin Functions for Hierachical Core RBAC
// extends RbacAdmin
// @author Stefan Meyer smeyer@databay.de


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