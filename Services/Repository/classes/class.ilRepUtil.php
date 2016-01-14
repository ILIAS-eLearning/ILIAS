<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectFactory.php");

/**
* Repository Utilities (application layer, put GUI related stuff into ilRepUtilGUI)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesRepository
*/
class ilRepUtil
{

	/**
	* Delete objects. Move them to trash (if trash feature is enabled).
	*
	* @param	integer		current ref id
	* @param	array		array of ref(!) ids to be deleted
	*/
	static public function deleteObjects($a_cur_ref_id, $a_ids)
	{
		global $ilAppEventHandler, $rbacsystem, $rbacadmin, $log, $ilUser, $tree, $lng,
			$ilSetting;
		
		include_once './Services/Payment/classes/class.ilPaymentObject.php';
		include_once("./Services/Repository/exceptions/class.ilRepositoryException.php");
		
		// Remove duplicate ids from array
		$a_ids = array_unique((array) $a_ids);

		// FOR ALL SELECTED OBJECTS
		foreach ($a_ids as $id)
		{
			if ($tree->isDeleted($id))
			{
				$log->write(__METHOD__.': Object with ref_id: '.$id.' already deleted.');
				throw new ilRepositoryException($lng->txt("msg_obj_already_deleted"));
			}
			
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $tree->getNodeData($id);
			$subtree_nodes = $tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK DELETE PERMISSION OF ALL OBJECTS
			foreach ($subtree_nodes as $node)
			{
				if($node['type'] == 'rolf')
				{
					continue;
				}
				if (!$rbacsystem->checkAccess('delete',$node["child"]))
				{
					$not_deletable[] = $node["child"];
					$perform_delete = false;
				}
				else if(ilPaymentObject::_isBuyable($node['child']))
				{
					$buyable[] = $node['child'];
					$perform_delete = false;
				}
			}
		}

		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO DELETE
		if (count($not_deletable))
		{
			$not_deletable = implode(',',$not_deletable);
			ilSession::clear("saved_post");
			throw new ilRepositoryException(
				$lng->txt("msg_no_perm_delete")." ".$not_deletable."<br/>".$lng->txt("msg_cancel"));
		}

		if(count($buyable))
		{
			foreach($buyable as $id)
			{
				$tmp_object = ilObjectFactory::getInstanceByRefId($id);

				$titles[] = $tmp_object->getTitle();
			}
			$title_str = implode(',',$titles);

			throw new ilRepositoryException(
				$lng->txt('msg_obj_not_deletable_sold').' '.$title_str);
		}

		// DELETE THEM
		if (!$all_node_data[0]["type"])
		{
// alex: this branch looks suspicious to me... I deactivate it for
// now. Objects that aren't in the tree should overwrite this method.
throw new ilRepositoryException($lng->txt("ilRepUtil::deleteObjects: Type information missing."));
			// OBJECTS ARE NO 'TREE OBJECTS'
			if ($rbacsystem->checkAccess('delete', $a_cur_ref_id))
			{
				foreach($a_ids as $id)
				{
					$obj =& ilObjectFactory::getInstanceByObjId($id);
					$obj->delete();
					
					// write log entry
					$log->write("ilObjectGUI::confirmedDeleteObject(), deleted obj_id ".$obj->getId().
						", type: ".$obj->getType().", title: ".$obj->getTitle());
				}
			}
			else
			{
				throw new ilRepositoryException(
					$lng->txt("no_perm_delete")."<br/>".$lng->txt("msg_cancel"));
			}
		}
		else
		{
			// SAVE SUBTREE AND DELETE SUBTREE FROM TREE
			$affected_ids = array();
			$affected_parents = array();
			foreach ($a_ids as $id)
			{
				if($tree->isDeleted($id))
				{
					$log->write(__METHOD__.': Object with ref_id: '.$id.' already deleted.');
					throw new ilRepositoryException($lng->txt("msg_obj_already_deleted"));
				}
				
				// DELETE OLD PERMISSION ENTRIES
				$subnodes = $tree->getSubtree($tree->getNodeData($id));

				foreach ($subnodes as $subnode)
				{
					$rbacadmin->revokePermission($subnode["child"]);
					// remove item from all user desktops
					$affected_users = ilUtil::removeItemFromDesktops($subnode["child"]);
					
					$affected_ids[$subnode["child"]] = $subnode["child"];
					$affected_parents[$subnode["child"]] = $subnode["parent"];
					
					// TODO: inform users by mail that object $id was deleted
					//$mail->sendMail($id,$msg,$affected_users);
					// should go to appevents at the end
				}
				
				// TODO: needs other handling
				// This class shouldn't have to know anything about ECS
				include_once('./Services/WebServices/ECS/classes/class.ilECSObjectSettings.php');
				ilECSObjectSettings::_handleDelete($subnodes);				

				if(!$tree->saveSubTree($id, true))
				{
					$log->write(__METHOD__.': Object with ref_id: '.$id.' already deleted.');
					throw new ilRepositoryException($lng->txt("msg_obj_already_deleted"));
				}

				// write log entry
				$log->write("ilObjectGUI::confirmedDeleteObject(), moved ref_id ".$id.
					" to trash");
				
				// remove item from all user desktops
				$affected_users = ilUtil::removeItemFromDesktops($id);
				
				$affected_ids[$id] = $id;

				// TODO: inform users by mail that object $id was deleted
				//$mail->sendMail($id,$msg,$affected_users);
			}
			
			// send global events
			foreach ($affected_ids as $aid)
			{
				$ilAppEventHandler->raise("Services/Object", "toTrash",
					array( "obj_id" => ilObject::_lookupObjId($aid),
						   "ref_id" => $aid,
						   "old_parent_ref_id" => $affected_parents[$aid]
						 ));
			}
			// inform other objects in hierarchy about paste operation
			//$this->object->notify("confirmedDelete", $_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$_SESSION["saved_post"]);
		}
		
		if (!$ilSetting->get('enable_trash'))
		{
			ilRepUtil::removeObjectsFromSystem($a_ids);
		}
	}
	
	/**
	* remove objects from trash bin and all entries therefore every object needs a specific deleteObject() method
	*
	* @access	public
	*/
	public function removeObjectsFromSystem($a_ref_ids, $a_from_recovery_folder = false)
	{
		global $rbacsystem, $log, $ilAppEventHandler, $tree;
		
		$affected_ids = array();
		
		// DELETE THEM
		foreach ($a_ref_ids as $id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			if (!$a_from_recovery_folder)
			{
				$saved_tree = new ilTree(-(int)$id);
				$node_data = $saved_tree->getNodeData($id);
				$subtree_nodes = $saved_tree->getSubTree($node_data);
			}
			else
			{
				$node_data = $tree->getNodeData($id);
				$subtree_nodes = $tree->getSubTree($node_data);
			}

			// BEGIN ChangeEvent: Record remove from system.
			require_once('Services/Tracking/classes/class.ilChangeEvent.php');
			// Record write event
			global $ilUser, $tree;
			$parent_data = $tree->getParentNodeData($node_data['ref_id']);
			ilChangeEvent::_recordWriteEvent($node_data['obj_id'], $ilUser->getId(), 'purge', 
				$parent_data['obj_id']);			
			// END ChangeEvent: Record remove from system.

			// remember already checked deleted node_ids
			if (!$a_from_recovery_folder)
			{
				$checked[] = -(int) $id;
			}
			else
			{
				$checked[] = $id;
			}

			// dive in recursive manner in each already deleted subtrees and remove these objects too
			ilRepUtil::removeDeletedNodes($id, $checked, true, $affected_ids);

			foreach ($subtree_nodes as $node)
			{
				if(!$node_obj =& ilObjectFactory::getInstanceByRefId($node["ref_id"],false))
				{
					continue;
				}

				// write log entry
				$log->write("ilObjectGUI::removeFromSystemObject(), delete obj_id: ".$node_obj->getId().
					", ref_id: ".$node_obj->getRefId().", type: ".$node_obj->getType().", ".
					"title: ".$node_obj->getTitle());
				$affected_ids[$node["ref_id"]] = array(
													"ref_id" => $node["ref_id"],
													"obj_id" => $node_obj->getId(), 
													"type" => $node_obj->getType(),
													"old_parent_ref_id" => $node["parent"]);
					
				// this is due to bug #1860 (even if this will not completely fix it)
				// and the fact, that media pool folders may find their way into
				// the recovery folder (what results in broken pools, if the are deleted)
				// Alex, 2006-07-21
				if (!$a_from_recovery_folder || $node_obj->getType() != "fold")
				{
					$node_obj->delete();
				}
			}

			// Use the saved tree object here (negative tree_id)
			if (!$a_from_recovery_folder)
			{
				$saved_tree->deleteTree($node_data);
			}
			else
			{
				$tree->deleteTree($node_data);
			}

			// write log entry
			$log->write("ilObjectGUI::removeFromSystemObject(), deleted tree, tree_id: ".$node_data["tree"].
				", child: ".$node_data["child"]);

		}
		
		// send global events
		foreach ($affected_ids as $aid)
		{
			$ilAppEventHandler->raise("Services/Object", "delete",
				array("obj_id" => $aid["obj_id"],
				"ref_id" => $aid["ref_id"],
				"type" => $aid["type"],
				"old_parent_ref_id" => $aid["old_parent_ref_id"]));
		}
	}
	
	/**
	* Remove already deleted objects within the objects in trash
	*/
	private function removeDeletedNodes($a_node_id, $a_checked, $a_delete_objects,
		&$a_affected_ids)
	{
		global $log, $ilDB, $tree;
		
		$q = "SELECT tree FROM tree WHERE parent= ".
			$ilDB->quote($a_node_id, "integer")." AND tree < 0";
		
		$r = $ilDB->query($q);

		while($row = $ilDB->fetchObject($r))
		{
			// only continue recursion if fetched node wasn't touched already!
			if (!in_array($row->tree,$a_checked))
			{
				$deleted_tree = new ilTree($row->tree);
				$a_checked[] = $row->tree;

				$row->tree = $row->tree * (-1);
				$del_node_data = $deleted_tree->getNodeData($row->tree);
				$del_subtree_nodes = $deleted_tree->getSubTree($del_node_data);

				ilRepUtil::removeDeletedNodes($row->tree,$a_checked, $a_delete_objects, $a_affected_ids);
			
				if ($a_delete_objects)
				{
					foreach ($del_subtree_nodes as $node)
					{
						$node_obj =& ilObjectFactory::getInstanceByRefId($node["ref_id"]);
						
						// write log entry
						$log->write("ilObjectGUI::removeDeletedNodes(), delete obj_id: ".$node_obj->getId().
							", ref_id: ".$node_obj->getRefId().", type: ".$node_obj->getType().", ".
							"title: ".$node_obj->getTitle());
						$a_affected_ids[$node["ref_id"]] = array(
															"ref_id" => $node["ref_id"],
															"obj_id" => $node_obj->getId(), 
															"type" => $node_obj->getType(),
															"old_parent_ref_id" => $node["parent"]);
														
						$node_obj->delete();
						
					}
				}
			
				$tree->deleteTree($del_node_data);
				
				// write log entry
				$log->write("ilObjectGUI::removeDeletedNodes(), deleted tree, tree_id: ".$del_node_data["tree"].
					", child: ".$del_node_data["child"]);
			}
		}
		
		return true;
	}
	
	/**
	* Move objects from trash back to repository
	*/
	function restoreObjects($a_cur_ref_id, $a_ref_ids)
	{
		global $rbacsystem, $log, $ilAppEventHandler, $lng, $tree;

		$cur_obj_id = ilObject::_lookupObjId($a_cur_ref_id);
		
		foreach ($a_ref_ids as $id)
		{
			$obj_data = ilObjectFactory::getInstanceByRefId($id);

			if (!$rbacsystem->checkAccess('create', $a_cur_ref_id, $obj_data->getType()))
			{
				$no_create[] = ilObject::_lookupTitle(ilObject::_lookupObjId($id));
			}
		}

		if (count($no_create))
		{
			include_once("./Services/Repository/exceptions/class.ilRepositoryException.php");
			throw new ilRepositoryException($lng->txt("msg_no_perm_paste")." ".implode(',',$no_create));
		}
		
		$affected_ids = array();
		
		foreach ($a_ref_ids as $id)
		{
			$affected_ids[$id] = $id;
			
			// INSERT AND SET PERMISSIONS
			try {
				ilRepUtil::insertSavedNodes($id, $a_cur_ref_id, -(int) $id, $affected_ids);
			} 
			catch (Exception $e) {
				include_once("./Services/Repository/exceptions/class.ilRepositoryException.php");
				throw new ilRepositoryException('Restore from trash failed with message: ' . $e->getMessage());
			}

			
			// BEGIN ChangeEvent: Record undelete. 
			require_once('Services/Tracking/classes/class.ilChangeEvent.php');
			global $ilUser;

			
			ilChangeEvent::_recordWriteEvent(
					ilObject::_lookupObjId($id),
					$ilUser->getId(), 
					'undelete', 
					ilObject::_lookupObjId($tree->getParentId($id))
			);
			ilChangeEvent::_catchupWriteEvents(
					$cur_obj_id, 
					$ilUser->getId());			
			// END PATCH ChangeEvent: Record undelete.
			
		}

		// send events
		foreach ($affected_ids as $id)
		{
			// send global event
			$ilAppEventHandler->raise("Services/Object", "undelete",
				array("obj_id" => ilObject::_lookupObjId($id), "ref_id" => $id));
		}
	}
	
	/**
	* Recursive method to insert all saved nodes of the clipboard
	*/
	private function insertSavedNodes($a_source_id, $a_dest_id, $a_tree_id, &$a_affected_ids)
	{
		global $rbacadmin, $rbacreview, $log, $tree;

		ilLoggerFactory::getLogger('rep')->debug('Restoring from trash: source_id: '. $a_source_id.', dest_id: '. $a_dest_id.', tree_id:'. $a_tree_id);
		ilLoggerFactory::getLogger('rep')->info('Restoring ref_id  ' . $a_source_id . ' from trash.');
		
		// read child of node
		$saved_tree = new ilTree($a_tree_id);
		$childs = $saved_tree->getChilds($a_source_id);
		
		// then delete node and put in tree
		try {
			$tree->insertNodeFromTrash($a_source_id, $a_dest_id, $a_tree_id, IL_LAST_NODE, true);
		} 
		catch (Exception $e) {
			ilLoggerFactory::getLogger('rep')->error('Restore from trash failed with message: ' . $e->getMessage());
			throw $e;
		}
		
		include_once './Services/Object/classes/class.ilObjectFactory.php';
		$factory = new ilObjectFactory();
		$ref_obj = $factory->getInstanceByRefId($a_source_id,FALSE);
		if($ref_obj instanceof ilObject)
		{
			$lroles = $GLOBALS['rbacreview']->getRolesOfRoleFolder($a_source_id,FALSE);
			foreach($lroles as $role_id)
			{
				include_once './Services/AccessControl/classes/class.ilObjRole.php';
				$role = new ilObjRole($role_id);
				$role->setParent($a_source_id);
				$role->delete();
			}
			if($a_dest_id)
			{
				$ref_obj->setPermissions($a_dest_id);
			}
		}
		foreach ($childs as $child)
		{
			ilRepUtil::insertSavedNodes($child["child"],$a_source_id,$a_tree_id,$a_affected_ids);
		}
	}
	
	
	
	//
	// OBJECT TYPE HANDLING / REMOVAL
	//
	
	protected function findTypeInTrash($a_type)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT child".
			" FROM tree".
			" JOIN object_reference ref ON (tree.child = ref.ref_id)".
			" JOIN object_data od ON (od.obj_id = ref.obj_id)".
			" WHERE tree.tree < ".$ilDB->quote(0, "integer").
			" AND od.type = ".$ilDB->quote($a_type, "text"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row["child"];
		}
		
		return $res;
	}
	
	protected function getObjectTypeId($a_type)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT obj_id".
			" FROM object_data ".
			" WHERE type = ".$ilDB->quote("typ", "text").
			" AND title = ".$ilDB->quote($a_type, "text"));
		$row = $ilDB->fetchAssoc($set);
		return $row["obj_id"];
	}
							
	public function deleteObjectType($a_type)
	{
		global $ilDB, $tree, $ilSetting;
		
		// delete object instances (repository/trash)
		
		$ref_ids_in_tree = $tree->getSubTree($tree->getNodeData(ROOT_FOLDER_ID), false, $a_type);
		if($ref_ids_in_tree)
		{
			$this->deleteObjects(null, $ref_ids_in_tree);
		}
		
		if($ilSetting->get('enable_trash'))
		{
			$ref_ids_in_trash = $this->findTypeInTrash($a_type);
			if($ref_ids_in_trash)
			{
				$this->removeObjectsFromSystem($ref_ids_in_tree);
			}
		}
		
		// delete "component"
		$type_id = $this->getObjectTypeId($a_type);
		if($type_id)
		{				
			// see ilRepositoryObjectPlugin::beforeActivation()
			
			$ilDB->manipulate("DELETE FROM object_data".
				" WHERE obj_id = ".$ilDB->quote($type_id, "integer"));		
			
			// RBAC
			
			// basic operations
			$ilDB->manipulate("DELETE FROM rbac_ta".
				" WHERE typ_id = ".$ilDB->quote($type_id, "integer") /*.
				" AND ".$ilDB->in("ops_id", array(1, 2, 3, 4, 6), "", "integer") */);
			
			// creation operation
			$set = $ilDB->query("SELECT ops_id".
				" FROM rbac_operations ".
				" WHERE class = ".$ilDB->quote("create", "text").
				" AND operation = ".$ilDB->quote("create_".$a_type, "text"));			
			$row = $ilDB->fetchAssoc($set);		
			$create_ops_id = $row["ops_id"];
			if($create_ops_id)
			{				
				$ilDB->manipulate("DELETE FROM rbac_operations".
					" WHERE ops_id = ".$ilDB->quote($create_ops_id, "integer"));
				
				$ilDB->manipulate("DELETE FROM rbac_templates".
					" WHERE ops_id = ".$ilDB->quote($create_ops_id, "integer"));
				
				// container create
				foreach(array("root", "cat", "crs", "grp", "fold") as $parent_type)
				{
					$parent_type_id = $this->getObjectTypeId($parent_type);
					if($parent_type_id)
					{
						$ilDB->manipulate("DELETE FROM rbac_ta".
							" WHERE typ_id = ".$ilDB->quote($parent_type_id, "integer").
							" AND ops_id = ".$ilDB->quote($create_ops_id, "integer"));
					}
				}			
			}
		}
		
		// delete new item settings	
		include_once "Services/Repository/classes/class.ilObjRepositorySettings.php";
		ilObjRepositorySettings::deleteObjectType($a_type);	
	}


}
