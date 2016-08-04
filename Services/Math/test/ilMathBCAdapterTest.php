<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Math/test/ilMathBaseAdapterTest.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilMathBCAdapterTest extends ilMathBaseAdapterTest
{
	/**
	 * @inheritDoc
	 */
	public function setUp()
	{
		if(!extension_loaded('bcmath'))
		{
			$this->markTestSkipped('Could not execute test due to missing bcmath extension!');
			return;
		}

		require_once 'Services/Math/classes/class.ilMathBCMathAdapter.php';
		$this->math_adapter = new ilMathBCMathAdapter();
		parent::setUp();
	}
}