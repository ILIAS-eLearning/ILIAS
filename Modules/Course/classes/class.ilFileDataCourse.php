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
* This class handles all operations of archive files for the course object
*
* @author	Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/
require_once("./Services/FileSystem/classes/class.ilFileData.php");
                
class ilFileDataCourse extends ilFileData
{
    /**
    * path of exercise directory
    * @var string path
    * @access private
    */
    public $course_path;

    private $course_id;

    /**
     * Constructor
     * call base constructors
     * checks if directory is writable and sets the optional obj_id
     * @param int obj_id
     * @access	public
     */
    public function __construct($a_course_id)
    {
        define('COURSE_PATH', 'course');
        parent::__construct();
        $this->course_path = parent::getPath() . "/" . COURSE_PATH;
        $this->course_id = $a_course_id;
    
        // IF DIRECTORY ISN'T CREATED CREATE IT
        if (!$this->__checkPath()) {
            $this->__initDirectory();
        }
        // Check import dir
        $this->__checkImportPath();
    }
    
    public function getArchiveFile($a_rel_name)
    {
        if (@file_exists($this->course_path . '/' . $a_rel_name . '.zip')) {
            return $this->course_path . '/' . $a_rel_name . '.zip';
        }
        if (@file_exists($this->course_path . '/' . $a_rel_name . '.pdf')) {
            return $this->course_path . '/' . $a_rel_name . '.pdf';
        }
        return false;
    }
    
    /**
     * Get all member export files
     *
     * @access public
     *
     */
    public function getMemberExportFiles()
    {
        $files = array();
        $dp = opendir($this->course_path);

        while ($file = readdir($dp)) {
            if (is_dir($file)) {
                continue;
            }
            
            if (preg_match("/^([0-9]{10})_[a-zA-Z]*_export_([a-z]+)_([0-9]+)\.[a-z]+$/", $file, $matches) and $matches[3] == $this->course_id) {
                $timest = $matches[1];
                $file_info['name'] = $matches[0];
                $file_info['timest'] = $matches[1];
                $file_info['type'] = $matches[2];
                $file_info['id'] = $matches[3];
                $file_info['size'] = filesize($this->course_path . '/' . $file);
                
                $files[$timest] = $file_info;
            }
        }
        closedir($dp);
        return $files ? $files : array();
    }
    
    public function deleteMemberExportFile($a_name)
    {
        $file_name = $this->course_path . '/' . $a_name;
        if (@file_exists($file_name)) {
            @unlink($file_name);
        }
    }

    public function getMemberExportFile($a_name)
    {
        $file_name = $this->course_path . '/' . $a_name;
        if (@file_exists($file_name)) {
            return file_get_contents($file_name);
        }
    }


    public function deleteArchive($a_rel_name)
    {
        $this->deleteZipFile($this->course_path . '/' . $a_rel_name . '.zip');
        $this->deleteDirectory($this->course_path . '/' . $a_rel_name);
        $this->deleteDirectory(CLIENT_WEB_DIR . '/courses/' . $a_rel_name);
        $this->deletePdf($this->course_path . '/' . $a_rel_name . '.pdf');

        return true;
    }
    public function deleteZipFile($a_abs_name)
    {
        if (@file_exists($a_abs_name)) {
            @unlink($a_abs_name);

            return true;
        }
        return false;
    }
    public function deleteDirectory($a_abs_name)
    {
        if (file_exists($a_abs_name)) {
            ilUtil::delDir($a_abs_name);
            
            return true;
        }
        return false;
    }
    public function deletePdf($a_abs_name)
    {
        if (@file_exists($a_abs_name)) {
            @unlink($a_abs_name);

            return true;
        }
        return false;
    }

    public function copy($a_from, $a_to)
    {
        if (@file_exists($a_from)) {
            @copy($a_from, $this->getCoursePath() . '/' . $a_to);

            return true;
        }
        return false;
    }

    public function rCopy($a_from, $a_to)
    {
        ilUtil::rCopy($a_from, $this->getCoursePath() . '/' . $a_to);

        return true;
    }


    public function addDirectory($a_rel_name)
    {
        ilUtil::makeDir($this->getCoursePath() . '/' . $a_rel_name);

        return true;
    }

    public function writeToFile($a_data, $a_rel_name)
    {
        if (!$fp = @fopen($this->getCoursePath() . '/' . $a_rel_name, 'w+')) {
            die("Cannot open file: " . $this->getCoursePath() . '/' . $a_rel_name);
        }
        @fwrite($fp, $a_data);

        return true;
    }

    public function zipFile($a_rel_name, $a_zip_name)
    {
        ilUtil::zip($this->getCoursePath() . '/' . $a_rel_name, $this->getCoursePath() . '/' . $a_zip_name);

        // RETURN filesize
        return filesize($this->getCoursePath() . '/' . $a_zip_name);
    }


    /**
    * get exercise path
    * @access	public
    * @return string path
    */
    public function getCoursePath()
    {
        return $this->course_path;
    }

    public function createOnlineVersion($a_rel_name)
    {
        ilUtil::makeDir(CLIENT_WEB_DIR . '/courses/' . $a_rel_name);
        ilUtil::rCopy($this->getCoursePath() . '/' . $a_rel_name, CLIENT_WEB_DIR . '/courses/' . $a_rel_name);

        return true;
    }

    public function getOnlineLink($a_rel_name)
    {
        return ilUtil::getWebspaceDir('filesystem') . '/courses/' . $a_rel_name . '/index.html';
    }


    // METHODS FOR XML IMPORT OF COURSE
    public function createImportFile($a_tmp_name, $a_name)
    {
        ilUtil::makeDir($this->getCoursePath() . '/import/crs_' . $this->course_id);

        ilUtil::moveUploadedFile(
            $a_tmp_name,
            $a_name,
            $this->getCoursePath() . '/import/crs_' . $this->course_id . '/' . $a_name
        );
        $this->import_file_info = pathinfo($this->getCoursePath() . '/import/crs_' . $this->course_id . '/' . $a_name);
    }

    public function unpackImportFile()
    {
        return ilUtil::unzip($this->getCoursePath() . '/import/crs_' . $this->course_id . '/' . $this->import_file_info['basename']);
    }

    public function validateImportFile()
    {
        if (!is_dir($this->getCoursePath() . '/import/crs_' . $this->course_id) . '/' .
           basename($this->import_file_info['basename'], '.zip')) {
            return false;
        }
        if (!file_exists($this->getCoursePath() . '/import/crs_' . $this->course_id
                        . '/' . basename($this->import_file_info['basename'], '.zip')
                        . '/' . basename($this->import_file_info['basename'], '.zip') . '.xml')) {
            return false;
        }
    }

    public function getImportFile()
    {
        return $this->getCoursePath() . '/import/crs_' . $this->course_id
            . '/' . basename($this->import_file_info['basename'], '.zip')
            . '/' . basename($this->import_file_info['basename'], '.zip') . '.xml';
    }
    



    // PRIVATE METHODS
    public function __checkPath()
    {
        if (!@file_exists($this->getCoursePath())) {
            return false;
        }
        if (!@file_exists(CLIENT_WEB_DIR . '/courses')) {
            ilUtil::makeDir(CLIENT_WEB_DIR . '/courses');
        }

            
        $this->__checkReadWrite();

        return true;
    }
    
    public function __checkImportPath()
    {
        if (!@file_exists($this->getCoursePath() . '/import')) {
            ilUtil::makeDir($this->getCoursePath() . '/import');
        }

        if (!is_writable($this->getCoursePath() . '/import') or !is_readable($this->getCoursePath() . '/import')) {
            $this->ilias->raiseError("Course import path is not readable/writable by webserver", $this->ilias->error_obj->FATAL);
        }
    }

    /**
    * check if directory is writable
    * overwritten method from base class
    * @access	private
    * @return bool
    */
    public function __checkReadWrite()
    {
        if (is_writable($this->course_path) && is_readable($this->course_path)) {
            return true;
        } else {
            $this->ilias->raiseError("Course directory is not readable/writable by webserver", $this->ilias->error_obj->FATAL);
        }
    }
    /**
    * init directory
    * overwritten method
    * @access	public
    * @return string path
    */
    public function __initDirectory()
    {
        if (is_writable($this->getPath())) {
            ilUtil::makeDir($this->getPath() . '/' . COURSE_PATH);
            $this->course_path = $this->getPath() . '/' . COURSE_PATH;
            
            return true;
        }
        return false;
    }
}
