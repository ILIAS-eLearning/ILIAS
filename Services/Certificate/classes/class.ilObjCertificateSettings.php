<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjCertificateSettings
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ServicesCertificate
*/
class ilObjCertificateSettings extends ilObject
{
    
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_reference = true)
    {
        parent::__construct($a_id, $a_reference);
        $this->type = "cert";
    }

    public function hasBackgroundImage()
    {
        if (@file_exists($this->getBackgroundImagePath()) && (@filesize($this->getBackgroundImagePath()) > 0)) {
            return true;
        } else {
            return false;
        }
    }

    public function getBackgroundImageDefaultFolder()
    {
        return CLIENT_WEB_DIR . "/certificates/default/";
    }

    /**
    * Returns the filesystem path of the background image
    *
    * @return string The filesystem path of the background image
    */
    public function getBackgroundImagePath()
    {
        return $this->getBackgroundImageDefaultFolder() . $this->getBackgroundImageName();
    }

    /**
    * Returns the filename of the background image
    *
    * @return string The filename of the background image
    */
    public function getBackgroundImageName()
    {
        return "background.jpg";
    }

    /**
    * Returns the filesystem path of the background image thumbnail
    *
    * @return string The filesystem path of the background image thumbnail
    */
    public function getBackgroundImageThumbPath()
    {
        return $this->getBackgroundImageDefaultFolder() . $this->getBackgroundImageName() . ".thumb.jpg";
    }

    /**
    * Returns the filesystem path of the background image temp file during upload
    *
    * @return string The filesystem path of the background image temp file
    */
    public function getBackgroundImageTempfilePath()
    {
        return $this->getBackgroundImageDefaultFolder() . "background_upload.tmp";
    }

    /**
    * Returns the web path of the background image
    *
    * @return string The web path of the background image
    */
    public function getBackgroundImagePathWeb()
    {
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $this->getBackgroundImagePath());
    }
    
    /**
    * Returns the web path of the background image thumbnail
    *
    * @return string The web path of the background image thumbnail
    */
    public function getBackgroundImageThumbPathWeb()
    {
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $this->getBackgroundImageThumbPath());
    }

    /**
    * Uploads a background image for the certificate. Creates a new directory for the
    * certificate if needed. Removes an existing certificate image if necessary
    *
    * @param string $image_tempfilename Name of the temporary uploaded image file
    * @return integer An errorcode if the image upload fails, 0 otherwise
    */
    public function uploadBackgroundImage($image_tempfilename)
    {
        if (!empty($image_tempfilename)) {
            $convert_filename = $this->getBackgroundImageName();
            $imagepath = $this->getBackgroundImageDefaultFolder();
            if (!file_exists($imagepath)) {
                ilUtil::makeDirParents($imagepath);
            }
            // upload the file
            if (!ilUtil::moveUploadedFile(
                $image_tempfilename,
                basename($this->getBackgroundImageTempfilePath()),
                $this->getBackgroundImageTempfilePath()
            )) {
                return false;
            }
            // convert the uploaded file to JPEG
            ilUtil::convertImage($this->getBackgroundImageTempfilePath(), $this->getBackgroundImagePath(), "JPEG");
            ilUtil::convertImage($this->getBackgroundImageTempfilePath(), $this->getBackgroundImageThumbPath(), "JPEG", 100);
            if (!file_exists($this->getBackgroundImagePath())) {
                // something went wrong converting the file. use the original file and hope, that PDF can work with it
                if (!ilUtil::moveUploadedFile($this->getBackgroundImageTempfilePath(), $convert_filename, $this->getBackgroundImagePath())) {
                    return false;
                }
            }
            unlink($this->getBackgroundImageTempfilePath());
            if (file_exists($this->getBackgroundImagePath()) && (filesize($this->getBackgroundImagePath()) > 0)) {
                return true;
            }
        }
        return false;
    }

    /**
    * Deletes the background image of a certificate
    *
    * @return boolean TRUE if the process succeeds
    */
    public function deleteBackgroundImage()
    {
        $result = true;
        if (file_exists($this->getBackgroundImageThumbPath())) {
            $result = $result & unlink($this->getBackgroundImageThumbPath());
        }
        if (file_exists($this->getBackgroundImagePath())) {
            $result = $result & unlink($this->getBackgroundImagePath());
        }
        if (file_exists($this->getBackgroundImageTempfilePath())) {
            $result = $result & unlink($this->getBackgroundImageTempfilePath());
        }
        return $result;
    }
} // END class.ilObjCertificateSettings
