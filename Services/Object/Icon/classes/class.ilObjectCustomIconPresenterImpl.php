<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjectCustomIconPresenter
 */
class ilObjectCustomIconPresenterImpl implements \ilObjectCustomIconPresenter
{
	/**
	 * @var ilObjectCustomIcon
	 */
	private $icon = null;

	/**
	 * ilObjectCustomIconPresenter constructor.
	 * @param ilObjectCustomIcon $icon
	 */
	public function __construct(ilObjectCustomIcon $icon)
	{
		$this->icon = $icon;
	}


	/**
	 * @return bool
	 */
	public function exists()
	{
		return $this->icon->exists();
	}

	/**
	 * @return string
	 */
	public function getFullPath()
	{
		return $this->icon->getFullPath();
	}
}