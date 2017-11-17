<?php

class ilGroupNameAsMailValidatorTest extends \ilMailBaseTest
{
	public function testObjectCanBeCreated()
	{
		$validator = new GroupNameAsMailValidator('someHost');
	}
}
