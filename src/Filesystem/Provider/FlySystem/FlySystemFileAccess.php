<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Provider\FlySystem;

use ILIAS\Data\DataSize;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Provider\FileAccess;
use ILIAS\Filesystem\Visibility;
use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;

/**
 * Class FlySystemFileAccess
 *
 * Fly system file access implementation.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 */
class FlySystemFileAccess implements FileAccess
{

    /**
     * @var FilesystemInterface $flySystemFS
     */
    private $flySystemFS;


    /**
     * FlySystemFileAccess constructor.
     *
     * @param FilesystemInterface $flySystemFS   A configured fly system filesystem instance.
     */
    public function __construct(FilesystemInterface $flySystemFS)
    {
        $this->flySystemFS = $flySystemFS;
    }


    /**
     * Reads a file content to a string.
     *
     * @param string $path The path to the file which should be read.
     *
     * @return string   The file content.
     *
     * @throws FileNotFoundException        If the file doesn't exist.
     * @throws IOException                  If the file could not be red.
     *
     * @since   5.3
     * @version 1.0
     */
    public function read(string $path) : string
    {
        try {
            $result = $this->flySystemFS->read($path);

            if ($result === false) {
                throw new IOException("Could not access the file \"$path\".");
            }

            return $result;
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            throw new FileNotFoundException("File \"$path\" not found.", 0, $ex);
        }
    }


    /**
     * Checks whether a file exists.
     *
     * @param string $path The file path which should be checked.
     *
     * @return bool True if the file exists, otherwise false.
     *
     * @since   5.3
     * @version 1.0
     */
    public function has(string $path) : bool
    {
        return $this->flySystemFS->has($path);
    }


    /**
     * Get a files mime-type.
     *
     * @param string $path The file which should be used to get the mime-type.
     *
     * @return string   The mime-type of the file.
     *
     * @throws FileNotFoundException    If the file is not found.
     * @throws IOException              If the mime-type could not be determined.
     */
    public function getMimeType(string $path) : string
    {
        try {
            $mimeType = $this->flySystemFS->getMimetype($path);
            if ($mimeType === false) {
                throw new IOException("Could not determine the MIME type of the file \"$path\".");
            }

            return $mimeType;
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            throw new FileNotFoundException("File \"$path\" not found.", 0, $ex);
        }
    }


    /**
     * Get the timestamp (mtime) of the file.
     *
     * @param string $path The path to the file.
     *
     * @return \DateTimeImmutable  The timestamp of the file.
     *
     * @throws FileNotFoundException    If the file is not found.
     * @throws IOException              If the file can not be red.
     *
     * @since   5.3
     * @version 1.0
     */
    public function getTimestamp(string $path) : \DateTimeImmutable
    {
        try {
            $rawTimestamp = $this->flySystemFS->getTimestamp($path);
            if ($rawTimestamp === false) {
                throw new IOException("Could not lookup timestamp of the file \"$path\".");
            }

            if (is_numeric($rawTimestamp)) {
                $rawTimestamp = '@' . $rawTimestamp;
            }

            return new \DateTimeImmutable($rawTimestamp);
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            throw new FileNotFoundException("File \"$path\" not found.", 0, $ex);
        }
    }


    /**
     * Get the size of a file.
     *
     * The file size units are provided by the DataSize class.
     *
     * @param string $path         The path to the file.
     * @param int    $fileSizeUnit The unit of the file size, which are defined in the DataSize class.
     *
     * @return DataSize
     * @since   5.3
     * @version 1.0
     *
     * @throws IOException              Thrown if the file is not accessible or the underlying filesystem adapter failed.
     * @throws FileNotFoundException    Thrown if the specified file was not found.
     *
     * @see     DataSize
     */
    public function getSize(string $path, int $fileSizeUnit) : DataSize
    {
        try {
            $byteSize = $this->flySystemFS->getSize($path);

            //check if the fly system adapter failed
            if ($byteSize === false) {
                throw new IOException("Could not calculate the file size of the file \"$path\".");
            }

            $size = new DataSize($byteSize, $fileSizeUnit);
            return  $size;
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            throw new FileNotFoundException("File \"$path\" not found.");
        }
    }


    /**
     * Sets the visibility for a file.
     * Please note that the $visibility must 'public' or 'private'.
     *
     * The Visibility interface provides two constants PUBLIC_ACCESS and PRIVATE_ACCESS.
     * We strongly encourage the consumers of this API to use the constants.
     *
     * @param string $path       The path to the file.
     * @param string $visibility The new visibility for the given file. This value must be 'private' or 'public'.
     *
     * @return bool                         True on success or false on failure.
     * @throws \InvalidArgumentException    If the visibility is not 'public' or 'private'.
     * @throws FileNotFoundException        If the given file could not be found.
     *
     * @since   5.3
     * @version 1.0
     */
    public function setVisibility(string $path, string $visibility) : bool
    {
        if ($this->has($path) === false) {
            throw new FileNotFoundException("Path \"$path\" not found.");
        }

        $this->validateVisibility($visibility);

        return $this->flySystemFS->setVisibility($path, $visibility);
    }


    /**
     * Checks if the given visibility is valid an throws an exception otherwise.
     * If the visibility is valid no further actions are taken.
     *
     * @param string $visibility The visibility which should be validated.
     * @return void
     *
     * @throws \InvalidArgumentException Thrown if the given visibility was considered as invalid.
     */
    private function validateVisibility(string $visibility)
    {
        if (strcmp($visibility, Visibility::PUBLIC_ACCESS) !== 0 && strcmp($visibility, Visibility::PRIVATE_ACCESS) !== 0) {
            throw new \InvalidArgumentException("The access must be 'public' or 'private' but '$visibility' was given.");
        }
    }


    /**
     * Get the file visibility.
     * The file visibility could be 'public' or 'private'.
     *
     * Please note that the Visibility interface defines two constants PUBLIC_ACCESS and PRIVATE_ACCESS
     * to ease the development process.
     *
     * @param string $path The path to the file which should be used.
     *
     * @return string       The string 'public' or 'private'.
     *
     * @throws FileNotFoundException    If the file could not be found.
     * @throws IOException              If the underlying adapter failed to determine the visibility.
     *
     * @since   5.3
     * @version 1.0
     */
    public function getVisibility(string $path) : string
    {
        if ($this->has($path) === false) {
            throw new FileNotFoundException("Path \"$path\" not found.");
        }

        $visibility = $this->flySystemFS->getVisibility($path);

        if ($visibility === false) {
            throw new IOException("Could not determine visibility for path '$path'.");
        }

        return $visibility;
    }


    /**
     * Writes the content to a new file.
     *
     * @param string $path    The path to the file which should be created.
     * @param string $content The content which should be written to the new file.
     *
     * @return void
     *
     * @throws FileAlreadyExistsException   If the file already exists.
     * @throws IOException                  If the file could not be created or written.
     *
     * @since   5.3
     * @version 1.0
     */
    public function write(string $path, string $content)
    {
        try {
            if ($this->flySystemFS->write($path, $content) === false) {
                throw new IOException("Could not write to file \"$path\" because a general IO error occurred. Please check that your destination is writable.");
            }
        } catch (FileExistsException $ex) {
            throw new FileAlreadyExistsException("File \"$path\" already exists.", 0, $ex);
        }
    }


    /**
     * Updates the content of a file.
     * Replaces the file content with a new one.
     *
     * @param string $path       The path to the file which should be updated.
     * @param string $newContent The new file content.
     *
     * @return void
     *
     * @throws FileNotFoundException    If the file is not found.
     * @throws IOException              If the file could not be updated.
     *
     * @since   5.3
     * @version 1.0
     */
    public function update(string $path, string $newContent)
    {
        try {
            if ($this->flySystemFS->update($path, $newContent) === false) {
                throw new IOException("Could not write to file \"$path\" because a general IO error occurred. Please check that your destination is writable.");
            }
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            throw new FileNotFoundException("File \"$path\" was not found update failed.", 0, $ex);
        }
    }


    /**
     * Creates a file or updates an existing one.
     *
     * @param string $path    The path to the file which should be created or updated.
     * @param string $content The content which should be written to the file.
     *
     * @return void
     *
     * @throws IOException  If the file could not be created or updated.
     *
     * @since   5.3
     * @version 1.0
     */
    public function put(string $path, string $content)
    {
        if ($this->flySystemFS->put($path, $content) === false) {
            throw new IOException("Could not write to file \"$path\" because a general IO error occurred. Please check that your destination is writable.");
        }
    }


    /**
     * Deletes a file.
     *
     * @param string $path The path to the file which should be deleted.
     *
     * @return void
     *
     * @throws FileNotFoundException    If the file was not found.
     * @throws IOException              If the file was found but the delete operation finished with errors.
     *
     * @since   5.3
     * @version 1.0
     */
    public function delete(string $path)
    {
        try {
            if ($this->flySystemFS->delete($path) === false) {
                throw new IOException("Could not delete file \"$path\" because a general IO error occurred. Please check that your target is writable.");
            }
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            throw new FileNotFoundException("File \"$path\" was not found delete operation failed.");
        }
    }


    /**
     * Reads the entire file content into a string and removes the file afterwards.
     *
     * @param string $path The file which should be red and removed.
     *
     * @return string       The entire file content.
     *
     * @throws FileNotFoundException    If the file was not found.
     * @throws IOException              If the file could not red or deleted.
     *
     * @since   5.3
     * @version 1.0
     */
    public function readAndDelete(string $path) : string
    {
        $content = $this->read($path);
        $this->delete($path);

        return $content;
    }


    /**
     * Moves a file from the source to the destination.
     *
     * @param string $path    The current path of the file which should be moved.
     * @param string $newPath The new path of the file.
     *
     * @return void
     *
     * @throws FileNotFoundException        If the source file is not found.
     * @throws FileAlreadyExistsException   If the destination file is already existing.
     * @throws IOException                  If the file could not be moved.
     *
     * @since   5.3
     * @version 1.0
     */
    public function rename(string $path, string $newPath)
    {
        try {
            if ($this->flySystemFS->rename($path, $newPath) === false) {
                throw new IOException("Could not move file from \"$path\" to \"$newPath\".");
            }
        } catch (FileExistsException $ex) {
            throw new FileAlreadyExistsException("File \"$newPath\" already exists.");
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            throw new FileNotFoundException("File \"$path\" not found.");
        }
    }


    /**
     * Copy the source file to a destination.
     *
     * @param string $path     The source path to the file which should be copied.
     * @param string $copyPath The destination path of the file copy.
     *
     * @return void
     *
     * @throws FileNotFoundException        If the source file does not exist.
     * @throws FileAlreadyExistsException   If the destination file already exists.
     * @throws IOException                  If the file could not be copied to the destination.
     *
     * @since   5.3
     * @version 1.0
     */
    public function copy(string $path, string $copyPath)
    {
        try {
            if ($this->flySystemFS->copy($path, $copyPath) === false) {
                throw new IOException("Could not copy file \"$path\" to destination \"$copyPath\" because a general IO error occurred. Please check that your destination is writable.");
            }
        } catch (FileExistsException $ex) {
            throw new FileAlreadyExistsException("File destination \"$copyPath\" already exists copy failed.");
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            throw new FileNotFoundException("File source \"$path\" was not found copy failed.");
        }
    }
}
