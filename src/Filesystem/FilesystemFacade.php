<?php

namespace ILIAS\Filesystem;

use ILIAS\Filesystem\Provider\DirectoryAccess;
use ILIAS\Filesystem\Provider\FileAccess;
use ILIAS\Filesystem\Provider\FileStreamAccess;
use ILIAS\Filesystem\Stream\FileStream;

/**
 * Class FilesystemFacade
 *
 * The filesystem facade is used internally to satisfy the Filesystem interface because the implementations are split into
 * different classes to reduce the size and responsibility of each class.
 *
 * This class simply delegates the work to the classes which are responsible for the task.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 *
 * @internal
 */
class FilesystemFacade implements Filesystem {

	/**
	 * @var FileStreamAccess $fileStreamAccess
	 */
	private $fileStreamAccess;
	/**
	 * @var FileAccess $fileAccess
	 */
	private $fileAccess;
	/**
	 * @var DirectoryAccess $directoryAccess
	 */
	private $directoryAccess;


	/**
	 * FilesystemFacade constructor.
	 *
	 * Creates a new instance of the facade with the provided access classes.
	 *
	 * @param FileStreamAccess $fileStreamAccess
	 * @param FileAccess       $fileAccess
	 * @param DirectoryAccess  $directoryAccess
	 */
	public function __construct(FileStreamAccess $fileStreamAccess, FileAccess $fileAccess, DirectoryAccess $directoryAccess) {
		$this->fileStreamAccess = $fileStreamAccess;
		$this->fileAccess = $fileAccess;
		$this->directoryAccess = $directoryAccess;
	}


	/**
	 * @inheritDoc
	 */
	public function hasDir($path) {
		return $this->directoryAccess->hasDir($path);
	}


	/**
	 * @inheritDoc
	 */
	public function listContents($path = '', $recursive = false) {
		return $this->directoryAccess->listContents($path, $recursive);
	}


	/**
	 * @inheritDoc
	 */
	public function createDir($path, $visibility = Visibility::PUBLIC_ACCESS) {
		$this->directoryAccess->createDir($path, $visibility);
	}


	/**
	 * @inheritDoc
	 */
	public function copyDir($source, $destination) {
		$this->directoryAccess->copyDir($source, $destination);
	}


	/**
	 * @inheritDoc
	 */
	public function deleteDir($path) {
		$this->directoryAccess->deleteDir($path);
	}


	/**
	 * @inheritDoc
	 */
	public function read($path) {
		return $this->fileAccess->read($path);
	}


	/**
	 * @inheritDoc
	 */
	public function has($path) {
		return $this->fileAccess->has($path);
	}


	/**
	 * @inheritDoc
	 */
	public function getMimeType($path) {
		return $this->fileAccess->getMimeType($path);
	}


	/**
	 * @inheritDoc
	 */
	public function getTimestamp($path) {
		return $this->fileAccess->getTimestamp($path);
	}


	/**
	 * @inheritDoc
	 */
	public function getSize($path, $fileSizeUnit) {
		return $this->fileAccess->getSize($path, $fileSizeUnit);
	}


	/**
	 * @inheritDoc
	 */
	public function setVisibility($path, $visibility) {
		$this->fileAccess->setVisibility($path, $visibility);
	}


	/**
	 * @inheritDoc
	 */
	public function getVisibility($path) {
		$this->fileAccess->getVisibility($path);
	}


	/**
	 * @inheritDoc
	 */
	public function readStream($path) {
		return $this->fileStreamAccess->readStream($path);
	}


	/**
	 * @inheritDoc
	 */
	public function writeStream($path, FileStream $stream) {
		$this->fileStreamAccess->writeStream($path, $stream);
	}


	/**
	 * @inheritDoc
	 */
	public function putStream($path, FileStream $stream) {
		$this->fileStreamAccess->putStream($path, $stream);
	}


	/**
	 * @inheritDoc
	 */
	public function updateStream($path, FileStream $stream) {
		$this->fileStreamAccess->updateStream($path, $stream);
	}


	/**
	 * @inheritDoc
	 */
	public function write($path, $content) {
		$this->fileAccess->write($path, $content);
	}


	/**
	 * @inheritDoc
	 */
	public function update($path, $newContent) {
		$this->fileAccess->update($path, $newContent);
	}


	/**
	 * @inheritDoc
	 */
	public function put($path, $content) {
		$this->fileAccess->put($path, $content);
	}


	/**
	 * @inheritDoc
	 */
	public function delete($path) {
		$this->fileAccess->delete($path);
	}


	/**
	 * @inheritDoc
	 */
	public function readAndDelete($path) {
		$this->fileAccess->readAndDelete($path);
	}


	/**
	 * @inheritDoc
	 */
	public function rename($path, $newPath) {
		$this->fileAccess->rename($path, $newPath);
	}


	/**
	 * @inheritDoc
	 */
	public function copy($path, $copyPath) {
		$this->fileAccess->copy($path, $copyPath);
	}
}