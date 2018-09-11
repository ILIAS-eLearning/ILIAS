<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilCustomIconObjectConfiguration
{
	/**
	 * @return string[]
	 */
	public function getSupportedFileExtensions();

	/**
	 * @return string
	 */
	public function getTargetFileExtension();

	/**
	 * @return string
	 */
	public function getBaseDirectory();

	/**
	 * @return string
	 */
	public function getSubDirectoryPrefix();

	/**
	 * A collection of post processors which are invoked if a new icon has been uploaded
	 * @return ilObjectCustomIconUploadPostProcessor[]
	 */
	public function getUploadPostProcessors();
}