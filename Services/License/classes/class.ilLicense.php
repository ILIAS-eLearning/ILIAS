<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilLicense
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id: class.ilLicense.php $
* 
* @package ilias-license
*/

class ilLicense
{
	/**
	* Constructor
	* @access public
	*/
	function ilLicense($a_obj_id)
	{
		$this->obj_id = (int) $a_obj_id;
		$this->read();
	}

	//////////
	// SET GET
	
	function setLicenses($a_licenses = 0)
	{
		$this->licenses = (int) $a_licenses;
	}
	function getLicenses()
	{
		return $this->licenses;
	}
	function setRemarks($a_remarks = '')
	{
		$this->remarks = $a_remarks;
	}
	function getRemarks()
	{
		return $this->remarks;
	}
	function getAccesses()
	{
		return $this->accesses;
	}
	function getRemainingLicenses()
	{
		return max(0, $this->licenses - $this->accesses);
	}
	
	/**
	* Get the number of users who may access the object but don't have yet a license
	*
	* @access   public
	* @return   int     number of potential accesses
	*/
	function getPotentialAccesses()
	{
		global $ilDB;
		
		// get the operation id
		$ops_ids = ilRbacReview::_getOperationIdsByName(array('read'));

		$query = 'SELECT COUNT(DISTINCT(ua.usr_id)) accesses '
		       . 'FROM rbac_ua ua '
			   . 'INNER JOIN rbac_pa pa ON pa.rol_id = ua.rol_id '
			   . 'INNER JOIN object_reference ob ON ob.ref_id = pa.ref_id '
			   . 'LEFT JOIN read_event re ON re.usr_id = ua.usr_id '
			   . 'WHERE '.$ilDB->like('pa.ops_id', 'text', '%%i:'.$ops_ids[0].';%%').' ' 
			   . 'AND ob.obj_id = %s '
			   . 'AND (re.usr_id IS NULL OR re.obj_id <> %s)';
		$result = $ilDB->queryF($query,
			array('integer', 'integer'),
			array($this->obj_id, $this->obj_id));

		$row = $ilDB->fetchObject($result);
		return $row->accesses;
	}
	

	///////////////////
	// Data maintenance
	
	/**
	* Read the license data from the database
	*
	* @access   public
	*/
	function read()
	{
		global $ilDB;
		
		$query = 'SELECT * FROM license_data WHERE obj_id = %s';
		$result = $ilDB->queryF($query, array('integer'), array($this->obj_id));
		
		if ($row = $ilDB->fetchObject($result))
		{
			$this->licenses = $row->licenses;
			$this->accesses = $row->used;
			$this->remarks = $row->remarks;
		}
		else
		{
			$this->licenses = 0;
			$this->accesses = 0;
			$this->remarks = '';
		}
	}

	/**
	* Update the license data in the database
	*
	* @access   public
	*/
	function update()
	{
		global $ilDB;
		
		$query = 'SELECT * FROM license_data WHERE obj_id = %s';
		$result = $ilDB->queryF($query, array('integer'), array($this->obj_id));

		if ($row = $ilDB->fetchObject($result))
		{
			$ilDB->update('license_data',
				array(					
					'licenses'	=> array('integer', $this->licenses),
					'used'		=> array('integer', $this->accesses),
					'remarks'	=> array('clob', $this->remarks)
				),
				array(
					'obj_id' 	=> array('integer', $this->obj_id),
				)
			);
		}
		else
		{
			$ilDB->insert('license_data', array(
				'obj_id' 	=> array('integer', $this->obj_id),
				'licenses'	=> array('integer', $this->licenses),
				'used'		=> array('integer', $this->accesses),
				'remarks'	=> array('clob', $this->remarks)
			));
		}
	}

	/**
	* Delete all data of the license
	*
	* @access   public
	*/
	function delete()
	{
		global $ilDB;
		
		$query = 'DELETE FROM license_data WHERE obj_id = %s';
		$ilDB->manipulateF($query, array('integer'), array($this->obj_id));
	}
	
	
	////////////
	// Tracking

	/**
	* Check, if a user can access an object by license
	*
	* The user can access, if he/she already accessed the object
	* or if a license is available for the object.
	* This check is called from the ilAccessHandler class.
	*
	* @access   static
	* @param    int     	user id
	* @param    int     	object id (not reference)
	* @return   boolean     access is possible (true/false)
	*/
	function _checkAccess($a_usr_id, $a_obj_id)
	{
		global $ilDB, $ilUser;
		
		// check if user has already accessed
		$query = 'SELECT read_count FROM read_event '
				.'WHERE usr_id = %s AND obj_id = %s';
			
		$result = $ilDB->queryF($query,
						array('integer','integer'),
						array($a_usr_id, $a_obj_id));
						
		if ($row = $ilDB->fetchObject($result))
		{
			return true;
		}

		// otherwise check the number remaining licenses
		$license =& new ilLicense($a_obj_id);
		return ($license->getLicenses() == 0
				or $license->getRemainingLicenses() > 0);
	}
	
	/**
	* Note the access of the current usr to an object
	*
	* This function has to be called if an object is accessed for viewing.
	*
	* @access   static
	* @param    int     	object id (not reference)
	*/
	function _noteAccess($a_obj_id)
	{
		global $ilDB, $ilUser, $ilSetting;
		
		// don't note the access if licensing is globally disabled
		$lic_set = new ilSetting("license");
		if (!$lic_set->get("license_counter"))
		{
   			return;
		}
		
		// check if user has already accessed
		$query = 'SELECT read_count FROM read_event '
				.'WHERE usr_id = %s AND obj_id = %s';
		$result = $ilDB->queryF($query,
						array('integer','integer'),
						array($ilUser->getId(), $a_obj_id));

		if ($row = $ilDB->fetchObject($result))
		{
			// already accessed -> nothing to do
			return;
		}
		else
		{
			// note access
			require_once('Services/Tracking/classes/class.ilChangeEvent.php');
			ilChangeEvent::_recordReadEvent($a_obj_id, $ilUser->getId());

			if (self::_isLicensed($a_obj_id))
			{
				// increase used licenses
				$query = "UPDATE license_data SET used = used + 1 "
				        ."WHERE obj_id = %s";
				$ilDB->manipulateF($query, array('integer'), array($a_obj_id));
			}
		}
	}


	//////////////////
	// Static Queries

	/**
	* Get a list of all objects with activated licensing
	*
	* @access   static
	* @return   array     	array of object data arrays (obj_id, type, title, description)
	*/
	function _getLicensedObjects()
	{
		global $ilDB;
		$objects = array();
		
		$query = 'SELECT od.obj_id, od.type, od.title, od.description, re.ref_id '
		       . 'FROM license_data ld '
			   . 'INNER JOIN object_data od ON od.obj_id = ld.obj_id '
			   . 'INNER JOIN object_reference re ON re.obj_id = od.obj_id '
			   . 'WHERE ld.licenses > 0 '
			   . 'ORDER BY od.title, od.obj_id';

		$result = $ilDB->query($query);
		$obj_id = 0;
		while ($row = $ilDB->fetchAssoc($result))
		{
			if ($row['obj_id'] != $obj_id)
			{
				$objects[] = $row;
				$obj_id = $row['obj_id'];
			}
		}
		return $objects;
	}

	/**
	* Get a list of all sub objects with activated licensing
	*
	* @access   static
	* @param    int         ref_id of the repository node to check
	* @return   array     	array of object data arrays (obj_id, title, desc)
	*/
	function _getLicensedChildObjects($a_ref_id)
	{
		global $ilDB, $tree;
		$objects = array();

		$childs = $tree->getChilds($a_ref_id, 'title');
		foreach ($childs as $data)
		{
			if (in_array($data['type'], array('sahs','htlm'))
			and self::_isLicensed($data['obj_id']))
			{
				$objects[] = $data;
			}
		}
		return $objects;
	}

	/**
	* Check if an object has licensing activated
	*
	* @access   static
	* @param    int         object id
	* @return   boolean   	object has licensing (true/false)
	*/
	function _isLicensed($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT licenses FROM license_data ".
				 "WHERE obj_id = %s ".
				 "AND licenses > 0";
		$result = $ilDB->queryF($query, array('integer'), array($a_obj_id));
		if ($row = $ilDB->fetchObject($result))
		{
			return true;
		}
	}
}
		
?>