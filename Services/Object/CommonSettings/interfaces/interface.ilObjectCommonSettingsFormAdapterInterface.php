<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Common settings form adapter. Helps to add and save common object settings for repository objects.
 *
 * @author killing@leifos.de
 * @ingroup ServicesObject
 */
interface ilObjectCommonSettingFormAdapterInterface
{
	/**
	 * Add icon setting to form
	 *
	 * @return null|ilPropertyFormGUI
	 */
	public function addIcon(): ilPropertyFormGUI;

	/**
	 * Save icon setting from form
	 */
	public function saveIcon();

	/**
	 * Add tile image setting to form
	 *
	 * @return null|ilPropertyFormGUI
	 */
	public function addTileImage(): ilPropertyFormGUI;

	/**
	 * Save tile image setting from form
	 */
	public function saveTileImage();


}