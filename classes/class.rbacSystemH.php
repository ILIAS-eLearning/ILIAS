<?php
// CLASS RbaSystemH
// 
// System Functions for Hierachical Core RBAC
//
// @author Stefan Meyer smeyer@databay.de
// 
class RbacSystemH extends RbacSystem
{
    function RbacSystemH(&$dbhandle)
    {
        $this->RbacSystem($dbhandle);
    }
    // 
    // @access public
    // @params void
    // @return
    function createSession()
    {
    }
    // 
    // @access public
    // @params void
    // @return
    function addActiveRole()
    {
    }
} // END CLASS RbacSystemH
?>