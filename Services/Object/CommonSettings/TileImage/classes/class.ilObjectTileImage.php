<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tile image object
 *
 * @author killing@leifos.de
 * @ingroup ServicesObject
 */
class ilObjectTileImage implements ilObjectTileImageInterface
{
	/**
	 * @var ilObjectService
	 */
	protected $service;

	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var \ILIAS\Filesystem\Filesystem
	 */
	protected $web;

	/**
	 * @var \ILIAS\FileUpload\FileUpload
	 */
	protected $upload;

	/**
	 * @var string file extension
	 */
	protected $ext;

	/**
	 * Constructor
	 */
	public function __construct(ilObjectService $service, int $obj_id)
	{
		$this->service = $service;
		$this->obj_id = $obj_id;
		$this->web = $service->filesystem()->web();
		$this->upload = $service->upload();
		$this->ext = ilContainer::_lookupContainerSetting($obj_id, 'tile_image');
	}

	/**
	 * @inheritdoc
	 */
	public function getExtension(): string
	{
		return $this->ext;
	}

	/**
	 * @inheritdoc
	 */
	public function copy(int $target_obj_id)
	{
		if (!$this->exists()) {
			ilContainer::_deleteContainerSettings($target_obj_id, 'tile_image');
			return;
		}

		try {
			$this->web->copy(
				$this->getRelativePath(),
				preg_replace(
					'/(' . "tile_image_" . ')(\d*)\/(.*)$/',
					'${1}' . $target_obj_id . '/${3}',
					$this->getRelativePath()
				)
			);

			ilContainer::_writeContainerSetting($target_obj_id, 'tile_image', $this->getExtension());
		} catch (\Exception $e) {
			ilContainer::_deleteContainerSettings($target_obj_id, 'tile_image');
		}
	}

	/**
	 * @inheritdoc
	 */
	public function delete()
	{
		if ($this->exists()) {
			try {
				$this->web->deleteDir($this->getRelativeDirectory());
			} catch (\Exception $e) {
			}
		}

		ilContainer::_deleteContainerSettings($this->obj_id, 'tile_image');
	}

	/**
	 * @inheritdoc
	 */
	public function saveFromHttpRequest(string $tmpname)
	{
		$this->createDirectory();

		// remove old file
		$file_name = $this->getRelativePath();
		if ($this->web->has($file_name)) {
			$this->web->delete($file_name);
		}

		if ($this->upload->hasUploads()) {
			if (!$this->upload->hasBeenProcessed())
			{
				$this->upload->process();
			}

			/** @var \ILIAS\FileUpload\DTO\UploadResult $result */
			$results = $this->upload->getResults();
			if (isset($results[$tmpname]))
			{
				$result = $results[$tmpname];
				$this->ext = pathinfo($result->getName(), PATHINFO_EXTENSION);
				$file_name = $this->getRelativePath();
				if ($result->getStatus() == \ILIAS\FileUpload\DTO\ProcessingStatus::OK)
				{
					$this->upload->moveOneFileTo(
						$result,
						$this->getRelativeDirectory(),
						\ILIAS\FileUpload\Location::WEB,
						$this->getFileName(),
						true
					);


					$fullpath = CLIENT_WEB_DIR . "/" . $this->getRelativeDirectory() . "/" . $this->getFileName();
					list($width, $height, $type, $attr) = getimagesize($fullpath);
					$min = min($width, $height);
					ilUtil::execConvert($fullpath . "[0] -geometry " . $min . "x" . $min . "^ -gravity center -extent " . $min . "x" . $min . " " . $fullpath);
				}
			}
		}
		$this->persistImageState($file_name);
	}

	/**
	 * @param string $filename
	 */
	protected function persistImageState($filename)
	{
		$ext = pathinfo($filename, PATHINFO_EXTENSION);

		if ($this->web->has($filename)) {
			ilContainer::_writeContainerSetting($this->obj_id, 'tile_image', $ext);
		} else {
			ilContainer::_deleteContainerSettings($this->obj_id, 'tile_image');
		}
	}

	/**
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 */
	protected function createDirectory()
	{
		$this->web->createDir($this->getRelativeDirectory());

		/*

		$rel_directory  = $this->getRelDirectory();

		if (!$this->web->has(dirname($rel_directory))) {
			$this->web->createDir(dirname($rel_directory));
		}

		if (!$this->web->has($rel_directory)) {
			$this->web->createDir($rel_directory);
		}*/
	}

	/**
	 * @return string
	 */
	public function getRelativeDirectory()
	{
		return implode(DIRECTORY_SEPARATOR, [
			"obj_data",
			"tile_image",
			"tile_image_".$this->obj_id
		]);
	}

	/**
	 * @return string
	 */
	protected function getFileName()
	{
		return 'tile_image.'.$this->getExtension();
	}

	/**
	 * @return string
	 */
	protected function getRelativePath()
	{
		return implode(DIRECTORY_SEPARATOR, [
			$this->getRelativeDirectory(),
			$this->getFileName()
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function exists(): bool
	{
		if (!\ilContainer::_lookupContainerSetting($this->obj_id, 'tile_image', 0)) {
			return false;
		}
		return $this->web->has($this->getRelativePath());
	}

	/**
	 * @inheritdoc
	 */
	public function getFullPath(): string
	{
		// TODO: Currently there is no option to get the relative base directory of a filesystem
		return implode(DIRECTORY_SEPARATOR, [
            \ilUtil::getWebspaceDir(),
            $this->getRelativePath()
        ]);
	}

    /**
     * @param $source_dir
     * @param $ext
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function createFromImportDir($source_dir, $ext)
    {
        $target_dir = implode(DIRECTORY_SEPARATOR, [
            \ilUtil::getWebspaceDir(),
            $this->getRelativeDirectory()
        ]);
        ilUtil::rCopy($source_dir, $target_dir);
        ilContainer::_writeContainerSetting($this->obj_id, 'tile_image', $ext);
    }


}