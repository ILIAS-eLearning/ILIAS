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

use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizerImpl;
use ILIAS\Filesystem\Util\LegacyPathHelper;

require_once "./Services/Container/classes/class.ilContainer.php";

/**
* Class ilObjFolder
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id: class.ilObjFolder.php 40448 2013-03-08 10:02:02Z jluetzen $
*
* @extends ilObject
*/
class ilObjFolder extends ilContainer
{
    public $folder_tree;
    // bugfix mantis 24309: array for systematically numbering copied files
    private static $duplicate_files = array();


    /**
     * Constructor
     * @access	public
     * @param	integer	reference_id or object_id
     * @param	boolean	treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
        $this->type = "fold";
        parent::__construct($a_id, $a_call_by_reference);
        $this->lng->loadLanguageModule('fold');
    }

    public function setFolderTree($a_tree)
    {
        $this->folder_tree = &$a_tree;
    }
    
    /**
     * Clone folder
     *
     * @access public
     * @param int target id
     * @param int copy id
     *
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        
        // Copy learning progress settings
        include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());
        unset($obj_settings);
        
        return $new_obj;
    }

    /**
    * insert folder into grp_tree
    *
    */
    public function putInTree($a_parent)
    {
        $tree = $this->tree;
        
        if (!is_object($this->folder_tree)) {
            $this->folder_tree = &$tree;
        }

        if ($this->withReferences()) {
            // put reference id into tree
            $this->folder_tree->insertNode($this->getRefId(), $a_parent);
        } else {
            // put object id into tree
            $this->folder_tree->insertNode($this->getId(), $a_parent);
        }
    }
    
    /**
     * Clone object dependencies (crs items, preconditions)
     *
     * @access public
     * @param int target ref id of new course
     * @param int copy id
     *
     */
    public function cloneDependencies($a_target_id, $a_copy_id)
    {
        parent::cloneDependencies($a_target_id, $a_copy_id);

        include_once('Services/Object/classes/class.ilObjectActivation.php');
        ilObjectActivation::cloneDependencies($this->getRefId(), $a_target_id, $a_copy_id);
        
        return true;
    }


    /**
     * private functions which iterates through all folders and files
     * and create an according file structure in a temporary directory. This function works recursive.
     *
     * @param int       $ref_id Reference-ID of Folder
     * @param string    $title  of Folder
     * @param    string $tmpdir (MUST be already relative due to filesystem-service)
     *
     * @return string returns first created directory
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilFileException
     * @throws ilFileUtilsException
     */
    private static function recurseFolder($ref_id, $title, $tmpdir)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ilAccess = $DIC->access();

        $tmpdir .= DIRECTORY_SEPARATOR . ilUtil::getASCIIFilename($title);

        $temp_fs = $DIC->filesystem()->temp();
        $temp_fs->createDir($tmpdir);

        $subtree = $tree->getChildsByTypeFilter($ref_id, array('fold', 'file'));

        $storage_fs = $DIC->filesystem()->storage();

        foreach ($subtree as $child) {
            if (!$ilAccess->checkAccess('read', '', $child['ref_id'])) {
                continue;
            }
            if (ilObject::_isInTrash($child['ref_id'])) {
                continue;
            }
            if ($child['type'] === 'fold') {
                ilObjFolder::recurseFolder($child["ref_id"], $child["title"], $tmpdir);
            } else {
                // enhance new_filename to full relative path to enable checking for identically named files
                $new_filename = $tmpdir . DIRECTORY_SEPARATOR . ilUtil::getASCIIFilename($child["title"]);
                $sanitizer = new FilenameSanitizerImpl();
                $new_filename = $sanitizer->sanitize($new_filename);
                if ($temp_fs->has($new_filename)) {
                    $new_filename = self::renameDuplicateFile($new_filename);
                }

                // copy to temporal directory
                $relative_path_of_file = LegacyPathHelper::createRelativePath(ilObjFile::_lookupAbsolutePath($child["obj_id"]));
                if ($storage_fs->has($relative_path_of_file)) {
                    $s = $storage_fs->readStream($relative_path_of_file);
                } else {
                    throw new ilFileException('Could not copy ' . $relative_path_of_file . ' to ' . $new_filename);
                }
                $temp_fs->writeStream($new_filename, $s);
            }
        }
    }


    /**
     * bugfix mantis 24309:
     * add "_copy_" followed by a number to the filename (in front of the file-type-extension)
     * if there are identically named files in the same folder to prevent an exception being thrown
     *
     * @param $duplicate_filename string filename including path and extension
     *
     * @return string
     */
    private static function renameDuplicateFile($duplicate_filename)
    {
        // determine the copy_number that will be added to the filename either by obtaining it from
        // the entry of the current file in the duplicate_files-array or use 1 if there is no entry yet
        $copy_number = 1;
        $duplicate_has_array_entry = false;
        foreach (self::$duplicate_files as &$duplicate_file) {
            if ($duplicate_file['file_name'] == $duplicate_filename) {
                $duplicate_has_array_entry = true;
                $copy_number = $duplicate_file['copy_number'];
                // increment the copy_number for correctly renaming the next duplicate of this file
                $duplicate_file['copy_number']++;
            }
        }

        // create an array entry for the duplicate file if there isn't one to ensure that the
        // copy_number can be determined correctly for other duplicates of this file
        if (!$duplicate_has_array_entry) {
            self::$duplicate_files[] = [
                'file_name' => $duplicate_filename,
                'copy_number' => 2 // set as 2 because 1 is already used for this duplicate
            ];
        }

        // rename the file
        $path = pathinfo($duplicate_filename, PATHINFO_DIRNAME);
        $filename = pathinfo($duplicate_filename, PATHINFO_FILENAME);
        $extension = pathinfo($duplicate_filename, PATHINFO_EXTENSION);
        $new_filename = $path . "/" . $filename . " (" . $copy_number . ")." . $extension;

        return $new_filename;
    }


    public function downloadFolder()
    {
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess("read", "", $this->getRefId())) {
            $this->ilErr->raiseError(get_class($this) . "::downloadFolder(): missing read permission!", $this->ilErr->WARNING);
        }
        if (ilObject::_isInTrash($this->getRefId())) {
            $this->ilErr->raiseError(get_class($this) . "::downloadFolder(): object is trashed!", $this->ilErr->WARNING);
        }

        $tmpdir = ilUtil::ilTempnam();
        ilUtil::makeDir($tmpdir);
        $basename = ilUtil::getAsciiFilename($this->getTitle());
        $deliverFilename = $basename . ".zip";
        $zipbasedir = $tmpdir . DIRECTORY_SEPARATOR . $basename;
        $tmpzipfile = $tmpdir . DIRECTORY_SEPARATOR . $deliverFilename;
        
        try {
            ilObjFolder::recurseFolder($this->getRefId(), $this->getTitle(), LegacyPathHelper::createRelativePath($tmpdir));
            ilUtil::zip($zipbasedir, $tmpzipfile);
            rename($tmpzipfile, $zipfile = ilUtil::ilTempnam());
            ilUtil::delDir($tmpdir);
            ilUtil::deliverFile($zipfile, $deliverFilename, '', false, true);
        } catch (ilFileException $e) {
            ilUtil::sendInfo($e->getMessage(), true);
        }
    }
    
    /**
    * Get container view mode
    */
    public function getViewMode()
    {
        $tree = $this->tree;
        
        // default: by type
        $view = ilContainer::VIEW_BY_TYPE;

        // always inherit from
        $container_ref_id = $tree->checkForParentType($this->ref_id, 'grp');
        if (!$container_ref_id) {
            $container_ref_id = $tree->checkForParentType($this->ref_id, 'crs');
        }
        if ($container_ref_id) {
            include_once("./Modules/Course/classes/class.ilObjCourseAccess.php");
            $view_mode = ilObjCourseAccess::_lookupViewMode(ilObject::_lookupObjId($container_ref_id));
            if ($view_mode == ilContainer::VIEW_SESSIONS ||
                $view_mode == ilContainer::VIEW_BY_TYPE ||
                $view_mode == ilContainer::VIEW_SIMPLE) {
                $view = $view_mode;
            }
        }
        
        return $view;
    }

    /**
    * Add additional information to sub item, e.g. used in
    * courses for timings information etc.
    */
    public function addAdditionalSubItemInformation(&$a_item_data)
    {
        include_once './Services/Object/classes/class.ilObjectActivation.php';
        ilObjectActivation::addAdditionalSubItemInformation($a_item_data);
    }
    
    /**
     * Overwritten read method
     *
     * @access public
     * @param
     * @return
     */
    public function read()
    {
        $tree = $this->tree;
        
        parent::read();
        
        // Inherit order type from parent course (if exists)
        include_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
        $this->setOrderType(ilContainerSortingSettings::_lookupSortMode($this->getId()));
    }
} // END class.ilObjFolder
