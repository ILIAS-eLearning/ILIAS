<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Stores all mediacast relevant settings.
*
* @author Roland Küstermann <rkuestermann@mps.de>
* @version $Id$
*
*
* @ingroup ModulesMediaCast
*/
class ilMediaCastSettings
{
    private $supported_suffixes = ["mp4", "mp3", "jpg", "jpeg", "png", "gif", "svg"];
    private $supported_mime_types = [
        "video/mp4" => "video/mp4",
        "audio/mpeg" => "audio/mpeg",
        "image/jpeg" => "image/jpeg",
        "image/png" => "image/png",
        "image/gif" => "image/gif",
        "image/svg+xml" => "image/svg+xml"
    ];


    private static $instance = null;
    private $defaultAccess = "users";
    private $purposeSuffixes = array();
    private $mimeTypes = array();

    /**
     * singleton contructor
     *
     * @access private
     *
     */
    private function __construct()
    {
        $this->initStorage();
        $this->read();
    }
    
    /**
     * get singleton instance
     *
     * @access public
     * @static
     *
     */
    public static function _getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilMediaCastSettings();
    }

    /**
     * set filetypes for purposes
     *
     * @access public
     *
     */
    public function setPurposeSuffixes($purpose_filetypes)
    {
        $this->purposeSuffixes = $purpose_filetypes;
    }

    /**
     * get filetypes for purposes
     *
     * @access public
     *
     */
    public function getPurposeSuffixes()
    {
        return $this->purposeSuffixes;
    }

    public function getDefaultAccess()
    {
        return $this->defaultAccess;
    }
    
    public function setDefaultAccess($value)
    {
        $this->defaultAccess = $value == "users" ? "users" : "public";
    }
    
    /**
     * @return array of mimetypes
     */
    public function getMimeTypes()
    {
        return $this->mimeTypes;
    }
    
    /**
     * @param unknown_type $mimeTypes
     */
    public function setMimeTypes(array $mimeTypes)
    {
        $this->mimeTypes = $mimeTypes;
    }

    
    /**
     * save
     *
     * @access public
     */
    public function save()
    {
        foreach ($this->purposeSuffixes as $purpose => $filetypes) {
            $this->storage->set($purpose . "_types", implode(",", $filetypes));
        }
        $this->storage->set("defaultaccess", $this->defaultAccess);
        $this->storage->set("mimetypes", implode(",", $this->getMimeTypes()));
    }

    /**
     * Read settings
     *
     * @access private
     * @param
     *
     */
    private function read()
    {
        foreach ($this->purposeSuffixes as $purpose => $filetypes) {
            if ($this->storage->get($purpose . "_types") != false) {
                $sf = explode(",", $this->storage->get($purpose . "_types"));
                $sf = array_filter($sf, function ($c) {
                    return in_array($c, $this->supported_suffixes);
                });
                $this->purposeSuffixes[$purpose] = $sf;
            }
        }
        $this->setDefaultAccess($this->storage->get("defaultaccess"));
        if ($this->storage->get("mimetypes")) {
            $mt = explode(",", $this->storage->get("mimetypes"));
            $mt = array_filter($mt, function ($c) {
                return in_array($c, $this->supported_mime_types);
            });

            $this->setMimeTypes($mt);
        }
    }
    
    /**
     * Init storage class (ilSetting)
     * @access private
     *
     */
    private function initStorage()
    {
        include_once('./Services/Administration/classes/class.ilSetting.php');
        $this->storage = new ilSetting('mcst');
        include_once('./Modules/MediaCast/classes/class.ilObjMediaCast.php');
        $this->purposeSuffixes = array_flip(ilObjMediaCast::$purposes);
               
        $this->purposeSuffixes["Standard"] = $this->supported_suffixes;
        //$this->purposeSuffixes["AudioPortable"] = array("mp3");
        //$this->purposeSuffixes["VideoPortable"] = array("mp4","mov");
        $this->setDefaultAccess("users");
        include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
        $mimeTypes = array_unique(array_values(ilMimeTypeUtil::getExt2MimeMap()));
        sort($mimeTypes);
        $this->setMimeTypes($this->supported_mime_types);
    }
}
