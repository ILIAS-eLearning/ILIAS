<?php

use ILIAS\UI\NotImplementedException;
use Sabre\DAV\Exception;

require_once 'Modules/File/classes/class.ilObjFile.php';

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
     * @throws BadRequest
     * @throws Forbidden
     */
    function put($data)
    {        
        if($this->repo_helper->checkAccess('write', $this->getRefId()))
        {
            $this->handleFileUpload($data);
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
    function get()
    {
        if($this->repo_helper->checkAccess("read", $this->obj->getRefId()))
        {
            $file = $this->getPathToFile();
            if(file_exists($file))
            {
                return fopen($file,'r');
            }
            else 
            {
                throw new Exception\NotFound("File not found");
            }
        }

        throw new Exception\Forbidden("Permission denied. No read access for this file");
    }
    
    /**
     * Returns the mime-type for a file
     *
     * If null is returned, we'll assume application/octet-stream
     *
     * @return string|null
     */
    function getContentType()
    {
        return  $this->obj->guessFileType();
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
        if(file_exists($this->getPathToFile()))
        {
            // This is not a password hash. So I think md5 should do just fine :)
            return '"' . hash_file("md5", $this->getPathToFile(), false) . '"';
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
        if(file_exists($this->getPathToFile()))
        {
            return $this->obj->getFileSize();
        }
        return 0;
    }

    /**
     * @param string $a_name
     * @throws Forbidden
     */
    function setName($a_name)
    {
        if($this->repo_helper->isValidFileNameWithValidFileExtension($a_name))
        {
            parent::setName($a_name);
        }
        else
        {
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
     * @throws BadRequest
     * @throws Forbidden
     */
    public function handleFileUpload($a_data)
    {
        $file_dest_path = $this->getPathToFile();

        // If dir does not exist yet -> create it
        if(!file_exists($file_dest_path))
        {
            ilUtil::makeDirParents($this->getPathToDirectory());
        }
        
        // File upload
        if(is_resource($a_data))
        {
            $written_length = $this->fileUploadWithStream($a_data, $file_dest_path);
        }
        else if(is_string($a_data))
        {
            $written_length = $this->fileUploadWithString($a_data, $file_dest_path);
        }
        else 
        {
            ilLoggerFactory::getLogger('WebDAV')->warning(get_class($this). ' ' . $this->obj->getTitle() ." -> invalid upload data sent");
            throw new Exception\BadRequest('Invalid put data sent');
        }
        
        // Security checks
        $this->checkForVirus($file_dest_path);

        // Set last meta data
        include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
        $this->obj->setFileType(ilMimeTypeUtil::lookupMimeType($file_dest_path));
        $this->obj->setFileSize($written_length);
        $this->obj->update();
    }

    /**
     * Write given data (as Resource) to the given file
     *
     * @param Resource $a_data
     * @param string $file_dest_path
     * @throws Exception
     * @return number
     */
    protected function fileUploadWithStream($a_data, string $file_dest_path)
    {
        try {
            $written_length = 0;
            $write_stream = fopen($file_dest_path,'w');
            while (!feof($a_data))
            {
                if (false === ($written = fwrite($write_stream, fread($a_data, 4096))))
                {
                    fclose($write_stream);
                    throw new Exception\Forbidden('Forbidden to write file');
                }
                $written_length += $written;
            }
            
        } catch(Exception $e) {
            ilLoggerFactory::getLogger('WebDAV')->error("Error on uploading {$this->obj->getTitle()} to path $file_dest_path with message: " . $e->getMessage());
            throw new Exception();
        } finally {
            fclose($write_stream);
        }
        
        return $written_length;
    }
    
    /**
     * Write given data (as string) to the given file
     * 
     * @param string $a_data
     * @param string $file_dest_path
     * @throws Forbidden
     * @return number $written_length
     */
    protected function fileUploadWithString(string $a_data, string $file_dest_path)
    {
        $write_stream = fopen($file_dest_path, 'w');
        $written_length = fwrite($write_stream, $a_data);
        fclose($write_stream);
        if($written_length === false && strlen($a_data) > 0)
        {
            throw new Exception\Forbidden('Forbidden to write file');
        }
        return $written_length;
    }
    
    protected function getPathToDirectory()
    {
        return $this->obj->getDirectory($this->obj->getVersion());
    }
    
    protected function getPathToFile()
    {
        return $this->getPathToDirectory() . "/" . $this->obj->getFileName();
    }
    
    protected function checkForVirus($file_dest_path)
    {
        $vrs = ilUtil::virusHandling($file_dest_path, '', true);
        // If vrs[0] == false -> virus found
        if($vrs[0] == false)
        {
            ilLoggerFactory::getLogger('WebDAV')->error(get_class($this). ' ' . $this->obj->getTitle() ." -> virus found on '$file_dest_path'!");
            unlink($file_dest_path);
            $this->obj->delete();
            throw new Exception\Forbidden('Virus found!');
        }
    }
}
