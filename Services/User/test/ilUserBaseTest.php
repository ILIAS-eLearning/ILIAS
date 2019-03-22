<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilUserBaseTest extends TestCase
{
	/**
	 * @param string $exception_class
	 */
	protected function assertException($exception_class)
	{
		$this->expectException($exception_class);
	}
}