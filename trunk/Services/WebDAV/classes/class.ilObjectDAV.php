<?php
// BEGIN WebDAV
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Class ilObjectDAV
*
* Superclass of all object specific handlers for DAV requests.
* Instances of this class are created and used by ilDAVServer.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id: class.ilDAVServer.php,v 1.0 2005/07/08 12:00:00 wrandelshofer Exp $
*
* @package webdav
*/
class ilObjectDAV 
{
	/**
	 * Refid to the object.
	 */
	var $refId;
	
	/**
	 * Application layer object.
	 */
	var $obj;

	/**
	 * The ObjectDAV prints lots of log messages to the ilias log, if this
	 * variable is set to true.
	 */
	var $isDebug = false;
	
	/** 
	* Constructor
	*
	* @param int A refId to the object.
	*/
	function ilObjectDAV($refId, $obj = null) 
	{
		if (is_object($obj))
		{
			$this->writelog('<constructor>('.$refId.','.get_class($obj).')');
		}
		$this->refId = $refId;
		$this->obj =& $obj;
	}
	
	
	/**
	 * Returns the ref id of this object.
	 * @return int.
	 */
	function getRefId()
	{
		return $this->refId;
	}
	/**
	 * Returns the object id of this object.
         * @return int.
	 */
	function getObjectId()
	{
		return ($this->obj == null) ? null : $this->obj->getId();
	}
	
	/**
	 * Returns the node id of this object.
	 * This only used by objects that are represented as a single object in RBAC, but
	 * as multiple objects in WebDAV.
         * @return int.
	 */
	function getNodeId()
	{
		return 0;
	}
	
	/**
	 * Initializes the object after it has been converted from NULL.
	 * We create all the additonal object data that is needed, to make the object work.
	 *
         * @return void.
	 */
	function initFromNull()
	{
		$this->obj->setPermissions($this->getRefId());
	}
	
	
	
	
	/**
	 * Reads the object data.
         * @return void.
	 */
	function read()
	{
		global $ilias;
		
		if (is_null($this->obj))
		{
			$this->obj =& $ilias->obj_factory->getInstanceByRefId($this->getRefId());
			$this->obj->read();
		}
	}
	/**
	 * Writes the object data.
         * @return void.
	 */
	function write()
	{
		$this->writelog('write() refid='.$this->refId);
		$this->obj->update();
	}
	
	
	/**
	 * Returns the resource name of this object.
	 * Precondition: Object must have been read.
         * @return String.
	 */
	function getResourceName()
	{
		return $this->obj->getUntranslatedTitle();
	}
	/**
	 * Sets the resource name of this object.
	 * Precondition: Object must have been read.
         * @parm String.
	 */
	function setResourceName($name)
	{
		$this->writelog('setResourceName('.$name.')');
		return $this->obj->setTitle($name);
	}
	/**
	 * Returns the display name of this object.
	 * Precondition: Object must have been read.
         * @return String.
	 */
	function getDisplayName()
	{
		return $this->obj->getTitle();
	}
	
	/**
	 * Returns the creation date of this object as a Unix timestamp.
	 * Precondition: Object must have been read. 
         * @return int.
	 */
	function getCreationTimestamp()
	{
		return strtotime($this->obj->getCreateDate());
	}
	
	/**
	 * Returns the modification date of this object as a Unix timestamp.
	 * Precondition: Object must have been read.
         * @return int.
	 */
	function getModificationTimestamp()
	{
		return strtotime($this->obj->getLastUpdateDate());
	}
	
	/**
	 * Returns the DAV resource type of this object.
	 * 
         * @return String "collection", "" (file) or "null".
	 */
	function getResourceType()
	{
		return "";
	}
	
	/**
	 * Returns true if this object is a DAV collection.
	 * 
         * @return bool.
	 */
	function isCollection()
	{
		return $this->getResourceType() == 'collection';
	}
	/**
	 * Returns true if this object is a DAV file.
	 * 
         * @return bool.
	 */
	function isFile()
	{
		return $this->getResourceType() == '';
	}
	/**
	 * Returns true if this is a null resource.
	 * Null objects are used for locking names.
	 */
	function isNullResource()
	{
		return $this->getResourceType() == 'null';
	}
        
	/**
	 * Returns the mime type of the content of this object.
         * @return String.
	 */
	function getContentType()
	{
		return 'application/x-non-readable';//'application/octet-stream';
	}
	/**
	 * Sets the mime type of the content of this object.
	 * @param String.
	 */
	function setContentType($type)
	{
		// subclass responsibility
	}
	/**
	 * Sets the length (number of bytes) of the content of this object.
	 * @param Integer.
	 */
	function setContentLength($length)
	{
		// subclass responsibility
	}
	/**
	 * Returns the number of bytes of the content.
         * @return int.
	 */
	function getContentLength()
	{
		return 0;
	}
	/**
	 * Returns the content of the object as a stream.
         * @return Stream or null, if the content does not support streaming.
	 */
	function getContentStream()
	{
		return null;
	}
	/**
	 * Returns an output stream to the content.
         * @return Stream or null, if the content does not support streaming.
	 */
	function getContentOutputStream()
	{
		return null;
	}
	/**
	 * Returns the length of the content output stream.
         * <p>
         * This method is used by the ilDAVServer, if a PUT operation
         * has been performed for which the client did not specify the
         * content length.
         * 
	 * @param Integer.
	 */
	function getContentOutputStreamLength()
	{
		// subclass responsibility
	}
	/**
	 * Returns the content of the object as a byte array.
         * @return Array, String. Return null if the content can not be delivered
	 * as data.
	 */
	function getContentData()
	{
		return null;
	}
	
	/**
	 * Returns true if the object is online.
	 */
	function isOnline()
	{
		return true;
	}
	
	/**	
	* Returns whether a specific operation is permitted for the current user.
	* This method takes all conditions into account that are required to perform
	* the specified action on behalf of the current user.
	*
	* @param	string		one or more operations, separated by commas (i.e.: visible,read,join)
	* @param	string		the ILIAS type definition abbreviation (i.e.: frm,grp,crs)
	* 				(only needed for 'create' operation'.
	* @return	boolean		returns true if ALL passed operations are given, otherwise false
	*/
	function isPermitted($operations, $type = '')
	{
		// Mount instructions are always visible
		if(isset($_GET['mount-instructions']))
		{
			return true;
		}
		
		// The 'visible' operation is only permitted if the object is online,
		// or if the user is also permitted the perform the 'write' operation.
		if (false)		// old implementation deactivated
		{
			$ops = explode(',',$operations);
			if (in_array('visible',$ops) && ! in_array('write',$ops))
			{
				if (! $this->isOnline()) {
					$operations .= ',write';
				}
			}
		
			global $rbacsystem;
			return $rbacsystem->checkAccess($operations, $this->getRefId(), $type);
		}
		else 		// this one fixes bug #5367
		{
			$GLOBALS['ilLog']->write('Checking permission for ref_id: '.$this->getRefId());
			$GLOBALS['ilLog']->write("Operations: ".print_r($operations,true));
			
			global $ilAccess;
			$operations = explode(",",$operations."");
			foreach ($operations as $operation)
			{
				if (!$ilAccess->checkAccess($operation, '', $this->getRefId(), $type))
				{
					$GLOBALS['ilLog']->write(__METHOD__.': Permission denied for user '.$GLOBALS['ilUser']->getId());
					return false;
				}
			}
			return true;
		}
	}
	
	/**
	 * Returns the ilias type of the current object.
	 */
	function getILIASType()
	{
		if($this->obj instanceof ilObject)
		{
			return $this->obj->getType();
		}
		$GLOBALS['ilLog']->write(__METHOD__.': Invalid object given, class='.get_class($this->obj));
		$GLOBALS['ilLog']->logStack();
	}
	/**
	 * Returns the ilias type for collections that can be created as children of this object.
	 */
	function getILIASCollectionType()
	{
		return 'fold';
	}
	/**
	 * Returns the ilias type for files that can be created as children of this object.
	 */
	function getILIASFileType()
	{
		return 'file';
	}

	/**
	 * Creates a new version of the object.
	 * Only objects which support versioning need to implement this method.
	 */
	function createNewVersion() {
	}

	
	/**	
	* Creates a dav collection as a child of this object.
	*
	* @param	string		the name of the collection.
	* @return	ilObjectDAV	returns the created collection, or null if creation failed.
	*/
	function createCollection($name)
	{
		global $tree;

		// create and insert Folder in tree
		require_once 'Modules/Folder/classes/class.ilObjFolder.php';
		$newObj = new ilObjFolder(0);
		$newObj->setType($this->getILIASCollectionType());
		$newObj->setTitle($name);
		//$newObj->setDescription('');
		$newObj->create();
		$newObj->createReference();
		$newObj->setPermissions($this->getRefId());
		$newObj->putInTree($this->getRefId());
		
		require_once 'class.ilObjFolderDAV.php';
		return new ilObjFolderDAV($newObj->getRefId(), $newObj);
	}
	/**	
	* Creates a dav file as a child of this object.
	*
	* @param	string		the name of the file.
	* @return	ilObjectDAV	returns the created object, or null if creation failed.
	*/
	function createFile($name)
	{
		global $tree;

		// create and insert Folder in tree
		require_once 'Modules/File/classes/class.ilObjFile.php';
		$newObj = new ilObjFile(0);
		$newObj->setType($this->getILIASFileType());
		$newObj->setTitle($name);
		$newObj->setFileName($name);
		include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
		$mime = ilMimeTypeUtil::getMimeType("", $name, 'application/octet-stream');
		//$newObj->setFileType('application/octet-stream');
		$newObj->setFileType($mime);
		//$newObj->setDescription('');
		$newObj->create();
		$newObj->createReference();
		$newObj->setPermissions($this->getRefId());
		$newObj->putInTree($this->getRefId());
		//$newObj->createDirectory();
		
		require_once 'class.ilObjFileDAV.php';
		$objDAV = new ilObjFileDAV($newObj->getRefId(), $newObj);
		/*		
		$fs = $objDAV->getContentOutputStream();
		fwrite($fs,' ');
		fclose($fs);
		*/
		return $objDAV;
	}
	/**	
	* Creates a dav file as a child of this object.
	*
	* @param	string		the name of the file.
	* @return	ilObjectDAV	returns the created object, or null if creation failed.
	*/
	function createFileFromNull($name, &$nullDAV)
	{
		global $tree;

		// create and insert Folder in tree
		require_once 'Modules/File/classes/class.ilObjFile.php';
		$objDAV =& $nullDAV->convertToILIASType($this->getRefId(), $this->getILIASFileType());
		$objDAV->initFromNull();
		return $objDAV;
	}
	/**	
	* Creates a dav null object as a child of this object.
	* null objects are used for locking names.
	*
	* @param	string		the name of the null object.
	* @return	ilObjectDAV	returns the created object, or null if creation failed.
	*/
	function createNull($name)
	{
		global $tree;

		// create and insert Folder in tree
		require_once './Services/Object/classes/class.ilObject.php';
		$newObj = new ilObject(0);
		$newObj->setType('null');
		$newObj->setTitle($name);
		$newObj->create();
		$newObj->createReference();
		$newObj->setPermissions($this->getRefId());
		$newObj->putInTree($this->getRefId());
		
		require_once 'class.ilObjNullDAV.php';
		$objDAV = new ilObjNullDAV($newObj->getRefId(), $newObj);
		
		return $objDAV;
	}
	
	
	
	/**	
	* Removes the specified child from this object.
	*
	* @param	ilObjectDAV	the child to be removed.
	*/
	function remove($objDAV)
	{
		global $tree, $rbacadmin;
		
		$subnodes = $tree->getSubTree($tree->getNodeData($objDAV->getRefId()));
		foreach ($subnodes as $node)
		{
			$rbacadmin->revokePermission($node["child"]);
			$affectedUsers = ilUtil::removeItemFromDesktops($node["child"]);
		}
		$tree->saveSubTree($objDAV->getRefId());
		$tree->deleteTree($tree->getNodeData($objDAV->getRefId()));
	}
	
	/**
	* Adds a copy of the specified object as a child to this object.
	*
	* @param	ilObjectDAV	the object to be copied.
	* @param	string the new name of the copy (optional).
	* @return	A new ilObjectDAV object representing the cloned object.
	*/
	function addCopy(&$objDAV, $newName = null)
	{
		$this->writelog("addCopy($objDAV,$newName) ....");
		global $rbacadmin, $tree;
		$revIdMapping = array(); 
		$newRef = $this->cloneNodes($objDAV->getRefId(),$this->getRefId(),$revIdMapping, $newName);
		//$rbacadmin->adjustMovedObjectPermissions($newRef, $tree->getParentId($objDAV->getRefId()));
		return $this->createObject($newRef, $objDAV->getILIASType());
		$this->writelog('... addCopy done.');
	}

	/**
	* Recursively clones all nodes of the RBAC tree.
	* 
	* @access	private
	* @param	integer ref_id of source object
	* @param	integer ref_id of destination object
	* @param	array	mapping new_ref_id => old_ref_id
	* @param	string the new name of the copy (optional).
	* @return	The ref_id pointing to the cloned object.
	*/
	function cloneNodes($srcRef,$dstRef,&$mapping, $newName=null)
	{
		$this->writelog("cloneNodes($srcRef,$dstRef,$mapping,$newName)");
		global $tree;
		global $ilias;
		
		// clone the source node
		$srcObj =& $ilias->obj_factory->getInstanceByRefId($srcRef);
		$this->writelog('cloneNodes cloning srcRef='.$srcRef.' dstRef='.$dstRef.'...');
		$newObj = $srcObj->cloneObject($dstRef);
		$newRef = $newObj->getRefId();
		
		// We must immediately apply a new name to the object, to
		// prevent confusion of WebDAV clients about having two objects with identical
		// name in the repository.
		$this->writelog("cloneNodes newname not null? ".(! is_null($newName)));
		if (! is_null($newName))
		{
			$newObjDAV = $this->createObject($newRef, $srcObj->getType()); 
			$newObjDAV->setResourceName($newName);
			$newObjDAV->write();
		}
		unset($srcObj);
		$mapping[$newRef] = $srcRef;

		// clone all children of the source node
		$children = $tree->getChilds($srcRef);
		foreach ($tree->getChilds($srcRef) as $child)
		{
			// Don't clone role folders, because it does not make sense to clone local roles
			// FIXME - Maybe it does make sense (?)
			if ($child["type"] != 'rolf')
			{
				$this->cloneNodes($child["ref_id"],$newRef,$mapping,null);
			}
			else
			{
				if (count($rolf = $tree->getChildsByType($newRef,"rolf")))
				{
					$mapping[$rolf[0]["ref_id"]] = $child["ref_id"];
				}
			}
		}
		$this->writelog('cloneNodes ...cloned srcRef='.$srcRef.' dstRef='.$dstRef.' newRef='.$newRef);
		return $newRef;
	}
	
	/**
	* Adds (moves) the specified object as a child to this object.
	* The object is removed from its former parent.
	*
	* @param	ilObjectDAV	the object to be moved.
	* @param	string the new name (optional).
	*/
	function addMove(&$objDAV, $newName = null)
	{
		global $tree;
		global $rbacadmin;
		global $ilias;
		global $log;
	
		$this->writelog('addMove('.$objDAV->getRefId().' to '.$this->getRefId().', newName='.$newName.')');
		
		// Step 0:Assign new name to moved object
		if (! is_null($newName))
		{
			$objDAV->setResourceName($newName);
			$objDAV->write();
		}
		
		// Step 1: Store old parent
		$old_parent = $tree->getParentId($objDAV->getRefId());
		
		// Step 2: Move the tree
		$tree->moveTree($objDAV->getRefId(),$this->getRefId());
		
		// Step 3: Repair permissions
		$rbacadmin->adjustMovedObjectPermissions($objDAV->getRefId(), $old_parent);
		
		/*
		// STEP 1: Move subtree to trash
		$this->writelog('addMove('.$objDAV->getRefId().' to '.$this->getRefId().') step 1: move subtree to trash');
		$subnodes = $tree->getSubTree($tree->getNodeData($objDAV->getRefId()));
		foreach ($subnodes as $node)
		{
			$rbacadmin->revokePermission($node["child"]);
			$affectedUsers = ilUtil::removeItemFromDesktops($node["child"]);
		}
		$tree->saveSubTree($objDAV->getRefId());
		$tree->deleteTree($tree->getNodeData($objDAV->getRefId()));
		
		// STEP 2: Move subtree to new location
		// TODO: this whole put in place again stuff needs revision. Permission settings get lost.
		$this->writelog('addMove() step 2: move subtree to new location');
		// put top node to dest
		$rbacadmin->revokePermission($subnodes[0]['child']);
		$obj_data =& $ilias->obj_factory->getInstanceByRefId($subnodes[0]['child']);
		$obj_data->putInTree($this->getRefId());
		$obj_data->setPermissions($this->getRefId());
		array_shift($subnodes);
		
		// put all sub nodes to their parent (of which we have moved top already to dest).
		foreach ($subnodes as $node)
		{
			$rbacadmin->revokePermission($node['child']);
			$obj_data =& $ilias->obj_factory->getInstanceByRefId($node['child']);
			$obj_data->putInTree($node['parent']);
			$obj_data->setPermissions($node['parent']);
		}
		
		// STEP 3: Remove trashed objects from system
		$this->writelog('addMove('.$objDAV->getRefID().') step 3: remove trashed objects from system');
		require_once 'Services/Tree/classes/class.ilTree.php';
		$trashTree = new ilTree(- (int) $objDAV->getRefId());
		$node = $trashTree->getNodeData($objDAV->getRefId());
		$subnodes = $trashTree->getSubTree($node);

		// remember already checked deleted node_ids
		$checked[] = -(int) $objDAV->getRefId();

		// dive in recursive manner in each already deleted subtrees and remove these objects too
		$this->removeDeletedNodes($objDAV->getRefId(), $checked, false);

		// delete trash tree
		$tree->deleteTree($node);
		$this->writelog('addMove('.$objDAV->getRefID().') all 3 steps done');
		*/
	}
	
	/**
	* remove already deleted objects within the objects in trash
	* recursive function
	*
	* @access	public
	* @param	integer ref_id of source object
	* @param    boolean 
	*/
	function removeDeletedNodes($a_node_id, $a_checked, $a_delete_objects = true)
	{
		global $ilDB, $log, $ilias, $tree;
		
		$query = "SELECT tree FROM tree WHERE parent = ? AND tree < 0 ";
		$sta = $ilDB->prepare($query,array('integer','integer'));
		$res = $ilDB->execute($sta,array(
			$a_node_id,
			0));
		

		while($row = $ilDB->fetchObject($res))
		{
			// only continue recursion if fetched node wasn't touched already!
			if (!in_array($row->tree,$a_checked))
			{
				$deleted_tree = new ilTree($row->tree);
				$a_checked[] = $row->tree;

				$row->tree = $row->tree * (-1);
				$del_node_data = $deleted_tree->getNodeData($row->tree);
				$del_subtree_nodes = $deleted_tree->getSubTree($del_node_data);

				$this->removeDeletedNodes($row->tree,$a_checked);
			
				if ($a_delete_objects)
				{
					foreach ($del_subtree_nodes as $node)
					{
						$node_obj =& $ilias->obj_factory->getInstanceByRefId($node["ref_id"]);
						
						// write log entry
						/*$this->writelog("removeDeletedNodes(), delete obj_id: ".$node_obj->getId().
							", ref_id: ".$node_obj->getRefId().", type: ".$node_obj->getType().", ".
							"title: ".$node_obj->getTitle());
						*/	
						$node_obj->delete();
					}
				}
			
				$tree->deleteTree($del_node_data);
				
				// write log entry
				//$this->writelog("removeDeletedNodes(), deleted tree, tree_id: ".$del_node_data["tree"].", child: ".$del_node_data["child"]);
			}
		}
		
		return true;
	}
	/**
	 * Returns the children of this object.
	 *
     * @return Array<ilObjectDAV>. Returns an empty array, if this object is not
	 * a collection..
	 */
	function children()
	{
		// FIXME: Remove duplicate entries from this list, because of RFC2518, chapter 5.2
		//        If a duplicate is found, the older object must win. We use the object
		//        id to determine this. This is based on the assumption, that new objects
		//        have higher object id's then older objects.
		
		global $tree;
		
		$childrenDAV = array();
		// Performance optimization. We sort the children using PHP instead of using the database.
		//$childrenData =& $tree->getChilds($this->getRefId(),'title');
		$childrenData =& $tree->getChilds($this->getRefId(),'');
		foreach ($childrenData as $data)
		{
			$childDAV =& $this->createObject($data['ref_id'],$data['type']);
			if (! is_null($childDAV))
			{
				// Note: We must not assign with =& here, because this will cause trouble
				//       when other functions attempt to work with the $childrenDAV array.
				$childrenDAV[] = $childDAV;
			}
			
		}
		return $childrenDAV;
	}
	/**
	 * Returns the children of this object with the specified permissions.
	 *
	* @param	string		one or more operations, separated by commas (i.e.: visible,read,join)
	* @param	string		the ILIAS type definition abbreviation (i.e.: frm,grp,crs)
	* 				(only needed for 'create' operation'.
         * @return Array<ilObjectDAV>. Returns an empty array, if this object is not
	 * a collection..
	 */
	function childrenWithPermission($operations, $type  ='')
	{
	//$this->writelog('@'.$this->getRefId().'.childrenWithPermission('.$operations.','.$type.')');
		$childrenDAV = $this->children();
		$permittedChildrenDAV = array();
		foreach ($childrenDAV as $childDAV)
		{
			if ($childDAV->isPermitted($operations, $type))
			{
				$permittedChildrenDAV[] = $childDAV;
			}
			
		}
	//$this->writelog('@'.$this->getRefId().'.childrenWithPermission():'.count($permittedChildrenDAV).' children');
		return $permittedChildrenDAV;
	}
		
	/**
	 * Static factory method to create a DAV object for a given refId and type.
	 *
	 * @param  int refID.
	 * @param  String type The ILIAS object type.
	 * @return ilObjectDAV. Returns null, if no DAV object can be constructed for
	 * the specified type.
	 */
	function createObject($refId, $type)
	{
		$newObj = null;
		switch ($type)
		{
			case 'mountPoint' :
				require_once 'class.ilObjMountPointDAV.php';
				$newObj = new ilObjMountPointDAV($refId,null);
				break;
			case 'root' :
				require_once 'class.ilObjRootDAV.php';
				$newObj = new ilObjRootDAV($refId,null);
				break;
			case 'cat' :
				require_once 'class.ilObjCategoryDAV.php';
				$newObj = new ilObjCategoryDAV($refId,null);
				break;
			case 'fold' :
				require_once 'class.ilObjFolderDAV.php';
				$newObj = new ilObjFolderDAV($refId,null);
				break;
			case 'crs' :
				require_once 'class.ilObjCourseDAV.php';
				$newObj = new ilObjCourseDAV($refId,null);
				break;
			case 'grp' :
				require_once 'class.ilObjGroupDAV.php';
				$newObj = new ilObjGroupDAV($refId,null);
				break;
			case 'file' :
				require_once 'class.ilObjFileDAV.php';
				$newObj = new ilObjFileDAV($refId,null);
				break;
			case 'null' :
				require_once 'class.ilObjNullDAV.php';
				$newObj = new ilObjNullDAV($refId,null);
				break;
			default :
				break;
		}
		if (! is_null($newObj))
		{
			$newObj->read();
		}
		return $newObj;
	}
        /**
         * Writes a message to the logfile.,
         *
         * @param  message String.
         * @return void.
         */
	function writelog($message) 
	{
		if ($this->isDebug)
		{
			global $log, $ilias;
			$log->write(
				$ilias->account->getLogin()
				.' DAV .'.get_class($this).' '.str_replace("\n",";",$message)
			);
			/*
			$fh = fopen('/opt/ilias/log/ilias.log', 'a');
			fwrite($fh, date('Y-m-d h:i:s '));
			fwrite($fh, str_replace("\n",";",$message));
			fwrite($fh, "\n\n");
			fclose($fh);		
			*/
		}
	}

	/**
	 * This method is needed, because the object class in PHP 5.2 does not
	 * have a default implementation of this method anymore.
	 */
	function __toString() {
		return get_class($this).'#'.$this->getObjectId();
	}
}
// END WebDAV
?>
