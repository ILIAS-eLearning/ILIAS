<?php
use Sabre\DAV\Exception;
use Sabre\DAV\Exception\Forbidden;
use ILIAS\DI\Container;
use ILIAS\Filesystem\Stream\Streams;

/**
 * Class ilObjFileDAV
 *
 * Implementation for ILIAS File Objects represented as WebDAV File Objects
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 * @extends ilObjectDAV
 * @implements Sabre\DAV\IFile
 */
class ilObjFileDAV extends ilObjectDAV implements Sabre\DAV\IFile
{
    /**
     * Application layer object.
     *
     * @var $obj ilObjFile
     */
    protected $obj;

    /**
     * We need to keep track of versioning.
     *
     * @var $versioning_enabled boolean
     */
    protected $versioning_enabled;

    /**
     * ilObjFileDAV represents the WebDAV-Interface to an ILIAS-Object
     *
     * So an ILIAS is needed in the constructor. Otherwise this object would
     * be useless.
     *
     * @param ilObjFile $a_obj
     * @param ilWebDAVRepositoryHelper $repo_helper
     * @param ilWebDAVObjDAVHelper $dav_helper
     */
    public function __construct(ilObjFile $a_obj, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        $settings = new ilSetting('webdav');
        $this->versioning_enabled = (bool) $settings->get('webdav_versioning_enabled', true);
        parent::__construct($a_obj, $repo_helper, $dav_helper);
    }

    /**
     * Replaces the contents of the file.
     *
     * The data argument is a readable stream resource.
     *
     * After a successful put operation, you may choose to return an ETag. The
     * etag must always be surrounded by double-quotes. These quotes must
     * appear in the actual string you're returning.
     *
     * Clients may use the ETag from a PUT request to later on make sure that
     * when they update the file, the contents haven't changed in the mean
     * time.
     *
     * If you don't plan to store the file byte-by-byte, and you return a
     * different object on a subsequent GET you are strongly recommended to not
     * return an ETag, and just return null.
     *
     * @param resource|string $data
     * @return string|null
     * @throws Forbidden
     */
    public function put($data)
    {
        if ($this->repo_helper->checkAccess('write', $this->getRefId())) {
            $this->setObjValuesForNewFileVersion();
            if ($this->versioning_enabled === true) {
                // Stolen from ilObjFile->addFileVersion
                $this->handleFileUpload($data, 'new_version');
            } else {
                $this->handleFileUpload($data, 'replace');
            }


            return $this->getETag();
        }
        throw new Exception\Forbidden("Permission denied. No write access for this file");
    }

    /**
     * Returns the data
     *
     * This method may either return a string or a readable stream resource
     *
     * @return mixed
     * @throws Forbidden
     */
    public function get()
    {
        if ($this->repo_helper->checkAccess("read", $this->obj->getRefId())) {
            $file = $this->getPathToFile();

            if (file_exists($file)) {
                return fopen($file, 'r');
            } else {
                throw new Exception\NotFound("File not found");
            }
        }

        throw new Exception\Forbidden("Permission denied. No read access for this file");
    }

    /**
     * Returns title of file object. If it has a forbidden file extension -> ".sec" will be added
     *
     * @return string
     */
    public function getName()
    {
        return ilFileUtils::getValidFilename($this->obj->getTitle());
    }

    /**
     * Returns the mime-type for a file
     *
     * If null is returned, we'll assume application/octet-stream
     *
     * @return string|null
     */
    public function getContentType()
    {
        return  $this->obj->getFileType();
    }

    /**
     * Returns the ETag for a file
     *
     * An ETag is a unique identifier representing the current version of the file. If the file changes, the ETag MUST change.
     *
     * Return null if the ETag can not effectively be determined.
     *
     * The ETag must be surrounded by double-quotes, so something like this
     * would make a valid ETag:
     *
     *   return '"someetag"';
     *
     * @return string|null
     */
    public function getETag()
    {
        if (file_exists($path = $this->getPathToFile())) {
            return '"' . sha1(
                fileinode($path) .
                filesize($path) .
                filemtime($path)
            ) . '"';
        }

        return null;
    }

    /**
     * Returns the size of the node, in bytes
     *
     * @return int
     */
    public function getSize()
    {
        if (file_exists($this->getPathToFile())) {
            return $this->obj->getFileSize();
        }
        return 0;
    }

    /**
     * @param string $a_name
     * @throws Forbidden
     */
    public function setName($a_name)
    {
        if ($this->dav_helper->isValidFileNameWithValidFileExtension($a_name)) {
            parent::setName($a_name);
        } else {
            throw new Exception\Forbidden("Invalid file extension");
        }
    }

    /**
     * Handle uploaded file. Either it is a new file upload to a directory or it is an
     * upload to replace an existing file.
     *
     * Given data can be a resource or data (given from the sabreDAV library)
     *
     * @param string | resource $a_data
     * @throws Forbidden
     */
    public function handleFileUpload($a_data, $a_file_action)
    {
        // Set name for uploaded file because due to the versioning, the title can change for different versions. This setter-call here
        // ensures that the uploaded file is saved with the title of the object. This obj->setFileName() has to be called
        // before $this->getPathToFile(). Otherwise, the file could be saved under the wrong filename.
        if ($a_file_action === 'replace') {
            $this->obj->deleteVersions();
            $this->obj->clearDataDirectory();
        }
        $stream = Streams::ofResource($a_data);
        $this->obj->appendStream($stream, $this->obj->getTitle());

        // TODO filename is "input" and metadata etc.

        if ($this->obj->update()) {
            $this->createHistoryAndNotificationForObjUpdate($a_file_action);
            ilPreview::createPreview($this->obj, true);
        }
    }


    protected function getPathToDirectory()
    {
        return $this->obj->getDirectory($this->obj->getVersion());
    }

    /**
     * This method is called in 2 use cases:
     *
     * Use case 1: Get the path to an already existing file to download it -> read operation
     * Use case 2: Get the path to save a new file into or overwrite an existing one -> write operation
     *
     * @throws ilFileUtilsException
     * @return string
     */
    protected function getPathToFile()
    {
        // ilObjFile delivers the filename like it was on the upload. But if the file-extension is forbidden, the file
        // will be safed as .sec-file. In this case ->getFileName returns the wrong file name
        $path = $this->getPathToDirectory() . "/" . $this->obj->getFileName();

        // For the case of forbidden file-extensions, ::getValidFilename($path) returns the path with the .sec extension
        return ilFileUtils::getValidFilename($path);
    }

    protected function checkForVirus(string $file_dest_path)
    {
        $vrs = ilUtil::virusHandling($file_dest_path, '', true);
        // If vrs[0] == false -> virus found
        if ($vrs[0] == false) {
            ilLoggerFactory::getLogger('WebDAV')->error(get_class($this) . ' ' . $this->obj->getTitle() . " -> virus found on '$file_dest_path'!");
            $this->deleteObjOrVersion();
            throw new Exception\Forbidden('Virus found!');
        }
    }

    /**
     * Set object values for a new file version
     */
    protected function setObjValuesForNewFileVersion()
    {
        // This is necessary for windows explorer. Because windows explorer makes always 2 PUT requests. One with a 0 Byte
        // file to test, if the user has write permissions and the second one to upload the original file.
        if ($this->obj->getFileSize() > 0) {
            // Stolen from ilObjFile->addFileVersion
//            $this->obj->setVersion($this->obj->getMaxVersion() + 1);
//            $this->obj->setMaxVersion($this->obj->getMaxVersion() + 1);
        }
    }

    /**
     * Create history entry and a news notification for file object update
     *
     * @param $a_action
     */
    protected function createHistoryAndNotificationForObjUpdate($a_action)
    {
        // Add history entry and notification for new file version (stolen from ilObjFile->addFileVersion)
        switch ($a_action) {
            case "new_version":
            case "replace":
                ilHistory::_createEntry($this->obj->getId(), $a_action, $this->obj->getTitle() . "," . $this->obj->getVersion() . "," . $this->obj->getMaxVersion());
                break;
        }

        $this->obj->addNewsNotification("file_updated");
    }

    /**
     * Delete an object if there is no other version in it otherwise delete version.
     */
    protected function deleteObjOrVersion()
    {
        if ($this->obj->getVersion() > 1) {
            $version_dir = $this->obj->getDirectory($this->obj->getVersion());
            ilUtil::delDir($version_dir);
        } else {
            $this->obj->deleteVersions();
            $this->obj->delete();
        }
    }
}
