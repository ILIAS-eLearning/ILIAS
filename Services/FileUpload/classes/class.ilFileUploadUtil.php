<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilFileUploadUtil
 *
 * This class provides utility methods for file uploads.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @package ServicesFileUpload
 */
class ilFileUploadUtil
{
    /**
     * Determines whether files can be uploaded to the object with the specified reference id.
     *
     * @param int $a_ref_id The reference id to check.
     * @param string $a_type The type of the object to check.
     * @return bool true, if file upload is allowed; otherwise, false.
     */
    public static function isUploadAllowed($a_ref_id, $a_type = "")
    {
        global $DIC;
        $objDefinition = $DIC['objDefinition'];
        $ilAccess = $DIC['ilAccess'];
        
        if (self::isUploadSupported()) {
            include_once("./Services/FileUpload/classes/class.ilFileUploadSettings.php");
            
            // repository upload enabled?
            if (ilFileUploadSettings::isDragAndDropUploadEnabled() &&
                ilFileUploadSettings::isRepositoryDragAndDropUploadEnabled()) {
                if ($a_type == "") {
                    $a_type = ilObject::_lookupType($a_ref_id, true);
                }
                
                if ($objDefinition->isContainer($a_type)) {
                    return $ilAccess->checkAccess("create_file", "", $a_ref_id, "file");
                }
            }
        }
        
        return false;
    }
    
    /**
     * Determines whether file upload is supported at the current location.
     *
     * @return bool true, if file upload is supported; otherwise, false.
     */
    public static function isUploadSupported()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        // right now, only the repository is supported
        if (strtolower($_GET["baseClass"]) == "ilrepositorygui") {
            $cmd = $ilCtrl->getCmd();
            if ($cmd == "" || $cmd == "view" || $cmd == "render") {
                return true;
            }
        }
            
        return false;
    }
    
    /**
     * Gets the maximum upload file size allowed in bytes.
     *
     * @return int The maximum upload file size allowed in bytes.
     */
    public static function getMaxFileSize()
    {
        // get the value for the maximal uploadable filesize from the php.ini (if available)
        $umf = ini_get("upload_max_filesize");
        // get the value for the maximal post data from the php.ini (if available)
        $pms = ini_get("post_max_size");
        
        //convert from short-string representation to "real" bytes
        $multiplier_a = array("K" => 1024, "M" => 1024 * 1024, "G" => 1024 * 1024 * 1024);
        
        $umf_parts = preg_split("/(\d+)([K|G|M])/", $umf, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $pms_parts = preg_split("/(\d+)([K|G|M])/", $pms, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        if (count($umf_parts) == 2) {
            $umf = $umf_parts[0] * $multiplier_a[$umf_parts[1]];
        }
        if (count($pms_parts) == 2) {
            $pms = $pms_parts[0] * $multiplier_a[$pms_parts[1]];
        }
        
        // use the smaller one as limit
        $max_filesize = min($umf, $pms);
        
        if (!$max_filesize) {
            $max_filesize = max($umf, $pms);
        }
        
        return $max_filesize;
    }
    
    /**
     * Gets the maximum upload file size allowed as string (like '14.2 MB').
     *
     * @return string The maximum upload file size allowed as string.
     */
    public static function getMaxFileSizeString()
    {
        $max_filesize = self::getMaxFileSize();
        
        // format for display in mega-bytes
        return sprintf("%.1f MB", $max_filesize / 1024 / 1024);
    }
}
