<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceCriterionFormGUITest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceCriterionFormGUITest extends \ilTermsOfServiceBaseTest
{
	/**
	 *
	 */
	public function testFormIsProperlyBuiltForNewCriterionAssignment()
	{
		$document = $this
			->getMockBuilder(\ilTermsOfServiceDocument::class)
			->disableOriginalConstructor()
			->getMock();

		$criterionAssignment = $this
			->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
			->disableOriginalConstructor()
			->setMethods(['getId', 'getCriterionId'])
			->getMock();

		$criterionAssignment
			->expects($this->any())
			->method('getId')
			->willReturn(0);

		$criterionAssignment
			->expects($this->any())
			->method('getCriterionId')
			->willReturn('');

		$criterionTypeFactory = $this
			->getMockBuilder(\ilTermsOfServiceCriterionTypeFactoryInterface::class)
			->getMock();

		$criterionType1 = $this
			->getMockBuilder(\ilTermsOfServiceCriterionType::class)
			->getMock();

		$criterionType1
			->expects($this->any())
			->method('getTypeIdent')
			->willReturn('dummy1');

		$criterionType1
			->expects($this->any())
			->method('getGUI')
			->willReturn($this->getMockBuilder(\ilTermsOfServiceCriterionTypeGUI::class)->getMock());

		$criterionType2 = $this
			->getMockBuilder(\ilTermsOfServiceCriterionType::class)
			->getMock();

		$criterionType2
			->expects($this->any())
			->method('getTypeIdent')
			->willReturn('dummy2');

		$criterionType2
			->expects($this->any())
			->method('getGUI')
			->willReturn($this->getMockBuilder(\ilTermsOfServiceCriterionTypeGUI::class)->getMock());

		$criterionTypeFactory
			->expects($this->once())
			->method('getTypesByIdentMap')
			->willReturn([
				$criterionType1, $criterionType2
			]);

		$form = new \ilTermsOfServiceCriterionFormGUI(
			$document, $criterionAssignment, $criterionTypeFactory,
			'', 'save', 'cancel'
		);

		$this->assertEquals($criterionType1->getTypeIdent(), $form->getItemByPostVar('criterion')->getValue());
	}

	/**
	 *
	 */
	public function testFormIsProperlyBuiltForExistingCriterionAssignment()
	{
		$document = $this
			->getMockBuilder(\ilTermsOfServiceDocument::class)
			->disableOriginalConstructor()
			->getMock();

		$criterionAssignment = $this
			->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
			->disableOriginalConstructor()
			->setMethods(['getId', 'getCriterionId'])
			->getMock();

		$criterionAssignment
			->expects($this->any())
			->method('getId')
			->willReturn(1);

		$criterionAssignment
			->expects($this->any())
			->method('getCriterionId')
			->willReturn('dummy2');

		$criterionTypeFactory = $this
			->getMockBuilder(\ilTermsOfServiceCriterionTypeFactoryInterface::class)
			->getMock();

		$criterionType1 = $this
			->getMockBuilder(\ilTermsOfServiceCriterionType::class)
			->getMock();

		$criterionType1
			->expects($this->any())
			->method('getTypeIdent')
			->willReturn('dummy1');

		$criterionType1
			->expects($this->any())
			->method('getGUI')
			->willReturn($this->getMockBuilder(\ilTermsOfServiceCriterionTypeGUI::class)->getMock());

		$criterionType2 = $this
			->getMockBuilder(\ilTermsOfServiceCriterionType::class)
			->getMock();

		$criterionType2
			->expects($this->any())
			->method('getTypeIdent')
			->willReturn('dummy2');

		$criterionType2
			->expects($this->any())
			->method('getGUI')
			->willReturn($this->getMockBuilder(\ilTermsOfServiceCriterionTypeGUI::class)->getMock());

		$criterionTypeFactory
			->expects($this->once())
			->method('getTypesByIdentMap')
			->willReturn([
				$criterionType1, $criterionType2
			]);

		$form = new \ilTermsOfServiceCriterionFormGUI(
			$document, $criterionAssignment, $criterionTypeFactory,
			'', 'save', 'cancel'
		);

		$this->assertEquals($criterionType2->getTypeIdent(), $form->getItemByPostVar('criterion')->getValue());
	}
}