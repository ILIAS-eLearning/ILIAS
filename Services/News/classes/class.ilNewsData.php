<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * News data
 *
 * @author killinh@leifos.de
 * @ingroup ServicesNews
 */
class ilNewsData
{
	/**
	 * @var ilNewsService
	 */
	protected $service;

	/**
	 * Constructor
	 */
	public function __construct(ilNewsService $service)
	{
		$this->service = $service;
	}


}