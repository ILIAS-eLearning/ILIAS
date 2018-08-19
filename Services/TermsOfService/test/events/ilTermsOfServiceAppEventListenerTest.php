<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAppEventListenerTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAppEventListenerTest extends \ilTermsOfServiceBaseTest
{
	/**
	 *
	 */
	public function testAcceptanceHistoryDeletionIsDelegatedWhenUserIsDeleted()
	{
		$helper = $this->getMockBuilder(\ilTermsOfServiceHelper::class)->disableOriginalConstructor()->getMock();

		$helper
			->expects($this->once())
			->method('deleteAcceptanceHistoryByUser')
			->with($this->isType('integer'));

		$listener = new \ilTermsOfServiceAppEventListener($helper);
		$listener
			->withComponent('Services/User')
			->withEvent('deleteUser')
			->withParameters(['usr_id' => 6])
			->handle();

		$listener
			->withComponent('Modules/Course')
			->withEvent('deleteUser')
			->withParameters(['usr_id' => 6])
			->handle();

		$listener
			->withComponent('Services/User')
			->withEvent('afterCreate')
			->withParameters(['usr_id' => 6])
			->handle();
	}
}