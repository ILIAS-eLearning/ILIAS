<?php
use Sabre\DAV\Exception;
use Sabre\DAV\Exception\Forbidden;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Consumer\Consumers;

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
    protected Manager $resource_manager;
    protected Consumers $resource_consumer;

    /**
     * We need to keep track of versioning.
     *
     * @var $versioning_enabled boolean
     */
    protected bool $versioning_enabled;

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
        global $DIC;
        $settings = new ilSetting('webdav');
        $this->resource_manager = $DIC->resourceStorage()->manage();
        $this->resource_consumer = $DIC->resourceStorage()->consume();
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
            if ($this->getSize() > 0 &&
                ($r_id = $this->obj->getResourceId()) &&
                ($identification = $this->resource_manager->find($r_id))) {
                return $this->resource_consumer->stream($identification)->getStream()->getContents();
            }
            
            throw new Exception\NotFound("File not found");
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
        if ($this->getSize() > 0) {
            return '"' . sha1(
                $this->getSize() .
                $this->getName() .
                $this->obj->getCreateDate()
            ) . '"';
        }

        return null;
    }

    /**
     * Returns the size of the node, in bytes
     *
     * @return int
     */
    public function getSize() : int
    {
        return $this->obj->getFileSize();
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
        $path = ilUtil::ilTempnam();
        $path_with_file = $path . '/' . $this->obj->getFileName();
        
        mkdir($path);
        file_put_contents($path_with_file, $a_data);

        $upload = fopen($path_with_file, 'read');
        
        $stream = Streams::ofResource($upload);
        if ($a_file_action === 'replace') {
            $this->obj->replaceWithStream($stream, $this->obj->getTitle());
        } else {
            $this->obj->appendStream($stream, $this->obj->getTitle());
        }
        
        unlink($path_with_file);
        rmdir($path);

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
