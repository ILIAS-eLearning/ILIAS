<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilGalleryUsers
 */
interface ilGalleryUsers
{
	/**
	 * @return array
	 */
	public function getGalleryUsers();

	/**
	 * @return string
	 */
	public function getUserCssClass();
}