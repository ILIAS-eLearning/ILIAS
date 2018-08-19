<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceSettingsFormGUITest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceSettingsFormGUITest extends \ilTermsOfServiceBaseTest
{
	/**
	 *
	 */
	public function testFormCanBeProperlyBuilt()
	{
		$tos = $this->getMockBuilder(\ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();

		$tos
			->expects($this->any())
			->method('getStatus')
			->willReturn(true);

		$form = new \ilTermsOfServiceSettingsFormGUI(
			$tos,
			'', 'save', true
		);

		$this->assertCount(1, $form->getCommandButtons(),'Failed asserting save button is given if form is editable');
		$this->assertArrayHasKey(0, $form->getCommandButtons(),'Failed asserting save button ist given if form is editable');
		$this->assertEquals('save', $form->getCommandButtons()[0]['cmd'],'Failed asserting save button ist given if form is editable');

		$form = new \ilTermsOfServiceSettingsFormGUI(
			$tos,
			'', 'save', false
		);

		$this->assertCount(0, $form->getCommandButtons(),'Failed asserting no button is given if form is not editable');
	}
}