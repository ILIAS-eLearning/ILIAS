<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Preview/classes/class.ilPreviewRenderer.php");

/**
 * Abstract parent class for all file preview renderer classes.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
abstract class ilFilePreviewRenderer extends ilPreviewRenderer
{
    /**
     * Gets an array containing the repository types (e.g. 'file' or 'crs') that are supported by the renderer.
     *
     * @return array An array containing the supported repository types.
     */
    final public function getSupportedRepositoryTypes()
    {
        return array("file");
    }
    
    /**
     * Determines whether the specified preview object is supported by the renderer.
     *
     * @param ilPreview $preview The preview object to check.
     * @return bool true, if the renderer supports the specified preview object; otherwise, false.
     */
    public function supports($preview)
    {
        // let parent check first
        if (!parent::supports($preview)) {
            return false;
        }
        
        // get file extension
        require_once("./Modules/File/classes/class.ilObjFile.php");
        include_once './Modules/File/classes/class.ilObjFileAccess.php';
        // bugfix mantis 23293
        if (isset($_FILES['file']['name'])) {
            $filename = $_FILES['file']['name'];
        } elseif (isset($_FILES['upload_files']['name'])) {
            $filename = $_FILES['upload_files']['name'];
        } else {
            $filename = ilObjFile::_lookupFileName($preview->getObjId());
        }
        $ext = ilObjFileAccess::_getFileExtension($filename);
        
        // contains that extension?
        return in_array($ext, $this->getSupportedFileFormats());
    }
    
    /**
     * Checks whether the specified file path can be used with exec() commands.
     * If the file name is not conform with exec() commands, a temporary file is
     * created and the path to that file is returned.
     *
     * @param string $filepath The path of the file to check.
     * @return string The specified file path if conform with exec(); otherwise, the path to a temporary copy of the file.
     */
    public function prepareFileForExec($filepath)
    {
        $pos = strrpos($filepath, "/");
        $name = $pos !== false ? substr($filepath, $pos + 1) : $filepath;
        
        // if the file path contains any characters that could cause problems
        // we copy the file to a temporary file
        // $normName = preg_replace("/[^A-Za-z0-9.\- +_&]/", "", $name);
        // if ($normName != $name)
        // {
        // 	$tempPath = ilUtil::ilTempnam();
        // 	if (copy($filepath, $tempPath))
        // 		return $tempPath;
        // }
        //
        return $filepath;
    }
    
    /**
     * Gets an array containing the file formats that are supported by the renderer.
     *
     * @return array An array containing the supported file formats.
     */
    abstract public function getSupportedFileFormats();
}
