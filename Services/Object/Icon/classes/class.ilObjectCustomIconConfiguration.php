<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/Icon/interfaces/interface.ilCustomIconObjectConfiguration.php';

/**
 * Class ilObjectIconConfiguration
 */
class ilObjectCustomIconConfiguration implements \ilCustomIconObjectConfiguration
{
	/**
	 * @return string[]
	 */
	public function getSupportedFileExtensions()
	{
		return ['svg'];
	}

	/**
	 * @return string
	 */
	public function getTargetFileExtension()
	{
		return 'svg';
	}

	/**
	 * @return string
	 */
	public function getBaseDirectory()
	{
		return 'custom_icons';
	}

	/**
	 * @return string
	 */
	public function getSubDirectoryPrefix()
	{
		return 'obj_';
	}

	/**
	 * @inheritdoc
	 */
	public function getUploadPostProcessors()
	{
		return [];
	}
}