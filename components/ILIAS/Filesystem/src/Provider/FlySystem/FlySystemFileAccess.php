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

declare(strict_types=1);

namespace ILIAS\Filesystem\Provider\FlySystem;

use ILIAS\Data\DataSize;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Provider\FileAccess;
use ILIAS\Filesystem\Visibility;
use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToCopyFile;

/**
 * Fly system file access implementation.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
class FlySystemFileAccess implements FileAccess
{
    public function __construct(
        private FilesystemOperator $flysystem_operator
    ) {
    }

    public function read(string $path): string
    {
        try {
            $path = Util::normalizeRelativePath($path);
            $adapter = $this->flysystem_operator->getAdapter();
            if (!$adapter->has($path)) {
                throw new \League\Flysystem\FileNotFoundException($path);
            }
            $result = $adapter->read($path);

            if (empty($result)) {
                throw new IOException("Could not access the file \"$path\".");
            }

            return $result;
        } catch (\Throwable $ex) {
            throw new FileNotFoundException("File \"$path\" not found.", 0, $ex);
        }
    }

    public function has(string $path): bool
    {
        return $this->flysystem_operator->has($path);
    }

    public function getMimeType(string $path): string
    {
        try {
            $mimeType = $this->flysystem_operator->mimeType($path);
            if ($mimeType === '') {
                throw new IOException("Could not determine the MIME type of the file \"$path\".");
            }

            return $mimeType;
        } catch (UnableToRetrieveMetadata $ex) {
            throw new FileNotFoundException("File \"$path\" not found.", 0, $ex);
        }
    }

    public function getTimestamp(string $path): \DateTimeImmutable
    {
        try {
            $last_modified = $this->flysystem_operator->lastModified($path);

            return new \DateTimeImmutable((string) $last_modified);
        } catch (UnableToRetrieveMetadata $ex) {
            throw new IOException("Could not lookup timestamp of the file \"$path\".");
        } catch (FilesystemException $ex) {
            throw new FileNotFoundException("File \"$path\" not found.", 0, $ex);
        }
    }

    public function getSize(string $path, int $unit): DataSize
    {
        try {
            $byte_size = $this->flysystem_operator->fileSize($path);
            return new DataSize($byte_size, $unit);
        } catch (UnableToRetrieveMetadata) {
            throw new FileNotFoundException("File \"$path\" not found.");
        }
    }

    /**
     * Sets the visibility for a file.
     * Please note that the $visibility must 'public' or 'private'.
     *
     * The Visibility interface provides two constants PUBLIC_ACCESS and PRIVATE_ACCESS.
     * We strongly encourage the consumers of this API to use the constants.
     */
    public function setVisibility(string $path, string $visibility): bool
    {
        if (!$this->has($path)) {
            throw new FileNotFoundException("Path \"$path\" not found.");
        }

        $this->validateVisibility($visibility);

        try {
            $this->flysystem_operator->setVisibility($path, $visibility);
        } catch (\Throwable) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the given visibility is valid an throws an exception otherwise.
     * If the visibility is valid no further actions are taken.
     *
     * @param string $visibility The visibility which should be validated.
     *
     * @throws \InvalidArgumentException Thrown if the given visibility was considered as invalid.
     */
    private function validateVisibility(string $visibility): void
    {
        if (strcmp($visibility, Visibility::PUBLIC_ACCESS) === 0) {
            return;
        }
        if (strcmp($visibility, Visibility::PRIVATE_ACCESS) === 0) {
            return;
        }
        throw new \InvalidArgumentException("The access must be 'public' or 'private' but '$visibility' was given.");
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
     */
    public function getVisibility(string $path): string
    {
        if (!$this->has($path)) {
            throw new FileNotFoundException("Path \"$path\" not found.");
        }

        $visibility = $this->flysystem_operator->getVisibility($path);

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
     *
     * @throws FileAlreadyExistsException   If the file already exists.
     * @throws IOException                  If the file could not be created or written.
     *
     */
    public function write(string $path, string $content): void
    {
        if ($this->flysystem_operator->has($path)) {
            throw new FileAlreadyExistsException("File \"$path\" already exists.");
        }
        try {
            $this->flysystem_operator->write($path, $content);
        } catch (FilesystemException) {
            throw new IOException(
                "Could not write to file \"$path\" because a general IO error occurred. Please check that your destination is writable."
            );
        }
    }

    /**
     * Updates the content of a file.
     * Replaces the file content with a new one.
     *
     * @param string $path        The path to the file which should be updated.
     * @param string $new_content The new file content.
     *
     *
     * @throws FileNotFoundException    If the file is not found.
     * @throws IOException              If the file could not be updated.
     *
     */
    public function update(string $path, string $new_content): void
    {
        try {
            $this->flysystem_operator->write($path, $new_content);
        } catch (UnableToWriteFile $ex) {
            throw new IOException(
                "Could not write to file \"$path\" because a general IO error occurred. Please check that your destination is writable.",
                0,
                $ex
            );
        } catch (UnableToRetrieveMetadata $ex) {
            throw new FileNotFoundException("File \"$path\" was not found update failed.", 0, $ex);
        }
    }

    /**
     * Creates a file or updates an existing one.
     *
     * @param string $path    The path to the file which should be created or updated.
     * @param string $content The content which should be written to the file.
     *
     *
     * @throws IOException  If the file could not be created or updated.
     *
     */
    public function put(string $path, string $content): void
    {
        if ($this->flysystem_operator->put($path, $content) === false) {
            throw new IOException(
                "Could not write to file \"$path\" because a general IO error occurred. Please check that your destination is writable."
            );
        }
    }

    /**
     * Deletes a file.
     *
     * @param string $path The path to the file which should be deleted.
     *
     *
     * @throws FileNotFoundException    If the file was not found.
     * @throws IOException              If the file was found but the delete operation finished with errors.
     *
     */
    public function delete(string $path): void
    {
        try {
            $this->flysystem_operator->delete($path);
        } catch (UnableToRetrieveMetadata) {
            throw new FileNotFoundException("File \"$path\" was not found delete operation failed.");
        } catch (UnableToDeleteFile) {
            throw new IOException(
                "Could not delete file \"$path\" because a general IO error occurred. Please check that your target is writable."
            );
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
     */
    public function readAndDelete(string $path): string
    {
        $content = $this->read($path);
        $this->delete($path);

        return $content;
    }

    /**
     * Moves a file from the source to the destination.
     *
     * @param string $path     The current path of the file which should be moved.
     * @param string $new_path The new path of the file.
     *
     *
     * @throws FileNotFoundException        If the source file is not found.
     * @throws FileAlreadyExistsException   If the destination file is already existing.
     * @throws IOException                  If the file could not be moved.
     *
     */
    public function rename(string $path, string $new_path): void
    {
        if ($this->flysystem_operator->has($new_path)) {
            throw new IOException("File \"$new_path\" already exists.");
        }
        try {
            $this->flysystem_operator->move($path, $new_path);
        } catch (UnableToMoveFile) {
            throw new IOException("Could not move file from \"$path\" to \"$new_path\".");
        } catch (UnableToRetrieveMetadata) {
            throw new FileNotFoundException("File \"$path\" not found.");
        }
    }

    /**
     * Copy the source file to a destination.
     *
     * @param string $path      The source path to the file which should be copied.
     * @param string $copy_path The destination path of the file copy.
     *
     *
     * @throws FileNotFoundException        If the source file does not exist.
     * @throws FileAlreadyExistsException   If the destination file already exists.
     * @throws IOException                  If the file could not be copied to the destination.
     */
    public function copy(string $path, string $copy_path): void
    {
        if ($this->flysystem_operator->has($copy_path)) {
            throw new FileAlreadyExistsException("File \"$copy_path\" already exists.");
        }
        try {
            $this->flysystem_operator->copy($path, $copy_path);
        } catch (UnableToCopyFile) {
            throw new IOException(
                "Could not copy file \"$path\" to destination \"$copy_path\" because a general IO error occurred. Please check that your destination is writable."
            );
        } catch (UnableToRetrieveMetadata) {
            throw new FileNotFoundException("File source \"$path\" was not found copy failed.");
        }
    }
}
