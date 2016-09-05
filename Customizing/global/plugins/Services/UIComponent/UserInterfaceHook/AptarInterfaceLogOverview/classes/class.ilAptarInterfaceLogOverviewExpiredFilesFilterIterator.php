<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAptarInterfaceLogOverviewExpiredFilesFilterIterator
 */
class ilAptarInterfaceLogOverviewExpiredFilesFilterIterator extends FilterIterator
{
	/**
	 * Boundary timestamp
	 * @var     integer
	 */
	protected $boundary_ts;

	/**
	 * @param Iterator $iterator
	 * @param integer  $boundary_ts
	 */
	public function __construct(Iterator $iterator, $boundary_ts)
	{
		parent::__construct($iterator);

		$this->boundary_ts = $boundary_ts;
	}

	/**
	 * Check whether the current element of the iterator is acceptable
	 * @access  public
	 * @return  boolean true/false
	 */
	public function accept()
	{
		/**
		 * @var SplFileInfo
		 */
		$current = parent::current();

		if(!is_numeric($this->boundary_ts) || $this->boundary_ts <= 0)
		{
			// Return false, otherwise all files will be deleted...
			return false;
		}

		if($current->getMTime() < $this->boundary_ts)
		{
			return true;
		}

		return false;
	}
}