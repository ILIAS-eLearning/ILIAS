<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Math/test/ilMathBaseAdapterTest.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilMathPhpAdapterTest extends ilMathBaseAdapterTest
{
	/**
	 * @inheritDoc
	 */
	protected function setUp()
	{
		require_once 'Services/Math/classes/class.ilMathPhpAdapter.php';
		$this->math_adapter = new ilMathPhpAdapter();
		parent::setUp();
	}
}