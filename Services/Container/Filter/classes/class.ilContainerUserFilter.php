<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Container user filer. This holds the current filter data being used for
 * filtering the objects being presented.
 *
 * Currently a plain assoc array as retrieved by $filter->getData
 *
 * @author killing@leifos.de
 * @ingroup ServicesContainer
 */
class ilContainerUserFilter
{
	/**
	 * @var array
	 */
	protected $data;

	/**
	 * Constructor
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}

	/**
	 * Get data
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Is empty?
	 *
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		$empty = true;
		if (is_array($this->data))
		{
			foreach ($this->data as $d)
			{
				if (trim($d) != "")
				{
					$empty = false;
				}
			}
		}
		return $empty;
	}



}