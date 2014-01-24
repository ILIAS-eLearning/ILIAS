<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Component/classes/class.ilPlugin.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ModulesTest
 */
class ilTestExportFilename
{
	/**
	 * @var ilObjTest
	 */
	protected $test;

	/**
	 * @var int
	 */
	protected $timestamp = 0;

	/**
	 * @param ilObjTest $test
	 */
	public function __construct(ilObjTest $test)
	{
		$this->test      = $test;
		$this->timestamp = time();
	}

	/**
	 * @return int
	 */
	public function getTimestamp()
	{
		return $this->timestamp;
	}

	/**
	 * @param string $extension
	 * @param string $additional
	 * @return string
	 * @throws ilException
	 */
	public function getPathname($extension, $additional = '')
	{
		if(!is_string($extension) || !strlen($extension))
		{
			throw new ilException('Missing file extension! Please pass a file extension of type string.');
		}
		else if(substr_count($extension, '.') > 1 || (strpos($extension, '.') !== false && strpos($extension, '.') !== 0))
		{
			throw new ilException('Please use at most one dot in your file extension.');
		}
		else if(strpos($extension, '.') === 0)
		{
			$extension = substr($extension, 1);
		}

		if(!is_string($additional))
		{
			
		}
		else if(strlen($additional))
		{
			if(strpos($additional, '__') === 0)
			{
				throw new ilException('The additional file part may not contain __ at the beginning!');
			}

			$additional = '__' . $additional . '_';
		}
		else
		{
			$additional = '_';
		}

		return $this->test->getExportDirectory() . DIRECTORY_SEPARATOR . $this->getTimestamp() . '__' . IL_INST_ID . '__' . $this->test->getType() . $additional . $this->test->getId() . '.' . $extension;
	}
}