<?php

class ilGroupNameAsMailValidatorTest extends \ilMailBaseTest
{
	public function testObjectCanBeCreated()
	{
		$validator = new ilGroupNameAsMailValidator('someHost');
	}
}
