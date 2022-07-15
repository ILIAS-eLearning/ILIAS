<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    final public function getSupportedRepositoryTypes() : array
    {
        return ["file"];
    }

    /**
     * Determines whether the specified preview object is supported by the renderer.
     *
     * @param ilPreview $preview The preview object to check.
     * @return bool true, if the renderer supports the specified preview object; otherwise, false.
     */
    public function supports(\ilPreview $preview) : bool
    {
        // let parent check first
        if (!parent::supports($preview)) {
            return false;
        }

        // legacy
        if (null === $preview->getObjId()) {
            // bugfix mantis 23293
            if (isset($_FILES['file']['name'])) {
                $filename = $_FILES['file']['name'];
            } elseif (isset($_FILES['upload_files']['name'])) {
                $filename = $_FILES['upload_files']['name'];
            }
            if (empty($filename)) {
                return false;
            }

            // contains that extension?
            $ext = ilObjFileAccess::_getFileExtension($filename);
            return in_array($ext, $this->getSupportedFileFormats(), true);
        }

        try {
            $obj = new ilObjFile($preview->getObjId(), false);
        } catch (Exception $e) {
            return false;
        }

        if (empty($obj->getResourceId())) {
            return false;
        }

        return in_array($obj->getFileExtension(), $this->getSupportedFileFormats(), true);
    }

    /**
     * Checks whether the specified file path can be used with exec() commands.
     * If the file name is not conform with exec() commands, a temporary file is
     * created and the path to that file is returned.
     *
     * @param string $filepath The path of the file to check.
     * @return string The specified file path if conform with exec(); otherwise, the path to a temporary copy of the file.
     */
    public function prepareFileForExec(string $filepath) : string
    {
        $filepath = ilFileUtils::getValidFilename($filepath);

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
    abstract public function getSupportedFileFormats() : array;
}
