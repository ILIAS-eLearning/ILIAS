<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";

/**
* Class ilObjWorkspaceFolder
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
*
* @extends ilObject2
*/
class ilObjWorkspaceFolder extends ilObject2
{
	var $folder_tree;
	
	function initType()
	{
		$this->type = "wfld";
	}

	function setFolderTree($a_tree)
	{
		$this->folder_tree =& $a_tree;
	}
	
	/**
	 * Clone folder
	 *
	 * @access public
	 * @param object clone
	 * @param int target id
	 * @param int copy id
	 */
	public function doCloneObject($a_new_object, $a_target_id, $a_copy_id = 0)
	{
		
	}

	/**
	 * Clone object dependencies (crs items, preconditions)
	 *
	 * @access public
	 * @param int target ref id of new course
	 * @param int copy id
	 * 
	 */
	public function cloneDependencies($a_target_id,$a_copy_id)
	{
		
	}
	
	/**
	 * private functions which iterates through all folders and files 
	 * and create an according file structure in a temporary directory. This function works recursive. 
	 *
	 * @param integer $refid reference it
	 * @param tmpdictory $tmpdir
	 * @return returns first created directory
	 */
	private static function recurseFolder ($refid, $title, $tmpdir) {
		global $rbacsystem, $tree, $ilAccess;
				
		$tmpdir = $tmpdir.DIRECTORY_SEPARATOR.ilUtil::getASCIIFilename($title);
		ilUtil::makeDir($tmpdir);
		
		$subtree = $tree->getChildsByTypeFilter($refid, array("fold","file"));
		
		foreach ($subtree as $child) 
		{
			if (!$ilAccess->checkAccess("read", "", $child["ref_id"]))
			{
				continue;			
			}
			if (ilObject::_isInTrash($child["ref_id"]))
			{
				continue;
			}
			if ($child["type"] == "fold")
			{
				ilObjFolder::recurseFolder ($child["ref_id"], $child["title"], $tmpdir);
			} else {
				$newFilename = $tmpdir.DIRECTORY_SEPARATOR.ilUtil::getASCIIFilename($child["title"]);
				// copy to temporal directory
				$oldFilename = ilObjFile::_lookupAbsolutePath($child["obj_id"]);
				if (!copy ($oldFilename, $newFilename))
				{
					throw new ilFileException("Could not copy ".$oldFilename." to ".$newFilename);
				}	
				touch($newFilename, filectime($oldFilename));								
			}
		}
		
	}
	
	public function downloadFolder() {
		global $lng, $rbacsystem, $ilAccess;
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		include_once 'Modules/File/classes/class.ilObjFile.php';
		include_once 'Modules/File/classes/class.ilFileException.php';
		if (!$ilAccess->checkAccess("read", "", $this->getRefId()))
		{
			$this->ilErr->raiseError(get_class($this)."::downloadFolder(): missing read permission!",$this->ilErr->WARNING);
		}
		if (ilObject::_isInTrash($this->getRefId()))
		{
			$this->ilErr->raiseError(get_class($this)."::downloadFolder(): object is trashed!",$this->ilErr->WARNING);
		}
		
		$zip = PATH_TO_ZIP;
		$tmpdir = ilUtil::ilTempnam();		
		ilUtil::makeDir($tmpdir);
		$basename = ilUtil::getAsciiFilename($this->getTitle());
		$deliverFilename = $basename.".zip";
		$zipbasedir = $tmpdir.DIRECTORY_SEPARATOR.$basename;
		$tmpzipfile = $tmpdir.DIRECTORY_SEPARATOR.$deliverFilename;
		
		try {
			ilObjFolder::recurseFolder ($this->getRefId(), $this->getTitle(), $tmpdir);
			ilUtil::zip($zipbasedir, $tmpzipfile);
			rename($tmpzipfile,$zipfile = ilUtil::ilTempnam());
			ilUtil::delDir($tmpdir);
			ilUtil::deliverFile($zipfile,$deliverFilename,'',false,true);
		} catch (ilFileException $e) {
			ilUtil::sendInfo($e->getMessage(), true);
		}
	}
	
	/**
	* Get container view mode
	*/
	function getViewMode()
	{		
		return ilContainer::VIEW_BY_TYPE;
	}

	/**
	* Add additional information to sub item, e.g. used in
	* courses for timings information etc.
	*/
	function addAdditionalSubItemInformation(&$a_item_data)
	{
		
	}

} 

?>