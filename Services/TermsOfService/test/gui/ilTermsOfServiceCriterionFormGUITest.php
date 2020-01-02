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
            ->method('ui')
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
            ->method('ui')
            ->willReturn($this->getMockBuilder(\ilTermsOfServiceCriterionTypeGUI::class)->getMock());

        $criterionTypeFactory
            ->expects($this->once())
            ->method('getTypesByIdentMap')
            ->willReturn([
                $criterionType1, $criterionType2
            ]);

        $user = $this
            ->getMockBuilder(\ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form = new \ilTermsOfServiceCriterionFormGUI(
            $document,
            $criterionAssignment,
            $criterionTypeFactory,
            $user,
            '',
            'save',
            'cancel'
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
            ->setMethods(['getId', 'getCriterionId', 'getCriterionValue'])
            ->getMock();

        $criterionAssignment
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $criterionAssignment
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('dummy2');

        $criterionAssignment
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn(new \ilTermsOfServiceCriterionConfig([]));

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
            ->method('ui')
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
            ->method('ui')
            ->willReturn($this->getMockBuilder(\ilTermsOfServiceCriterionTypeGUI::class)->getMock());

        $criterionTypeFactory
            ->expects($this->once())
            ->method('getTypesByIdentMap')
            ->willReturn([
                $criterionType1, $criterionType2
            ]);

        $user = $this
            ->getMockBuilder(\ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form = new \ilTermsOfServiceCriterionFormGUI(
            $document,
            $criterionAssignment,
            $criterionTypeFactory,
            $user,
            '',
            'save',
            'cancel'
        );

        $this->assertEquals($criterionType2->getTypeIdent(), $form->getItemByPostVar('criterion')->getValue());
    }

    /**
     *
     */
    public function testFormForNewCriterionAssignmentCanBeSavedForValidInput()
    {
        $document = $this
            ->getMockBuilder(\ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->getMock();

        $document
            ->expects($this->once())
            ->method('save');

        $document
            ->expects($this->once())
            ->method('attachCriterion');

        $criterionAssignment = $this
            ->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCriterionId', 'getCriterionValue'])
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
            ->method('ui')
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
            ->method('ui')
            ->willReturn($this->getMockBuilder(\ilTermsOfServiceCriterionTypeGUI::class)->getMock());

        $criterionTypeFactory
            ->expects($this->once())
            ->method('getTypesByIdentMap')
            ->willReturn([
                $criterionType1, $criterionType2
            ]);

        $criterionTypeFactory
            ->expects($this->once())
            ->method('findByTypeIdent')
            ->willReturn($criterionType1);

        $user = $this
            ->getMockBuilder(\ilObjUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $user
            ->expects($this->any())
            ->method('getId')
            ->willReturn(6);

        $form = $this->getMockBuilder(\ilTermsOfServiceCriterionFormGUI::class)
            ->setConstructorArgs([
                $document, $criterionAssignment, $criterionTypeFactory, $user,
                'action', 'save', 'cancel'
            ])
            ->setMethods(['checkInput'])
            ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $_POST = [
            'criterion' => $criterionType1->getTypeIdent()
        ];

        $form->setCheckInputCalled(true);

        $this->assertTrue($form->saveObject());
        $this->assertFalse($form->hasTranslatedError());
        $this->assertEmpty($form->getTranslatedError());
    }

    /**
     *
     */
    public function testFormForExistingAssignmentCannotBeSavedForInalidInput()
    {
        $lng = $this->getLanguageMock();

        $lng
            ->expects($this->any())
            ->method('txt')
            ->willReturn('translation');

        $this->setGlobalVariable('lng', $lng);

        $document = $this
            ->getMockBuilder(\ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->getMock();

        $document
            ->expects($this->never())
            ->method('save');

        $document
            ->expects($this->never())
            ->method('attachCriterion');

        $criterionAssignment = $this
            ->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCriterionId', 'getCriterionValue'])
            ->getMock();

        $criterionAssignment
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $criterionAssignment
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn(new \ilTermsOfServiceCriterionConfig(['role_id' => 4]));

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
            ->method('ui')
            ->willReturn($this->getMockBuilder(\ilTermsOfServiceCriterionTypeGUI::class)->getMock());

        $criterionType2 = $this
            ->getMockBuilder(\ilTermsOfServiceCriterionType::class)
            ->getMock();

        $criterionType2
            ->expects($this->any())
            ->method('getTypeIdent')
            ->willReturn('dummy2');

        $criterionTypeGui2 = $this->getMockBuilder(\ilTermsOfServiceCriterionTypeGUI::class)->getMock();

        $criterionTypeGui2
            ->expects($this->any())
            ->method('getConfigByForm')
            ->willReturn($criterionAssignment->getCriterionValue());

        $criterionType2
            ->expects($this->any())
            ->method('ui')
            ->willReturn($criterionTypeGui2);

        $criterionTypeFactory
            ->expects($this->once())
            ->method('getTypesByIdentMap')
            ->willReturn([
                $criterionType1, $criterionType2
            ]);

        $criterionTypeFactory
            ->expects($this->exactly(2))
            ->method('findByTypeIdent')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new \ilTermsOfServiceCriterionTypeNotFoundException('')),
                $criterionType1
            );

        $anotherCriterionAssignment = $this
            ->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCriterionId', 'getCriterionValue'])
            ->getMock();

        $anotherCriterionAssignment
            ->expects($this->any())
            ->method('getId')
            ->willReturn(2);

        $anotherCriterionAssignment
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $anotherCriterionAssignment
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn(new \ilTermsOfServiceCriterionConfig(['role_id' => 4]));

        $document
            ->expects($this->once())
            ->method('criteria')
            ->willReturn([$anotherCriterionAssignment]);

        $user = $this
            ->getMockBuilder(\ilObjUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $user
            ->expects($this->any())
            ->method('getId')
            ->willReturn(6);

        $form = $this->getMockBuilder(\ilTermsOfServiceCriterionFormGUI::class)
            ->setConstructorArgs([
                $document, $criterionAssignment, $criterionTypeFactory, $user,
                'action', 'save', 'cancel'
            ])
            ->setMethods(['checkInput'])
            ->getMock();

        $form
            ->expects($this->exactly(2))
            ->method('checkInput')
            ->willReturn(true);

        $_POST = [
            'criterion' => $criterionType1->getTypeIdent()
        ];

        $form->setCheckInputCalled(true);

        $this->assertFalse(
            $form->saveObject(),
            'Failed asserting form cannot be saved selected criterion type was not found'
        );
        $this->assertTrue($form->hasTranslatedError());
        $this->assertNotEmpty($form->getTranslatedError());

        $this->assertFalse(
            $form->saveObject(),
            'Failed asserting form cannot be saved selected criterion type was already assigned to document'
        );
        $this->assertTrue($form->hasTranslatedError());
        $this->assertNotEmpty($form->getTranslatedError());
    }
}
