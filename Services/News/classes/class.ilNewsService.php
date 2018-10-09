<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * News service
 *
 * @author killing@leifos.de
 * @ingroup ServiceNews
 */
class ilNewsService
{
	/**
	 * @var ilNewsServiceDependencies
	 */
	protected $_deps;
	/**
	 * Constructor
	 * @param ilLanguage $lng
	 */
	public function __construct(ilLanguage $lng, ilSetting $settings)
	{
		$this->_deps = new ilNewsServiceDependencies($lng, $settings);
	}

	/**
	 * @inheritdoc
	 */
	public function data(): ilNewsData
	{
		return new ilNewsData($this, $this->_deps);
	}
}