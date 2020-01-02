<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* This class handles all operations on files for the exercise object
*
* @author	Stefan Meyer <meyer@leifos.com>
* @version $Id$Id: class.ilFileDataImportMail.php,v 1.1 2004/03/31 13:42:19 smeyer Exp $
*
*/
require_once("./Services/FileSystem/classes/class.ilFileDataImport.php");
                
class ilFileDataImportMail extends ilFileDataImport
{
    /**
    * path of exercise directory
    * @var string path
    * @access private
    */
    public $mail_path;

    public $files;
    public $xml_file;
    

    /**
    * Constructor
    * call base constructors
    * checks if directory is writable and sets the optional obj_id
    * @param integereger obj_id
    * @access	public
    */
    public function __construct()
    {
        define('MAIL_IMPORT_PATH', 'mail');
        parent::__construct();
        $this->mail_path = parent::getPath() . "/" . MAIL_IMPORT_PATH;

        // IF DIRECTORY ISN'T CREATED CREATE IT
        // CALL STTIC TO AVOID OVERWRITE PROBLEMS
        ilFileDataImportMail::_initDirectory();
        $this->__readFiles();
    }

    public function getFiles()
    {
        return $this->files ? $this->files : array();
    }

    public function getXMLFile()
    {
        return $this->xml_file;
    }

    /**
    * store uploaded file in filesystem
    * @param array HTTP_POST_FILES
    * @access	public
    * @return bool
    */
    public function storeUploadedFile($a_http_post_file)
    {
        // TODO:
        // CHECK UPLOAD LIMIT
        //

        if (isset($a_http_post_file) && $a_http_post_file['size']) {
            // DELETE OLD FILES
            $this->unlinkLast();

            // CHECK IF FILE WITH SAME NAME EXISTS
            ilUtil::moveUploadedFile(
                $a_http_post_file['tmp_name'],
                $a_http_post_file['name'],
                $this->getPath() . '/' . $a_http_post_file['name']
            );
            //move_uploaded_file($a_http_post_file['tmp_name'],$this->getPath().'/'.$a_http_post_file['name']);

            // UPDATE FILES LIST
            $this->__readFiles();
            return true;
        } else {
            return false;
        }
    }
    public function findXMLFile($a_dir = '')
    {
        $a_dir = $a_dir ? $a_dir : $this->getPath();

        $this->__readFiles($a_dir);

        foreach ($this->getFiles() as $file_data) {
            if (is_dir($file_data["abs_path"])) {
                $this->findXMLFile($file_data["abs_path"]);
            }
            if (($tmp = explode(".", $file_data["name"])) !== false) {
                if ($tmp[count($tmp) - 1] == "xml") {
                    return $this->xml_file = $file_data["abs_path"];
                }
            }
        }
        return $this->xml_file;
    }

    public function unzip()
    {
        foreach ($this->getFiles() as $file_data) {
            ilUtil::unzip($file_data["abs_path"]);
            
            return true;
        }
        return false;
    }

    /**
    * get exercise path
    * @access	public
    * @return string path
    */
    public function getPath()
    {
        return $this->mail_path;
    }

    public function unlinkLast()
    {
        foreach ($this->getFiles() as $file_data) {
            if (is_dir($file_data["abs_path"])) {
                ilUtil::delDir($file_data["abs_path"]);
            } else {
                unlink($file_data["abs_path"]);
            }
        }
        return true;
    }
    // PRIVATE METHODS
    public function __readFiles($a_dir = '')
    {
        $a_dir = $a_dir ? $a_dir : $this->getPath();

        $this->files = array();
        $dp = opendir($a_dir);

        while ($file = readdir($dp)) {
            if ($file == "." or $file == "..") {
                continue;
            }
            $this->files[] = array(
                'name'			=> $file,
                'abs_path'		=> $a_dir . "/" . $file,
                'size'			=> filesize($a_dir . "/" . $file),
                'ctime'			=> filectime($a_dir . '/' . $file)
            );
        }
        closedir($dp);

        return true;
    }

    /**
    * check if directory is writable
    * overwritten method from base class
    * @access	private
    * @return bool
    */
    public function __checkReadWrite()
    {
        if (is_writable($this->mail_path) && is_readable($this->mail_path)) {
            return true;
        } else {
            $this->ilias->raiseError("Mail import directory is not readable/writable by webserver", $this->ilias->error_obj->FATAL);
        }
    }
    /**
    * init directory
    * overwritten method
    * @access	public
    * @static
    * @return boolean
    */
    public function _initDirectory()
    {
        if (!@file_exists($this->mail_path)) {
            ilUtil::makeDir($this->mail_path);
        }
        return true;
    }
}
