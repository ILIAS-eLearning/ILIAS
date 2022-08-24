<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilTermsOfServiceCriterionFormGUITest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceCriterionFormGUITest extends ilTermsOfServiceBaseTest
{
    public function testFormIsProperlyBuiltForNewCriterionAssignment(): void
    {
        $document = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->getMock();

        $criterionAssignment = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionId'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment
            ->method('getId')
            ->willReturn(0);

        $criterionAssignment
            ->method('getCriterionId')
            ->willReturn('');

        $criterionTypeFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();

        $criterionType1 = $this
            ->getMockBuilder(ilTermsOfServiceCriterionType::class)
            ->getMock();

        $criterionType1
            ->method('getTypeIdent')
            ->willReturn('dummy1');

        $criterionType1
            ->method('ui')
            ->willReturn($this->getMockBuilder(ilTermsOfServiceCriterionTypeGUI::class)->getMock());

        $criterionType2 = $this
            ->getMockBuilder(ilTermsOfServiceCriterionType::class)
            ->getMock();

        $criterionType2
            ->method('getTypeIdent')
            ->willReturn('dummy2');

        $criterionType2
            ->method('ui')
            ->willReturn($this->getMockBuilder(ilTermsOfServiceCriterionTypeGUI::class)->getMock());

        $criterionTypeFactory
            ->expects($this->once())
            ->method('getTypesByIdentMap')
            ->willReturn([
                $criterionType1,
                $criterionType2
            ]);

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form = new ilTermsOfServiceCriterionFormGUI(
            $document,
            $criterionAssignment,
            $criterionTypeFactory,
            $user,
            '',
            'save',
            'cancel'
        );

        $this->assertSame($criterionType1->getTypeIdent(), $form->getItemByPostVar('criterion')->getValue());
    }

    public function testFormIsProperlyBuiltForExistingCriterionAssignment(): void
    {
        $document = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->getMock();

        $criterionAssignment = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionId', 'getCriterionValue'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment
            ->method('getId')
            ->willReturn(1);

        $criterionAssignment
            ->method('getCriterionId')
            ->willReturn('dummy2');

        $criterionAssignment
            ->method('getCriterionValue')
            ->willReturn(new ilTermsOfServiceCriterionConfig([]));

        $criterionTypeFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();

        $criterionType1 = $this
            ->getMockBuilder(ilTermsOfServiceCriterionType::class)
            ->getMock();

        $criterionType1
            ->method('getTypeIdent')
            ->willReturn('dummy1');

        $criterionType1
            ->method('ui')
            ->willReturn($this->getMockBuilder(ilTermsOfServiceCriterionTypeGUI::class)->getMock());

        $criterionType2 = $this
            ->getMockBuilder(ilTermsOfServiceCriterionType::class)
            ->getMock();

        $criterionType2
            ->method('getTypeIdent')
            ->willReturn('dummy2');

        $criterionType2
            ->method('ui')
            ->willReturn($this->getMockBuilder(ilTermsOfServiceCriterionTypeGUI::class)->getMock());

        $criterionTypeFactory
            ->expects($this->once())
            ->method('getTypesByIdentMap')
            ->willReturn([
                $criterionType1,
                $criterionType2
            ]);

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form = new ilTermsOfServiceCriterionFormGUI(
            $document,
            $criterionAssignment,
            $criterionTypeFactory,
            $user,
            '',
            'save',
            'cancel'
        );

        $this->assertSame($criterionType2->getTypeIdent(), $form->getItemByPostVar('criterion')->getValue());
    }

    public function testFormForNewCriterionAssignmentCanBeSavedForValidInput(): void
    {
        $document = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->getMock();

        $document
            ->expects($this->once())
            ->method('save');

        $document
            ->expects($this->once())
            ->method('attachCriterion');

        $criterionAssignment = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionId', 'getCriterionValue'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment
            ->method('getId')
            ->willReturn(0);

        $criterionAssignment
            ->method('getCriterionId')
            ->willReturn('');

        $criterionTypeFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();

        $criterionType1 = $this
            ->getMockBuilder(ilTermsOfServiceCriterionType::class)
            ->getMock();

        $criterionType1
            ->method('getTypeIdent')
            ->willReturn('dummy1');

        $criterionType1
            ->method('ui')
            ->willReturn($this->getMockBuilder(ilTermsOfServiceCriterionTypeGUI::class)->getMock());

        $criterionType2 = $this
            ->getMockBuilder(ilTermsOfServiceCriterionType::class)
            ->getMock();

        $criterionType2
            ->method('getTypeIdent')
            ->willReturn('dummy2');

        $criterionType2
            ->method('ui')
            ->willReturn($this->getMockBuilder(ilTermsOfServiceCriterionTypeGUI::class)->getMock());

        $criterionTypeFactory
            ->expects($this->once())
            ->method('getTypesByIdentMap')
            ->willReturn([
                $criterionType1,
                $criterionType2
            ]);

        $criterionTypeFactory
            ->expects($this->once())
            ->method('findByTypeIdent')
            ->willReturn($criterionType1);

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();

        $user
            ->method('getId')
            ->willReturn(6);

        $form = $this->getMockBuilder(ilTermsOfServiceCriterionFormGUI::class)
                     ->setConstructorArgs([
                         $document,
                         $criterionAssignment,
                         $criterionTypeFactory,
                         $user,
                         'action',
                         'save',
                         'cancel'
                     ])
                     ->onlyMethods(['checkInput'])
                     ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form->setCheckInputCalled(true);

        $this->assertTrue($form->saveObject());
        $this->assertFalse($form->hasTranslatedError());
        $this->assertEmpty($form->getTranslatedError());
    }

    public function testFormForExistingAssignmentCannotBeSavedForInvalidInput(): void
    {
        $lng = $this->getLanguageMock();

        $lng
            ->method('txt')
            ->willReturn('translation');

        $this->setGlobalVariable('lng', $lng);

        $document = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->getMock();

        $document
            ->expects($this->never())
            ->method('save');

        $document
            ->expects($this->never())
            ->method('attachCriterion');

        $criterionAssignment = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionId', 'getCriterionValue'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment
            ->method('getId')
            ->willReturn(1);

        $criterionAssignment
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment
            ->method('getCriterionValue')
            ->willReturn(new ilTermsOfServiceCriterionConfig(['role_id' => 4]));

        $criterionTypeFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();

        $criterionType1 = $this
            ->getMockBuilder(ilTermsOfServiceCriterionType::class)
            ->getMock();

        $criterionType1
            ->method('getTypeIdent')
            ->willReturn('dummy1');

        $criterionType1
            ->method('ui')
            ->willReturn($this->getMockBuilder(ilTermsOfServiceCriterionTypeGUI::class)->getMock());

        $criterionType2 = $this
            ->getMockBuilder(ilTermsOfServiceCriterionType::class)
            ->getMock();

        $criterionType2
            ->method('getTypeIdent')
            ->willReturn('dummy2');

        $criterionTypeGui2 = $this->getMockBuilder(ilTermsOfServiceCriterionTypeGUI::class)->getMock();

        $criterionTypeGui2
            ->method('getConfigByForm')
            ->willReturn($criterionAssignment->getCriterionValue());

        $criterionType2
            ->method('ui')
            ->willReturn($criterionTypeGui2);

        $criterionTypeFactory
            ->expects($this->once())
            ->method('getTypesByIdentMap')
            ->willReturn([
                $criterionType1,
                $criterionType2
            ]);

        $criterionTypeFactory
            ->expects($this->exactly(2))
            ->method('findByTypeIdent')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new ilTermsOfServiceCriterionTypeNotFoundException('')),
                $criterionType1
            );

        $anotherCriterionAssignment = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionId', 'getCriterionValue'])
            ->addMethods(['getId'])
            ->getMock();

        $anotherCriterionAssignment
            ->method('getId')
            ->willReturn(2);

        $anotherCriterionAssignment
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $anotherCriterionAssignment
            ->method('getCriterionValue')
            ->willReturn(new ilTermsOfServiceCriterionConfig(['role_id' => 4]));

        $document
            ->expects($this->once())
            ->method('criteria')
            ->willReturn([$anotherCriterionAssignment]);

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();

        $user
            ->method('getId')
            ->willReturn(6);

        $form = $this->getMockBuilder(ilTermsOfServiceCriterionFormGUI::class)
                     ->setConstructorArgs([
                         $document,
                         $criterionAssignment,
                         $criterionTypeFactory,
                         $user,
                         'action',
                         'save',
                         'cancel'
                     ])
                     ->onlyMethods(['checkInput'])
                     ->getMock();

        $form
            ->expects($this->exactly(2))
            ->method('checkInput')
            ->willReturn(true);

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
