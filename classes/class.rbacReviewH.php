<?php
/**
 * class RbacreviewH
 * extensions for hierachical Rbac (maybe later)
 * @author Stefan Meyer <smeyer@databay.de> 
 * @version $Id$ 
 * @package rbac
 * 
*/
class RbacReviewH extends RbacReview
{

    function RbacReview(&$dbhandle)
    {
        $this->RbacReview($dbhandle);
    }
    // 
    // @access public
    // @params void
    // @return
    function authorizedUsers()
    {
    }
    // 
    // @access public
    // @params void
    // @return
    function authorizedRoles()
    {
    }
    // 
    // @access public
    // @params void
    // @return
    function rolePermissions()
    {
    }
    // 
    // @access public
    // @params void
    // @return
    function userPermissions()
    {
    }

} // END CLASS RbacReviewH
?>