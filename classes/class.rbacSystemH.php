<?php
/**
* Class RbacSystemH
* extensions for hierachical Rbac (maybe later)
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends RbacSystem 
* @package rbac
*/
class RbacSystemH extends RbacSystem
{
	/**
	* Constructor
	* @param object ilias
	*/
    function RbacSystemH(&$a_ilias)
    {
        $this->RbacSystem($a_ilias);
    }

	/**
	* @access public
	*/
    function createSession()
    {
	
    }

	/**
	* @access public
	*/
    function addActiveRole()
    {

    }
} // END class.RbacSystemH
?>