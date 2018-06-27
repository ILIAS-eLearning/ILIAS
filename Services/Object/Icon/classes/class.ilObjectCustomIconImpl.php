<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/Icon/interfaces/interface.ilObjectCustomIcon.php';

/**
 * Class ilObjectCustomIconImpl
 * TODO: Inject database persistence in future instead of using \ilContainer
 */
class ilObjectCustomIconImpl implements \ilObjectCustomIcon
{
	const ICON_BASENAME = 'icon_custom';

	/**
	 * @var \ILIAS\Filesystem\Filesystem
	 */
	protected $webDirectory;

	/**
	 * @var \ILIAS\FileUpload\FileUpload
	 */
	protected $upload;

	/**
	 * @var \ilCustomIconObjectConfiguration
	 */
	protected $config;

	/**
	 * @var int
	 */
	protected $objId;

	public function __construct($webDirectory, $uploadService, \ilCustomIconObjectConfiguration $config, $objId)
	{
		$this->objId = $objId;

		$this->webDirectory = $webDirectory;
		$this->upload       = $uploadService;
		$this->config       = $config;
	}

	/**
	 * @return int
	 */
	protected function getObjId()
	{
		return $this->objId;
	}

	/**
	 * @inheritdoc
	 */
	public function copy($targetObjId)
	{
		if (!$this->exists()) {
			\ilContainer::_writeContainerSetting($targetObjId, 'icon_custom', 0);
			return;
		}

		try {
			$this->webDirectory->copy(
				$this->getRelativePath(),
				preg_replace(
					'/(' . $this->config->getSubDirectoryPrefix() . ')(\d*)\/(.*)$/',
					'${1}' . $targetObjId . '/${3}',
					$this->getRelativePath()
				)
			);

			\ilContainer::_writeContainerSetting($targetObjId, 'icon_custom', 1);
		} catch (\Exception $e) {
			\ilContainer::_writeContainerSetting($targetObjId, 'icon_custom', 0);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function delete()
	{
		if ($this->exists()) {
			try {
				$this->webDirectory->deleteDir($this->getIconDirectory());
			} catch (\Exception $e) {
			}
		}

		\ilContainer::_deleteContainerSettings($this->getObjId(), 'icon_custom');
	}

	/**
	 * @return string[]
	 */
	public function getSupportedFileExtensions()
	{
		return $this->config->getSupportedFileExtensions();
	}

	/**
	 * @inheritdoc
	 */
	public function saveFromSourceFile($sourceFilePath)
	{
		$this->createCustomIconDirectory();

		$fileName = $this->getRelativePath();

		if ($this->webDirectory->has($fileName)) {
			$this->webDirectory->delete($fileName);
		}

		$this->webDirectory->copy($sourceFilePath, $fileName);

		$this->persistIconState($fileName);
	}

	/**
	 * @inheritdoc
	 */
	public function saveFromHttpRequest()
	{
		$this->createCustomIconDirectory();

		$fileName = $this->getRelativePath();

		if ($this->webDirectory->has($fileName)) {
			$this->webDirectory->delete($fileName);
		}

		if ($this->upload->hasUploads() && !$this->upload->hasBeenProcessed()) {
			$this->upload->process();

			/** @var \ILIAS\FileUpload\DTO\UploadResult $result */
			$result = array_values($this->upload->getResults())[0];
			if ($result->getStatus() == \ILIAS\FileUpload\DTO\ProcessingStatus::OK) {
				$this->upload->moveOneFileTo(
					$result,
					$this->getIconDirectory(),
					\ILIAS\FileUpload\Location::WEB,
					$this->getIconFileName(),
					true
				);
			}

			foreach ($this->config->getUploadPostProcessors() as $processor) {
				$processor->process($fileName);
			}
		}

		$this->persistIconState($fileName);
	}

	/**
	 * @param string$fileName
	 */
	protected function persistIconState($fileName)
	{
		if ($this->webDirectory->has($fileName)) {
			\ilContainer::_writeContainerSetting($this->getObjId(), 'icon_custom', 1);
		} else {
			\ilContainer::_writeContainerSetting($this->getObjId(), 'icon_custom', 0);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function remove()
	{
		$fileName = $this->getRelativePath();

		if ($this->webDirectory->has($fileName)) {
			$this->webDirectory->delete($fileName);
		}

		\ilContainer::_writeContainerSetting($this->getObjId(), 'icon_custom', 0);
	}

	/**
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 */
	protected function createCustomIconDirectory()
	{
		$iconDirectory  = $this->getIconDirectory();

		if (!$this->webDirectory->has(dirname($iconDirectory))) {
			$this->webDirectory->createDir(dirname($iconDirectory));
		}

		if (!$this->webDirectory->has($iconDirectory)) {
			$this->webDirectory->createDir($iconDirectory);
		}
	}

	/**
	 * @return string
	 */
	protected function getIconDirectory()
	{
		return implode(DIRECTORY_SEPARATOR, [
			$this->config->getBaseDirectory(),
			$this->config->getSubDirectoryPrefix() . $this->getObjId()
		]);
	}

	/**
	 * @return string
	 */
	protected function getIconFileName()
	{
		return self::ICON_BASENAME . '.' . $this->config->getTargetFileExtension();
	}

	/**
	 * @return string
	 */
	protected function getRelativePath()
	{
		return implode(DIRECTORY_SEPARATOR, [
			$this->getIconDirectory(),
			$this->getIconFileName()
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function exists()
	{
		if (!\ilContainer::_lookupContainerSetting($this->getObjId(), 'icon_custom', 0)) {
			return false;
		}

		return $this->webDirectory->has($this->getRelativePath());
	}

	/**
	 * @inheritdoc
	 */
	public function getFullPath()
	{
		// TODO: Currently there is no option to get the relative base directory of a filesystem
		return implode(DIRECTORY_SEPARATOR, [
			\ilUtil::getWebspaceDir(),
			$this->getRelativePath()
		]);
	}
}