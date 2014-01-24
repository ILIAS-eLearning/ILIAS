<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
 * @classDescription Settings for members view
 * @author Stefan Meyer <meyer@leifos.com>
 * 
 */
class ilMemberViewSettings
{
	private static $instance = null;
	
	private $active = false;
	private $enabled = false;
	private $container = null;
	private $container_items = array();
	
	/**
	 * Constructor (singleton)
	 * @return 
	 */
	private function __construct()
	{
		$this->read();
	}
	
	/**
	 * Get instance
	 * @return object ilMemberViewSettings
	 */
	public static function getInstance()
	{
		if(self::$instance != null)
		{
			return self::$instance;
		}
		return self::$instance = new ilMemberViewSettings();
	}
	
    /**
     * Returns $container.
     * @see ilMemberView::$container
     */
    public function getContainer()
    {
        return $this->container;
    }
    
    /**
     * Sets $container.
     * @param object $container
     * @see ilMemberView::$container
     */
    public function setContainer($container)
    {
        $this->container = $container;
		$_SESSION['member_view_container'] = $this->container;
		$_SESSION['il_cont_admin_panel'] = false;
    }

	/**
	 * Check if member view currently enabled
	 * @return bool
	 */
	public function isActive()
	{
		global $tree;
		
		if(!$this->active)
		{
			// Not active
			return false;
		}
				
		$ref_id = $_GET['ref_id'] ? $_GET['ref_id'] : null;
		if(!$ref_id)
		{
			$target_arr = explode('_',(string) $_GET['target']);
			$ref_id = $target_arr[1] ? $target_arr[1] : null;		
		}		

		if(!$ref_id)
		{
			// No ref id given => mail, search, personal desktop menu in other tab
			return false;
		}
		
		if(!in_array($ref_id,$this->container_items) and 
			$this->getContainer() != $ref_id) 
		{
			// outside of course
			return false;
		}
		return true;
	}
	
	/**
	 * Check if member view is currently enabled for given ref id
	 * @param int $a_ref_id
	 * @return bool
	 */
	public function isActiveForRefId($a_ref_id)
	{
		if(!$this->active || !(int)$a_ref_id)
		{
			// Not active
			return false;
		}
		
		if(!in_array($a_ref_id,$this->container_items) and 
			$this->getContainer() != $a_ref_id) 
		{
			// outside of course
			return false;
		}
		return true;
	}
	
	/**
	 * Enable member view for this session and container.
	 * @return 
	 * @param int $a_ref_id Reference Id of course or group
	 * @exception IllegalArgumentException Thrown if reference id is not a course or group
	 */
	public function activate($a_ref_id)
	{
		$this->active = true;
		$this->setContainer($a_ref_id);
	}
	
	/**
	 * Deactivate member view
	 * @return 
	 */
	public function deactivate()
	{
		$this->active = false;
		$this->container = null;
		unset($_SESSION['member_view_container']);
	}
	
	/**
	 * Toggle activation status
	 * @return 
	 * @param int $a_ref_id
	 * @param bool $a_activation
	 */
	public function toggleActivation($a_ref_id,$a_activation)
	{
		if($a_activation)
		{
			return $this->activate($a_ref_id);
		}
		else
		{
			return $this->deactivate($a_ref_id);
		}
	}
	
	/**
	 * Check if members view is enabled in the administration
	 * @return bool active
	 */
	public function isEnabled()
	{
		return (bool) $this->enabled;
	}
	
	/**
	 * Read settings
	 * @return void
	 */
	protected function read()
	{
		global $ilSetting,$tree;
		
		// member view is always enabled 
		// (2013-06-18, http://www.ilias.de/docu/goto_docu_wiki_1357_Reorganising_Administration.html) 		
		
		// $this->enabled = $ilSetting->get('preview_learner');		
		$this->enabled = true;
		
		if(isset($_SESSION['member_view_container']))
		{
			$this->active = true;
			$this->container = (int) $_SESSION['member_view_container'];
			$this->container_items = $tree->getSubTreeIds($this->getContainer());
		}
	} 
}
?>