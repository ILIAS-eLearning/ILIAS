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
* class ilRbacReview
*  Contains Review functions of core Rbac.
*  This class offers the possibility to view the contents of the user <-> role (UR) relation and
*  the permission <-> role (PR) relation.
*  For example, from the UA relation the administrator should have the facility to view all user assigned to a given role.
*  
* 
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
* 
* @version $Id$
* 
* @ingroup ServicesAccessControl
*/
class ilRbacReview
{
	protected $assigned_roles = array();
	var $log = null;

	/**
	* Constructor
	* @access	public
	*/
	function ilRbacReview()
	{
		global $ilDB,$ilErr,$ilias,$ilLog;

		$this->log =& $ilLog;

		// set db & error handler
		(isset($ilDB)) ? $this->ilDB =& $ilDB : $this->ilDB =& $ilias->db;
		
		if (!isset($ilErr))
		{
			$ilErr = new ilErrorHandling();
			$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));
		}
		else
		{
			$this->ilErr =& $ilErr;
		}
	}

	/**
	* Finds all role ids that match the specified user friendly role mailbox address list.
	*
	* The role mailbox name address list is an e-mail address list according to IETF RFC 822:
	*
	* address list  = role mailbox, {"," role mailbox } ;
	* role mailbox  = "#", local part, ["@" domain] ;
	*
	* Examples: The following role mailbox names are all resolved to the role il_crs_member_123:
	*
	*    #Course.A
	*    #member@Course.A
	*    #il_crs_member_123@Course.A
	*    #il_crs_member_123
	*    #il_crs_member_123@ilias
	*
	* Examples: The following role mailbox names are all resolved to the role il_crs_member_345:
	*
	*    #member@[English Course]
	*    #il_crs_member_345@[English Course]
	*    #il_crs_member_345
	*    #il_crs_member_345@ilias
	*
	* If only the local part is specified, or if domain is equal to "ilias", ILIAS compares
	* the title of role objects with local part. Only roles that are not in a trash folder
	* are considered for the comparison.
	*
	* If a domain is specified, and if the domain is not equal to "ilias", ILIAS compares
	* the title of objects with the domain. Only objects that are not in a trash folder are
	* considered for the comparison. Then ILIAS searches for local roles which contain
	* the local part in their title. This allows for abbreviated role names, e.g. instead of
	* having to specify #il_grp_member_345@MyGroup, it is sufficient to specify #member@MyGroup.
	*
	* The address list may contain addresses thate are not role mailboxes. These addresses
	* are ignored.
	*
	* If a role mailbox address is ambiguous, this function returns the ID's of all role
	* objects that are possible recipients for the role mailbox address. 
	*
	* If Pear Mail is not installed, then the mailbox address 
	*
	*
	* @access	public
	* @param	string	IETF RFX 822 address list containing role mailboxes.
	* @return	int[] Array with role ids that were found
	*/
	function searchRolesByMailboxAddressList($a_address_list)
	{
		$role_ids = array();
		
		include_once "Services/Mail/classes/class.ilMail.php";
		if (ilMail::_usePearMail())
		{
			require_once 'Mail/RFC822.php';
			$parser = &new Mail_RFC822();
			$parsedList = $parser->parseAddressList($a_address_list, "ilias", false, true);
			//echo '<br>ilRBACReview '.var_export($parsedList,false);
			foreach ($parsedList as $address)
			{
				$local_part = $address->mailbox;
				if (strpos($local_part,'#') !== 0) 
				{
					// A local-part which doesn't start with a '#' doesn't denote a role.
					// Therefore we can skip it.
					continue;
				}

				$local_part = substr($local_part, 1);

				if (substr($local_part,0,8) == 'il_role_')
				{
					$role_id = substr($local_part,8);
					$q = "SELECT t.tree ".
						"FROM rbac_fa AS fa ".
						"JOIN tree AS t ON t.child=fa.parent ".
						"WHERE fa.rol_id=".$this->ilDB->quote($role_id)." ".
						"AND fa.assign='y' ".
						"AND t.tree=1";
					$r = $this->ilDB->query($q);
					if ($r->numRows() > 0)
					{
						$role_ids[] = $role_id;
					}
					continue;
				}


				$domain = $address->host;
				if (strpos($domain,'[') == 0 && strrpos($domain,']'))
				{
					$domain = substr($domain,1,strlen($domain) - 2);
				}
				if (strlen($local_part) == 0)
				{
					$local_part = $domain;
					$address->host = 'ilias';
					$domain = 'ilias';
				}

				if (strtolower($address->host) == 'ilias')
				{
					// Search for roles = local-part in the whole repository
					$q = "SELECT dat.obj_id ".
						"FROM object_data AS dat ".
						"JOIN rbac_fa AS fa ON fa.rol_id = dat.obj_id ".
						"JOIN tree AS t ON t.child = fa.parent ".
						"WHERE dat.title =".$this->ilDB->quote($local_part)." ".
						"AND dat.type = 'role' ".
						"AND fa.assign = 'y' ".
						"AND t.tree = 1";
				}
				else
				{
					// Search for roles like local-part in objects = host
					$q = "SELECT rdat.obj_id ".
						"FROM object_data AS odat ".
						"JOIN object_reference AS oref ON oref.obj_id = odat.obj_id ".
						"JOIN tree AS otree ON otree.child = oref.ref_id ".
						"JOIN tree AS rtree ON rtree.parent = otree.child ".
						"JOIN rbac_fa AS rfa ON rfa.parent = rtree.child ".
						"JOIN object_data AS rdat ON rdat.obj_id = rfa.rol_id ".
						"WHERE odat.title = ".$this->ilDB->quote($domain)." ".
						"AND otree.tree = 1 AND rtree.tree = 1 ".
						"AND rfa.assign = 'y' ".
						"AND rdat.title LIKE ".
							$this->ilDB->quote('%'.preg_replace('/([_%])/','\\\\$1',$local_part).'%');
				}
				$r = $this->ilDB->query($q);

				$count = 0;
				while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$role_ids[] = $row->obj_id;
					$count++;
				}

				// Nothing found?
				// In this case, we search for roles = host.
				if ($count == 0 && strtolower($address->host) == 'ilias')
				{
					$q = "SELECT dat.obj_id ".
						"FROM object_data AS dat ".
						"JOIN object_reference AS ref ON ref.obj_id = dat.obj_id ".
						"JOIN tree AS t ON t.child = ref.ref_id ".
						"WHERE dat.title = ".$this->ilDB->quote($domain)." ".
						"AND dat.type = 'role' ".
						"AND t.tree = 1 ";
					$r = $this->ilDB->query($q);

					while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
					{
						$role_ids[] = $row->obj_id;
					}
				}
				//echo '<br>ids='.var_export($role_ids,true);
			}
		} 
		else 
		{
			// the following code is executed, when Pear Mail is
			// not installed

			$titles = explode(',', $a_address_list);
			
			$titleList = '';
			foreach ($titles as $title)
			{
				if (strlen($inList) > 0)
				{
					$titleList .= ',';
				}
				$title = trim($title);
				if (strpos($title,'#') == 0) 
				{
					$titleList .= $this->ilDB->quote(substr($title, 1));
				}
			}	
			if (strlen($titleList) > 0)
			{
				$q = "SELECT obj_id ".
					"FROM object_data ".
					"WHERE title IN (".$titleList.") ".
					"AND type='role'";
				$r = $this->ilDB->query($q);
				while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$role_ids[] = $row->obj_id;
				}
			}
		}

		return $role_ids;
	}
	
	/**
	 * Returns the mailbox address of a role.
	 *
	 * Example 1: Mailbox address for an ILIAS reserved role name
     * ----------------------------------------------------------
     * The il_crs_member_345 role of the course object "English Course 1" is 
	 * returned as one of the following mailbox addresses:
	 *
	 * a)   Course Member <#member@[English Course 1]>
	 * b)   Course Member <#il_crs_member_345@[English Course 1]>
	 * c)   Course Member <#il_crs_member_345>
	 *
	 * Address a) is returned, if the title of the object is unique, and
 	 * if there is only one local role with the substring "member" defined for
	 * the object.
     *
	 * Address b) is returned, if the title of the object is unique, but 
     * there is more than one local role with the substring "member" in its title.
     *
     * Address c) is returned, if the title of the course object is not unique.
     *
     *
	 * Example 2: Mailbox address for a manually defined role name
     * -----------------------------------------------------------
     * The "Admin" role of the category object "Courses" is 
	 * returned as one of the following mailbox addresses:
	 *
	 * a)   Course Administrator <#Admin@Courses>
	 * b)   Course Administrator <#Admin>
     * c)   Course Adminstrator <#il_role_34211>
	 *
	 * Address a) is returned, if the title of the object is unique, and
 	 * if there is only one local role with the substring "Admin" defined for
	 * the course object.
     *
	 * Address b) is returned, if the title of the object is not unique, but 
     * the role title is unique.
     *
     * Address c) is returned, if neither the role title nor the title of the
     * course object is unique. 
     *
     *
	 * Example 3: Mailbox address for a manually defined role title that can
     *            contains special characters in the local-part of a 
     *            mailbox address
     * --------------------------------------------------------------------
     * The "Author Courses" role of the category object "Courses" is 
	 * returned as one of the following mailbox addresses:
	 *
	 * a)   "#Author Courses"@Courses
     * b)   Author Courses <#il_role_34234>
	 *
	 * Address a) is returned, if the title of the role is unique.
     *
     * Address b) is returned, if neither the role title nor the title of the
     * course object is unique, or if the role title contains a quote or a
	 * backslash.
     *
	 *
	 * @param int a role id
	 * @param boolean is_localize whether mailbox addresses should be localized
	 * @return	String mailbox address or null, if role does not exist.
	 */
	function getRoleMailboxAddress($a_role_id, $is_localize = true)
	{
		global $log, $lng;

		include_once "Services/Mail/classes/class.ilMail.php";
		if (ilMail::_usePearMail())
		{
			// Retrieve the role title and the object title.
			$q = "SELECT rdat.title AS role_title,odat.title AS object_title, ".
					" oref.ref_id AS object_ref ".
				"FROM object_data AS rdat ".
				"JOIN rbac_fa AS fa ON fa.rol_id = rdat.obj_id ".
				"JOIN tree AS rtree ON rtree.child = fa.parent ".
				"JOIN object_reference AS oref ON oref.ref_id = rtree.parent ".
				"JOIN object_data AS odat ON odat.obj_id = oref.obj_id ".
				"WHERE rdat.obj_id = ".$this->ilDB->quote($a_role_id)." ".
				"AND fa.assign = 'y' ";
			$r = $this->ilDB->query($q);
			if (! ($row = $r->fetchRow(DB_FETCHMODE_OBJECT)))
			{
				//$log->write('class.ilRbacReview->getMailboxAddress('.$a_role_id.'): error role does not exist');
				return null; // role does not exist
			}
			$object_title = $row->object_title;
			$object_ref = $row->object_ref;
			$role_title = $row->role_title;


			// In a perfect world, we could use the object_title in the 
			// domain part of the mailbox address, and the role title
			// with prefix '#' in the local part of the mailbox address.
			$domain = $object_title;
			$local_part = $role_title;


			// Determine if the object title is unique
			$q = "SELECT COUNT(DISTINCT dat.obj_id) AS count ".
				"FROM object_data AS dat ".
				"JOIN object_reference AS ref ON ref.obj_id = dat.obj_id ".
				"JOIN tree ON tree.child = ref.ref_id ".
				"WHERE title = ".$this->ilDB->quote($object_title)." ".
				"AND tree.tree = 1";
			$r = $this->ilDB->query($q);
			$row = $r->fetchRow(DB_FETCHMODE_OBJECT);

			// If the object title is not unique, we get rid of the domain.
			if ($row->count > 1)
			{
				$domain = null;
			}

			// If the domain contains illegal characters, we get rid of it.
			if (domain != null && preg_match('/[\[\]\\]|[\x00-\x1f]/',$domain))
			{
				$domain = null;
			}

			// If the domain contains special characters, we put square
			//   brackets around it.
			if ($domain != null && 
					(preg_match('/[()<>@,;:\\".\[\]]/',$domain) || 
					preg_match('/[^\x21-\x8f]/',$domain))
					)
			{
				$domain = '['.$domain.']';
			}

			// If the role title is one of the ILIAS reserved role titles,
			//     we can use a shorthand version of it for the local part
			//     of the mailbox address.
			if (strpos($role_title, 'il_') === 0 && $domain != null)
			{
				$unambiguous_role_title = $role_title;

				$pos = strpos($role_title, '_', 3) + 1;
				$local_part = substr(
					$role_title, 
					$pos,  
					strrpos($role_title, '_') - $pos
				);
			}
			else
			{
				$unambiguous_role_title = 'il_role_'.$a_role_id;
			}

			// Determine if the local part is unique. If we don't have a
			// domain, the local part must be unique within the whole repositry.
			// If we do have a domain, the local part must be unique for that
			// domain.
			if ($domain == null)
			{
				$q = "SELECT COUNT(DISTINCT dat.obj_id) AS count ".
					"FROM object_data AS dat ".
					"JOIN object_reference AS ref ON ref.obj_id = dat.obj_id ".
					"JOIN tree ON tree.child = ref.ref_id ".
					"WHERE title = ".$this->ilDB->quote($local_part)." ".
					"AND tree.tree = 1";
			}
			else
			{
				$q = "SELECT COUNT(rd.obj_id) AS count ".
					 "FROM object_data AS rd ".
					 "JOIN rbac_fa AS fa ON rd.obj_id = fa.rol_id ".
					 "JOIN tree AS t ON t.child = fa.parent ". 
					 "WHERE fa.assign = 'y' ".
					 "AND t.parent = ".$this->ilDB->quote($object_ref)." ".
					 "AND rd.title LIKE ".$this->ilDB->quote(
						'%'.preg_replace('/([_%])/','\\\\$1', $local_part).'%')
					;
			}

			$r = $this->ilDB->query($q);
			$row = $r->fetchRow(DB_FETCHMODE_OBJECT);

			// if the local_part is not unique, we use the unambiguous role title 
			//   instead for the local part of the mailbox address
			if ($row->count > 1)
			{
				$local_part = $unambiguous_role_title;
			}


			// If the local part contains illegal characters, we use
			//     the unambiguous role title instead.
			if (preg_match('/[\\"\x00-\x1f]/',$local_part)) 
			{
				$local_part = $unambiguous_role_title;
			}


			// Add a "#" prefix to the local part
			$local_part = '#'.$local_part;

			// Put quotes around the role title, if needed
			if (preg_match('/[()<>@,;:.\[\]\x20]/',$local_part))
			{
				$local_part = '"'.$local_part.'"';
			}

			$mailbox = ($domain == null) ?
					$local_part :
					$local_part.'@'.$domain;

			if ($is_localize)
			{
				if (substr($role_title,0,3) == 'il_')
				{
					$phrase = $lng->txt(substr($role_title, 0, strrpos($role_title,'_')));
				}
				else
				{
					$phrase = $role_title;
				}

				// make phrase RFC 822 conformant:
				// - strip excessive whitespace 
				// - strip special characters
				$phrase = preg_replace('/\s\s+/', ' ', $phrase);
				$phrase = preg_replace('/[()<>@,;:\\".\[\]]/', '', $phrase);

				$mailbox = $phrase.' <'.$mailbox.'>';
			}

			return $mailbox;
		}
		else 
		{
			$q = "SELECT title ".
				"FROM object_data ".
				"WHERE obj_id = ".$this->ilDB->quote($a_role_id);
			$r = $this->ilDB->query($q);

			if ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
			{
				return '#'.$row->title;
			}
			else
			{
				return null;
			}
		}
	}

	
	/**
	* Checks if a role already exists. Role title should be unique
	* @access	public
	* @param	string	role title
	* @param	integer	obj_id of role to exclude in the check. Commonly this is the current role you want to edit
	* @return	boolean	true if exists
	*/
	function roleExists($a_title,$a_id = 0)
	{
		global $ilDB;
		
		if (empty($a_title))
		{
			$message = get_class($this)."::roleExists(): No title given!";
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}
		
		$clause = ($a_id) ? " AND obj_id != ".$ilDB->quote($a_id)." " : "";
		
		$q = "SELECT DISTINCT(obj_id) as obj_id FROM object_data ".
			 "WHERE title =".$ilDB->quote($a_title)." ".
			 "AND type IN('role','rolt')".
			 $clause;
		$r = $this->ilDB->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->obj_id;
		}
		return false;
	}
	
	/**
	 * get parent roles (NEW implementation)
	 *
	 * @access protected
	 * @param 
	 * @return
	 */
	protected function getParentRoles($a_path,$a_templates,$a_keep_protected)
	{
		global $log,$ilDB,$tree;
		
		$parent_roles = array();
		$role_hierarchy = array();

		$node = $tree->getNodeData($a_path);
		$lft = $node['lft'];
		$rgt = $node['rgt'];


		// Role folder id		
		$relevant_rolfs[] = ROLE_FOLDER_ID;
		
		// Role folder of current object
		if($rolf = $this->getRoleFolderIdOfObject($a_path))
		{
			$relevant_rolfs[] = $rolf;
		}
		
		// role folder of objects in path
		$query = "SELECT * FROM tree ".
			"JOIN object_reference as obr ON child = ref_id ".
			"JOIN object_data as obd ON obr.obj_id = obd.obj_id ".
			"WHERE type = 'rolf' ".
			"AND lft < ".$lft." ".
			"AND rgt > ".$rgt;
	
	
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$relevant_rolfs[] = $row->child;
		}
		foreach($relevant_rolfs as $rolf)
		{
			$roles = $this->getRoleListByObject($rolf,$a_templates);
			
			foreach ($roles as $role)
			{
				$id = $role["obj_id"];
				$role["parent"] = $rolf;
				$parent_roles[$id] = $role;
				
				if (!array_key_exists($role['obj_id'],$role_hierarchy))
				{
					$role_hierarchy[$id] = $rolf;
				}
			}
		}
		
		if (!$a_keep_protected)
		{
			return $this->__setProtectedStatus($parent_roles,$role_hierarchy,$a_path);
		}
		return $parent_roles;
	}
	

	/**
	* DEPRECTED use getParentRoles instead.
	* This version is much to slow on big installations
	* 
	* Get parent roles in a path. If last parameter is set 'true'
	* it delivers also all templates in the path
	* @access	private
	* @param	array	array with path_ids
	* @param	boolean	true for role templates (default: false)
	* @return	array	array with all parent roles (obj_ids)
	*/
	function __getParentRoles($a_path,$a_templates,$a_keep_protected)
	{
		global $log,$ilDB;
		
		if (!isset($a_path) or !is_array($a_path))
		{
			$message = get_class($this)."::getParentRoles(): No path given or wrong datatype!";
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}

		$parent_roles = array();
		$role_hierarchy = array();
		
		$child = $this->__getAllRoleFolderIds();
		
		// CREATE IN() STATEMENT
		$in = " IN(";
		$in .= implode(",",ilUtil::quoteArray($child));
		$in .= ") ";
		
		foreach ($a_path as $path)
		{
			// Note the use of the HAVING clause: For large trees with many
			// local roles, this query performs much faster when the IN
            // condition is inside of the HAVING clause.
			$q = "SELECT * FROM tree ".
				 "WHERE parent = ".$ilDB->quote($path)." ".
				 "HAVING child ".$in;
			$r = $this->ilDB->query($q);

			while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$roles = $this->getRoleListByObject($row->child,$a_templates);

				foreach ($roles as $role)
				{
					$id = $role["obj_id"];
					$role["parent"] = $row->child;
					$parent_roles[$id] = $role;
					
					if (!array_key_exists($role['obj_id'],$role_hierarchy))
					{
						$role_hierarchy[$id] = $row->child;
					}
				}
			}
		}
		if (!$a_keep_protected)
		{
			return $this->__setProtectedStatus($parent_roles,$role_hierarchy,$path);
		}
		return $parent_roles;
	}

	/**
	* get an array of parent role ids of all parent roles, if last parameter is set true
	* you get also all parent templates
	* @access	public
	* @param	integer		ref_id of an object which is end node
	* @param	boolean		true for role templates (default: false)
	* @return	array       array(role_ids => role_data)
	*/
	function getParentRoleIds($a_endnode_id,$a_templates = false,$a_keep_protected = false)
	{
		global $tree,$log,$ilDB;

		if (!isset($a_endnode_id))
		{
			$message = get_class($this)."::getParentRoleIds(): No node_id (ref_id) given!";
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}
		
		//var_dump($a_endnode_id);exit;
		//$log->write("ilRBACreview::getParentRoleIds(), 0");	
		$pathIds  = $tree->getPathId($a_endnode_id);

		// add system folder since it may not in the path
		$pathIds[0] = SYSTEM_FOLDER_ID;
		//$log->write("ilRBACreview::getParentRoleIds(), 1");
		#return $this->getParentRoles($a_endnode_id,$a_templates,$a_keep_protected);
		return $this->__getParentRoles($pathIds,$a_templates,$a_keep_protected);
	}

	/**
	* Returns a list of roles in an container
	* @access	public
	* @param	integer	ref_id
	* @param	boolean	if true fetch template roles too
	* @return	array	set ids
	*/
	function getRoleListByObject($a_ref_id,$a_templates = false)
	{
		global $ilDB;
		
		if (!isset($a_ref_id) or !isset($a_templates))
		{
			$message = get_class($this)."::getRoleListByObject(): Missing parameter!".
					   "ref_id: ".$a_ref_id.
					   "tpl_flag: ".$a_templates;
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}

		$role_list = array();

		$where = $this->__setTemplateFilter($a_templates);
	
		$q = "SELECT * FROM object_data ".
			 "JOIN rbac_fa ".$where.
			 "AND object_data.obj_id = rbac_fa.rol_id ".
			 "AND rbac_fa.parent = ".$ilDB->quote($a_ref_id)." ";
		$r = $this->ilDB->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$row["desc"] = $row["description"];
			$row["user_id"] = $row["owner"];
			$role_list[] = $row;
		}

		$role_list = $this->__setRoleType($role_list);
		
		return $role_list;
	}
	
	/**
	* Returns a list of all assignable roles
	* @access	public
	* @param	boolean	if true fetch template roles too
	* @return	array	set ids
	*/
	function getAssignableRoles($a_templates = false,$a_internal_roles = false)
	{
		global $ilDB;
		
		$role_list = array();

		$where = $this->__setTemplateFilter($a_templates);

		$q = "SELECT DISTINCT * FROM object_data ".
			 "JOIN rbac_fa ".$where.
			 "AND object_data.obj_id = rbac_fa.rol_id ".
			 "AND rbac_fa.assign = 'y'";
		$r = $this->ilDB->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$row["desc"] = $row["description"];
			$row["user_id"] = $row["owner"];
			$role_list[] = $row;
		}
		
		$role_list = $this->__setRoleType($role_list);

		return $role_list;
	}

	/**
	* Returns a list of assignable roles in a subtree of the repository
	* @access	public
	* @param	ref_id Rfoot node of subtree
	* @return	array	set ids
	*/
	function getAssignableRolesInSubtree($ref_id)
	{
		$role_list = array();

		$where = $this->__setTemplateFilter($a_templates);

		$q = "SELECT fa.*, dat.* ".
			"FROM tree AS root ".
			"JOIN tree AS node ON node.tree = root.tree AND node.lft > root.lft AND node.rgt < root.rgt ".
			"JOIN object_reference AS ref ON ref.ref_id = node.child ".
			"JOIN rbac_fa AS fa ON fa.parent = ref.ref_id ".
			"JOIN object_data AS dat ON dat.obj_id = fa.rol_id ".
			"WHERE root.child = ".$this->ilDB->quote($ref_id)." AND root.tree = 1 ".
			"AND fa.assign = 'y' ".
			"ORDER BY dat.title";
		$r = $this->ilDB->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$role_list[] = $row;
		}
		
		$role_list = $this->__setRoleType($role_list);
		
		return $role_list;
	}

	/**
	* Get all assignable roles under a specific node
	* @access	public
	* @param ref_id
	* @return	array	set ids
	*/
	function getAssignableChildRoles($a_ref_id)
	{
		global $tree;

		//$roles_data = $this->getAssignableRoles();
		$q = "SELECT fa.*, rd.* ".
			 "FROM object_data AS rd ".
			 "JOIN rbac_fa AS fa ON rd.obj_id = fa.rol_id ".
			 "JOIN tree AS t ON t.child = fa.parent ". 
			 "WHERE fa.assign = 'y' ".
			 "AND t.parent = ".$this->ilDB->quote($a_ref_id)." "
			;
		$r = $this->ilDB->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$roles_data[] = $row;
		}
		
		return $roles_data ? $roles_data : array();
	}
	
	/**
	* get roles and templates or only roles; returns string for where clause
	* @access	private
	* @param	boolean	true: with templates
	* @return	string	where clause
	*/
	function __setTemplateFilter($a_templates)
	{
		if ($a_templates === true)
		{
			 $where = "WHERE object_data.type IN ('role','rolt') ";		
		}
		else
		{
			$where = "WHERE object_data.type = 'role' ";
		}
		
		return $where;
	}

	/**
	* computes role type in role list array:
	* global: roles in ROLE_FOLDER_ID
	* local: assignable roles in other role folders
	* linked: roles with stoppped inheritance
	* template: role templates
	* 
	* @access	private
	* @param	array	role list
	* @return	array	role list with additional entry for role_type
	*/
	function __setRoleType($a_role_list)
	{
		foreach ($a_role_list as $key => $val)
		{
			// determine role type
			if ($val["type"] == "rolt")
			{
				$a_role_list[$key]["role_type"] = "template";
			}
			else
			{
				if ($val["assign"] == "y")
				{
					if ($val["parent"] == ROLE_FOLDER_ID)
					{
						$a_role_list[$key]["role_type"] = "global";
					}
					else
					{
						$a_role_list[$key]["role_type"] = "local";
					}
				}
				else
				{
					$a_role_list[$key]["role_type"] = "linked";
				}
			}
			
			if ($val["protected"] == "y")
			{
				$a_role_list[$key]["protected"] = true;
			}
			else
			{
				$a_role_list[$key]["protected"] = false;
			}
		}
		
		return $a_role_list;
	}
	
	/**
	* get all assigned users to a given role
	* @access	public
	* @param	integer	role_id
	* @param    array   columns to get form usr_data table (optional)
	* @return	array	all users (id) assigned to role OR arrays of user datas
	*/
	function assignedUsers($a_rol_id, $a_fields = NULL)
	{
		global $ilBench,$ilDB;
		
		$ilBench->start("RBAC", "review_assignedUsers");
		
		if (!isset($a_rol_id))
		{
			$message = get_class($this)."::assignedUsers(): No role_id given!";
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}
		
        $result_arr = array();

        if ($a_fields !== NULL and is_array($a_fields))
        {
            if (count($a_fields) == 0)
            {
                $select = "*";
            }
            else
            {
                if (($usr_id_field = array_search("usr_id",$a_fields)) !== false)
                    unset($a_fields[$usr_id_field]);

                $select = implode(",",$a_fields).",usr_data.usr_id";
                $select = addslashes($select);
            }

	        $q = "SELECT ".$select." FROM usr_data ".
                 "LEFT JOIN rbac_ua ON usr_data.usr_id=rbac_ua.usr_id ".
                 "WHERE rbac_ua.rol_id=".$ilDB->quote($a_rol_id)." ";
            $r = $this->ilDB->query($q);

            while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
            {
                $result_arr[] = $row;
            }
        }
        else
        {
		    $q = "SELECT usr_id FROM rbac_ua WHERE rol_id=".$ilDB->quote($a_rol_id)." ";
            $r = $this->ilDB->query($q);

            while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
            {
                array_push($result_arr,$row["usr_id"]);
            }
        }
		
		$ilBench->stop("RBAC", "review_assignedUsers");

		return $result_arr;
	}

	/**
	* check if a specific user is assigned to specific role
	* @access	public
	* @param	integer		usr_id
	* @param	integer		role_id
	* @return	boolean
	*/
	function isAssigned($a_usr_id,$a_role_id)
	{
		return in_array($a_usr_id,$this->assignedUsers($a_role_id));
	}
	
	/**
	* get all assigned roles to a given user
	* @access	public
	* @param	integer		usr_id
	* @return	array		all roles (id) the user have
	*/
	function assignedRoles($a_usr_id)
	{
		global $ilDB;
		
		$role_arr = array();
		
		$q = "SELECT rol_id FROM rbac_ua WHERE usr_id = ".$ilDB->quote($a_usr_id)." ";
		$r = $this->ilDB->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$role_arr[] = $row->rol_id;
		}

		if (!count($role_arr))
		{
			$message = get_class($this)."::assignedRoles(): No assigned roles found or user does not exist!";
		}
		return $role_arr ? $role_arr : array();
	}

	/**
	* Check if its possible to assign users
	* @access	public
	* @param	integer	object id of role
	* @param	integer	ref_id of object in question
	* @return	boolean 
	*/
	function isAssignable($a_rol_id, $a_ref_id)
	{
		global $ilBench,$ilDB;

		$ilBench->start("RBAC", "review_isAssignable");

		// exclude system role from rbac
		if ($a_rol_id == SYSTEM_ROLE_ID)
		{
			$ilBench->stop("RBAC", "review_isAssignable");

			return true;
		}

		if (!isset($a_rol_id) or !isset($a_ref_id))
		{
			$message = get_class($this)."::isAssignable(): Missing parameter!".
					   " role_id: ".$a_rol_id." ,ref_id: ".$a_ref_id;
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}
		
		$q = "SELECT * FROM rbac_fa ".
			 "WHERE rol_id = ".$ilDB->quote($a_rol_id)." ".
			 "AND parent = ".$ilDB->quote($a_ref_id)." ";
		$row = $this->ilDB->getRow($q);

		$ilBench->stop("RBAC", "review_isAssignable");

		return $row->assign == 'y' ? true : false;
	}

	/**
	* returns an array of role folder ids assigned to a role. A role with stopped inheritance
	* may be assigned to more than one rolefolder.
	* To get only the original location of a role, set the second parameter to true
	*
	* @access	public
	* @param	integer		role id
	* @param	boolean		get only rolefolders where role is assignable (true) 
	* @return	array		reference IDs of role folders
	*/
	function getFoldersAssignedToRole($a_rol_id, $a_assignable = false)
	{
		global $ilDB;
		
		if (!isset($a_rol_id))
		{
			$message = get_class($this)."::getFoldersAssignedToRole(): No role_id given!";
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}
		
		if ($a_assignable)
		{
			$where = " AND assign ='y'";
		}

		$q = "SELECT DISTINCT parent FROM rbac_fa ".
			 "WHERE rol_id = ".$ilDB->quote($a_rol_id)." ".$where;
		$r = $this->ilDB->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$folders[] = $row->parent;
		}

		return $folders ? $folders : array();
	}

	/**
	* get all roles of a role folder including linked local roles that are created due to stopped inheritance
	* returns an array with role ids
	* @access	public
	* @param	integer		ref_id of object
	* @param	boolean		if false only get true local roles
	* @return	array		Array with rol_ids
	*/
	function getRolesOfRoleFolder($a_ref_id,$a_nonassignable = true)
	{
		global $ilBench,$ilDB,$ilLog;
		
		$ilBench->start("RBAC", "review_getRolesOfRoleFolder");

		if (!isset($a_ref_id))
		{
			$message = get_class($this)."::getRolesOfRoleFolder(): No ref_id given!";
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
			
		}
		
		if ($a_nonassignable === false)
		{
			$and = " AND assign='y'";
		}

		$q = "SELECT rol_id FROM rbac_fa ".
			 "WHERE parent = ".$ilDB->quote($a_ref_id)." ".
			 $and;

		$r = $this->ilDB->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rol_id[] = $row->rol_id;
		}

		$ilBench->stop("RBAC", "review_getRolesOfRoleFolder");

		return $rol_id ? $rol_id : array();
	}
	
	/**
	* get only 'global' roles
	* @access	public
	* @return	array		Array with rol_ids
	*/
	function getGlobalRoles()
	{
		return $this->getRolesOfRoleFolder(ROLE_FOLDER_ID,false);
	}

	/**
	* get only 'global' roles
	* @access	public
	* @return	array		Array with rol_ids
	*/
	function getGlobalRolesArray()
	{
		foreach($this->getRolesOfRoleFolder(ROLE_FOLDER_ID,false) as $role_id)
		{
			$ga[] = array('obj_id'		=> $role_id,
						  'role_type'	=> 'global');
		}
		return $ga ? $ga : array();
	}

	/**
	* get only 'global' roles (with flag 'assign_users')
	* @access	public
	* @return	array		Array with rol_ids
	*/
	function getGlobalAssignableRoles()
	{
		include_once './Services/AccessControl/classes/class.ilObjRole.php';

		foreach($this->getGlobalRoles() as $role_id)
		{
			if(ilObjRole::_getAssignUsersStatus($role_id))
			{
				$ga[] = array('obj_id' => $role_id,
							  'role_type' => 'global');
			}
		}
		return $ga ? $ga : array();
	}

	/**
	* get all role folder ids
	* @access	private
	* @return	array
	*/
	function __getAllRoleFolderIds()
	{
		$parent = array();
		
		$q = "SELECT DISTINCT parent FROM rbac_fa";
		$r = $this->ilDB->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$parent[] = $row->parent;
		}

		return $parent;
	}

	/**
	* returns the data of a role folder assigned to an object
	* @access	public
	* @param	integer		ref_id of object with a rolefolder object under it
	* @return	array		empty array if rolefolder not found
	*/
	function getRoleFolderOfObject($a_ref_id)
	{
		global $tree,$ilBench;
		
		$ilBench->start("RBAC", "review_getRoleFolderOfObject");
		
		if (!isset($a_ref_id))
		{
			$message = get_class($this)."::getRoleFolderOfObject(): No ref_id given!";
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}

		$childs = $tree->getChildsByType($a_ref_id,"rolf");

		$ilBench->stop("RBAC", "review_getRoleFolderOfObject");

		return $childs[0] ? $childs[0] : array();
	}
	
	function getRoleFolderIdOfObject($a_ref_id)
	{
		$rolf = $this->getRoleFolderOfObject($a_ref_id);
		
		if (!$rolf)
		{
			return false;
		}
		
		return $rolf['ref_id'];
	}

	/**
	* get all possible operations 
	* @access	public
	* @return	array	array of operation_id
	*/
	function getOperations()
	{
		global $ilDB;

		$query = "SELECT * FROM rbac_operations ORDER BY ops_id ";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops[] = array('ops_id' => $row->ops_id,
						   'operation' => $row->operation,
						   'description' => $row->description);
		}

		return $ops ? $ops : array();
 	}

	/**
	* get one operation by operation id
	* @access	public
	* @return	array data of operation_id
	*/
	function getOperation($ops_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM rbac_operations WHERE ops_id = ".$ilDB->quote($ops_id)." ";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops = array('ops_id' => $row->ops_id,
						 'operation' => $row->operation,
						 'description' => $row->description);
		}

		return $ops ? $ops : array();
	}

	/**
	* get all possible operations of a specific role
	* The ref_id of the role folder (parent object) is necessary to distinguish local roles
	* @access	public
	* @param	integer	role_id
	* @param	string	object type
	* @param	integer	role folder id
	* @return	array	array of operation_id
	*/
	function getOperationsOfRole($a_rol_id,$a_type,$a_parent = 0)
	{
		global $ilDB,$ilLog;
		
		if (!isset($a_rol_id) or !isset($a_type))
		{
			$message = get_class($this)."::getOperationsOfRole(): Missing Parameter!".
					   "role_id: ".$a_rol_id.
					   "type: ".$a_type.
					   "parent_id: ".$a_parent;
			$ilLog->logStack("Missing parameter! ");
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}

		$ops_arr = array();

		// if no rolefolder id is given, assume global role folder as target
		if ($a_parent == 0)
		{
			$a_parent = ROLE_FOLDER_ID;
		}
		
		$q = "SELECT ops_id FROM rbac_templates ".
			 "WHERE type =".$ilDB->quote($a_type)." ".
			 "AND rol_id = ".$ilDB->quote($a_rol_id)." ".
			 "AND parent = ".$ilDB->quote($a_parent)."";
		$r  = $this->ilDB->query($q);


		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops_arr[] = $row->ops_id;
		}
		
		return $ops_arr;
	}
	
	function getRoleOperationsOnObject($a_role_id,$a_ref_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM rbac_pa ".
			"WHERE rol_id = ".$ilDB->quote($a_role_id)." ".
			"AND ref_id = ".$ilDB->quote($a_ref_id)." ";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops = unserialize(stripslashes($row->ops_id));
		}

		return $ops ? $ops : array();
	}

	/**
	* all possible operations of a type
	* @access	public
	* @param	integer		object_ID of type
	* @return	array		valid operation_IDs
	*/
	function getOperationsOnType($a_typ_id)
	{
		global $ilDB;
		
		if (!isset($a_typ_id))
		{
			$message = get_class($this)."::getOperationsOnType(): No type_id given!";
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}

		$q = "SELECT * FROM rbac_ta WHERE typ_id = ".$ilDB->quote($a_typ_id)." ";
		$r = $this->ilDB->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops_id[] = $row->ops_id;
		}

		return $ops_id ? $ops_id : array();
	}

	/**
	* all possible operations of a type
	* @access	public
	* @param	integer		object_ID of type
	* @return	array		valid operation_IDs
	*/
	function getOperationsOnTypeString($a_type)
	{
		global $ilDB;
		
		$query = "SELECT * FROM object_data WHERE type = 'typ' AND title = ".$ilDB->quote($a_type)." ";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $this->getOperationsOnType($row->obj_id);
		}
		return false;
	}
	/**
	* get all objects in which the inheritance of role with role_id was stopped
	* the function returns all reference ids of objects containing a role folder.
	* @access	public
	* @param	integer	role_id
	* @return	array	with ref_ids of objects
	*/
	function getObjectsWithStopedInheritance($a_rol_id)
	{
		$tree = new ilTree(ROOT_FOLDER_ID);

		if (!isset($a_rol_id))
		{
			$message = get_class($this)."::getObjectsWithStopedInheritance(): No role_id given!";
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}
			
		$all_rolf_ids = $this->getFoldersAssignedToRole($a_rol_id,false);

		foreach ($all_rolf_ids as $rolf_id)
		{
			$parent[] = $tree->getParentId($rolf_id);
		}

		return $parent ? $parent : array();
	}

	/**
	* checks if a rolefolder is set as deleted (negative tree_id)
	* @access	public
	* @param	integer	ref_id of rolefolder
	* @return	boolean	true if rolefolder is set as deleted
	*/
	function isDeleted($a_node_id)
	{
		global $ilDB;
		
		$q = "SELECT tree FROM tree WHERE child =".$ilDB->quote($a_node_id)." ";
		$r = $this->ilDB->query($q);
		
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
		
		if (!$row)
		{
			$message = sprintf('%s::isDeleted(): Role folder with ref_id %s not found!',
							   get_class($this),
							   $a_node_id);
			$this->log->write($message,$this->log->FATAL);

			return true;
		}

		// rolefolder is deleted
		if ($row->tree < 0)
		{
			return true;
		}
		
		return false;
	}

	function getRolesByFilter($a_filter = 0,$a_user_id = 0)
	{
		global $ilDB;
		
        $assign = "y";

		switch($a_filter)
		{
            // all (assignable) roles
            case 1:
				return $this->getAssignableRoles();
				break;

            // all (assignable) global roles
            case 2:
				$where = "WHERE rbac_fa.rol_id IN ";
				$where .= '(';
				$where .= implode(',',ilUtil::quoteArray($this->getGlobalRoles()));
				$where .= ')';
				break;

            // all (assignable) local roles
            case 3:
            case 4:
            case 5:
				$where = "WHERE rbac_fa.rol_id NOT IN ";
				$where .= '(';
				$where .= implode(',',ilUtil::quoteArray($this->getGlobalRoles()));
				$where .= ')';
				break;
				
            // all role templates
            case 6:
				$where = "WHERE object_data.type = 'rolt'";
				$assign = "n";
				break;

            // only assigned roles, handled by ilObjUserGUI::roleassignmentObject()
            case 0:
			default:
                if (!$a_user_id) return array();
                
				$where = "WHERE rbac_fa.rol_id IN ";
				$where .= '(';
				$where .= implode(',',ilUtil::quoteArray($this->assignedRoles($a_user_id)));
				$where .= ')';
                break;
		}
		
		$roles = array();

		$q = "SELECT DISTINCT * FROM object_data ".
			 "JOIN rbac_fa ".$where.
			 "AND object_data.obj_id = rbac_fa.rol_id ".
			 "AND rbac_fa.assign = ".$ilDB->quote($assign)." ";
		$r = $this->ilDB->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
            $prefix = (substr($row["title"],0,3) == "il_") ? true : false;

            // all (assignable) internal local roles only
            if ($a_filter == 4 and !$prefix)
			{
                continue;
            }

            // all (assignable) non internal local roles only
			if ($a_filter == 5 and $prefix)
			{
                continue;
            }
            
			$row["desc"] = $row["description"];
			$row["user_id"] = $row["owner"];
			$roles[] = $row;
		}

		$roles = $this->__setRoleType($roles);

		return $roles ? $roles : array();
	}
	
	// get id of a given object type (string)
	function getTypeId($a_type)
	{
		global $ilDB;

		$q = "SELECT obj_id FROM object_data ".
			 "WHERE title=".$ilDB->quote($a_type)." AND type='typ'";
		$r = $ilDB->query($q);
		
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
		return $row->obj_id;
	}

	/**
	* get ops_id's by name.
	*
	* Example usage: $rbacadmin->grantPermission($roles,ilRbacReview::_getOperationIdsByName(array('visible','read'),$ref_id));
	*
	* @access	public
	* @param	array	string name of operation. see rbac_operations
	* @return	array   integer ops_id's
	*/
	function _getOperationIdsByName($operations)
	{
		global $ilDB;

		if(!count($operations))
		{
			return array();
		}
		$where = "WHERE operation IN (";
		$where .= implode(",",ilUtil::quoteArray($operations));
		$where .= ")";

		$query = "SELECT ops_id FROM rbac_operations ".$where;
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops_ids[] = $row->ops_id;
		}
		return $ops_ids ? $ops_ids : array();
	}
	
	/**
	* get operation id by name of operation
	* @access	public
	* @access	static
	* @param	string	operation name
	* @return	integer	operation id
	*/
	public static function _getOperationIdByName($a_operation)
	{
		global $ilDB,$ilErr;
	
		if (!isset($a_operation))
		{
			$message = "perm::getOperationId(): No operation given!";
			$ilErr->raiseError($message,$ilErr->WARNING);	
		}
	
		$q = "SELECT DISTINCT ops_id FROM rbac_operations ".
			 "WHERE operation = ".$ilDB->quote($a_operation)." ";		    
		$row = $ilDB->getRow($q);
	
		return $row->ops_id;
	}


	/**
	* get all linked local roles of a role folder that are created due to stopped inheritance
	* returns an array with role ids
	* @access	public
	* @param	integer		ref_id of object
	* @param	boolean		if false only get true local roles
	* @return	array		Array with rol_ids
	*/
	function getLinkedRolesOfRoleFolder($a_ref_id)
	{
		global $ilDB;
		
		if (!isset($a_ref_id))
		{
			$message = get_class($this)."::getLinkedRolesOfRoleFolder(): No ref_id given!";
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}
		
		$and = " AND assign='n'";

		$q = "SELECT rol_id FROM rbac_fa ".
			 "WHERE parent = ".$ilDB->quote($a_ref_id)." ".
			 $and;
		$r = $this->ilDB->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rol_id[] = $row->rol_id;
		}

		return $rol_id ? $rol_id : array();
	}
	
	// checks if default permission settings of role under current parent (rolefolder) are protected from changes
	function isProtected($a_ref_id,$a_role_id)
	{
		global $ilDB;
		
		$q = "SELECT protected FROM rbac_fa ".
			 "WHERE rol_id= ".$ilDB->quote($a_role_id)." ".
			 "AND parent= ".$ilDB->quote($a_ref_id)." ";
		$r = $this->ilDB->query($q);
		$row = $r->fetchRow();
		
		return ilUtil::yn2tf($row[0]);
	}
	
	// this method alters the protected status of role regarding the current user's role assignment
	// and current postion in the hierarchy.
	function __setProtectedStatus($a_parent_roles,$a_role_hierarchy,$a_ref_id)
	{
		global $rbacsystem,$ilUser,$log;
		
		if (in_array(SYSTEM_ROLE_ID,$this->assignedRoles($ilUser->getId())))
		{
			$leveladmin = true;
		}
		else
		{
			$leveladmin = false;
		}
		
		//var_dump($a_role_hierarchy);
		
		foreach ($a_role_hierarchy as $role_id => $rolf_id)
		{
			//$log->write("ilRBACreview::__setProtectedStatus(), 0");	
			//echo "<br/>ROLF: ".$rolf_id." ROLE_ID: ".$role_id." (".$a_parent_roles[$role_id]['title'].") ";
			//var_dump($leveladmin,$a_parent_roles[$role_id]['protected']);

			if ($leveladmin == true)
			{
				$a_parent_roles[$role_id]['protected'] = false;
				continue;
			}
				
			if ($a_parent_roles[$role_id]['protected'] == true)
			{
				$arr_lvl_roles_user = array_intersect($this->assignedRoles($ilUser->getId()),array_keys($a_role_hierarchy,$rolf_id));
				
				foreach ($arr_lvl_roles_user as $lvl_role_id)
				{
					//echo "<br/>level_role: ".$lvl_role_id;
					//echo "<br/>a_ref_id: ".$a_ref_id;
					
					//$log->write("ilRBACreview::__setProtectedStatus(), 1");
					// check if role grants 'edit_permission' to parent
					if ($rbacsystem->checkPermission($a_ref_id,$lvl_role_id,'edit_permission'))
					{
						//$log->write("ilRBACreview::__setProtectedStatus(), 2");
						// user may change permissions of that higher-ranked role
						$a_parent_roles[$role_id]['protected'] = false;
						
						// remember successful check
						$leveladmin = true;
					}
				}
			}
		}
		
		return $a_parent_roles;
	}
	
	/**
	* get operation list by object type
	* TODO: rename function to: getOperationByType
	* @access	public
	* @access 	static
	* @param	string	object type you want to have the operation list
	* @param	string	order column
	* @param	string	order direction (possible values: ASC or DESC)
	* @return	array	returns array of operations
	*/
	public static function _getOperationList($a_type = null)
	 {
		global $ilDB;
	
		$arr = array();
	
		if ($a_type)
		{
			$q = "SELECT * FROM rbac_operations ".
				 "LEFT JOIN rbac_ta ON rbac_operations.ops_id = rbac_ta.ops_id ".
				 "LEFT JOIN object_data ON rbac_ta.typ_id = object_data.obj_id ".
				 "WHERE object_data.title= ".$ilDB->quote($a_type)." AND object_data.type='typ' ".
				 "ORDER BY 'op_order' ASC"; 
		}
		else
		{
			$q = "SELECT * FROM rbac_operations ".
				 "ORDER BY 'op_order' ASC";
		}
		
		$r = $ilDB->query($q);
	
		while ($row = $r->fetchRow())
		{
			$arr[] = array(
						"ops_id"	=> $row[0],
						"operation"	=> $row[1],
						"desc"		=> $row[2],
						"class"		=> $row[3],
						"order"		=> $row[4]
						);
		}
	
		return $arr;
	}
	
	public static function _groupOperationsByClass($a_ops_arr)
	{
		$arr = array();
	
		foreach ($a_ops_arr as $ops)
		{
			$arr[$ops['class']][] = array ('ops_id'	=> $ops['ops_id'],
										   'name'	=> $ops['operation']
										 );
		}
		return $arr; 
	}

	/**
	 * Get object id of objects a role is assigned to
	 *
	 * @access public
	 * @param int role id
	 * 
	 */
	public function getObjectOfRole($a_role_id)
	{
		global $ilDB;
		
		$query = "SELECT obr.obj_id FROM rbac_fa as rfa ".
			"JOIN tree ON rfa.parent = tree.child ".
			"JOIN object_reference AS obr ON tree.parent = obr.ref_id ".
			"WHERE tree.tree = 1 ".
			"AND assign = 'y' ".
			"AND rol_id = ".$ilDB->quote($a_role_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$obj_id = $row->obj_id;
		}
		
		return $obj_id ? $obj_id : 0;
	}
	
	/**
	 * return if role is only attached to deleted role folders
	 *
	 * @param int $a_role_id
	 * @return boolean
	 */
	public function isRoleDeleted ($a_role_id){
		$rolf_list = $this->getFoldersAssignedToRole($a_role_id, false);
		$deleted = true;
		if (count($rolf_list))
		{
			foreach ($rolf_list as $rolf) {      	        
	    		// only list roles that are not set to status "deleted"
	    		if (!$this->isDeleted($rolf))
				{
	   				$deleted = false;
	   				break;
				}
			}
		}
		return $deleted;	
	}
	
	
	function getRolesForIDs($role_ids, $use_templates)
	{
		global $ilDB;
		
		$role_list = array();

		$where = $this->__setTemplateFilter($use_templates);

		$q = "SELECT DISTINCT * FROM object_data ".
			 "JOIN rbac_fa ".$where.
			 "AND object_data.obj_id = rbac_fa.rol_id ".
			 "AND rbac_fa.assign = 'y' " .
			 "AND object_data.obj_id IN (".implode(",", $role_ids).")";
		
		$r = $this->ilDB->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$row["desc"] = $row["description"];
			$row["user_id"] = $row["owner"];
			$role_list[] = $row;
		}
		
		$role_list = $this->__setRoleType($role_list);

		return $role_list;
	}
} // END class.ilRbacReview
?>
