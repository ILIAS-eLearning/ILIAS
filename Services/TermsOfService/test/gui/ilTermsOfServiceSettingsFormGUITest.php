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
            '',
            'save',
            true
        );

        $this->assertCount(1, $form->getCommandButtons(), 'Failed asserting save button is given if form is editable');
        $this->assertArrayHasKey(0, $form->getCommandButtons(), 'Failed asserting save button ist given if form is editable');
        $this->assertEquals('save', $form->getCommandButtons()[0]['cmd'], 'Failed asserting save button ist given if form is editable');

        $form = new \ilTermsOfServiceSettingsFormGUI(
            $tos,
            '',
            'save',
            false
        );

        $this->assertCount(0, $form->getCommandButtons(), 'Failed asserting no button is given if form is not editable');
    }

    /**
     *
     */
    public function testFormCanBeSavedWithDisabledService()
    {
        $tos = $this->getMockBuilder(\ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();

        $tos
            ->expects($this->any())
            ->method('getStatus')
            ->willReturn(false);

        $tos
            ->expects($this->once())
            ->method('saveStatus')
            ->with(false);

        $form = $this->getMockBuilder(\ilTermsOfServiceSettingsFormGUI::class)
            ->setConstructorArgs([
                $tos,
                '', 'save', true
            ])
            ->setMethods(['checkInput', 'getInput'])
            ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form
            ->expects($this->exactly(2))
            ->method('getInput')
            ->willReturn(0);

        $_POST = [
            'tos_status' => 1
        ];

        $form->setCheckInputCalled(true);

        $this->assertTrue($form->saveObject());
        $this->assertFalse($form->hasTranslatedError());
        $this->assertEmpty($form->getTranslatedError());
    }

    /**
     *
     */
    public function testFormCanBeSavedWithEnabledServiceWhenAtLeastOneDocumentExists()
    {
        $tos = $this->getMockBuilder(\ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();

        $tos
            ->expects($this->any())
            ->method('getStatus')
            ->willReturn(false);

        $tos
            ->expects($this->once())
            ->method('saveStatus')
            ->with(true);

        $form = $this->getMockBuilder(\ilTermsOfServiceSettingsFormGUI::class)
            ->setConstructorArgs([
                $tos,
                '', 'save', true
            ])
            ->setMethods(['checkInput', 'getInput'])
            ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form
            ->expects($this->exactly(2))
            ->method('getInput')
            ->willReturn(1);

        $_POST = [
            'tos_status' => 1
        ];

        $form->setCheckInputCalled(true);

        $documentConnector = $this->getMockBuilder(\arConnector::class)->getMock();#

        $documentConnector
            ->expects($this->once())
            ->method('affectedRows')
            ->willReturn(2);

        \arConnectorMap::register(new \ilTermsOfServiceDocument(), $documentConnector);

        $this->assertTrue($form->saveObject());
        $this->assertFalse($form->hasTranslatedError());
        $this->assertEmpty($form->getTranslatedError());
    }

    /**
     *
     */
    public function testFormCannotBeSavedWithEnabledServiceWhenNoDocumentsExistAndServiceIsCurrentlyDisabled()
    {
        $lng = $this->getLanguageMock();

        $lng
            ->expects($this->any())
            ->method('txt')
            ->willReturn('translation');

        $this->setGlobalVariable('lng', $lng);

        $tos = $this->getMockBuilder(\ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();

        $tos
            ->expects($this->any())
            ->method('getStatus')
            ->willReturn(false);

        $tos
            ->expects($this->never())
            ->method('saveStatus');

        $form = $this->getMockBuilder(\ilTermsOfServiceSettingsFormGUI::class)
            ->setConstructorArgs([
                $tos,
                '', 'save', true
            ])
            ->setMethods(['checkInput', 'getInput'])
            ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form
            ->expects($this->once())
            ->method('getInput')
            ->willReturn(1);

        $_POST = [
            'tos_status' => 1
        ];

        $form->setCheckInputCalled(true);

        $documentConnector = $this->getMockBuilder(\arConnector::class)->getMock();#

        $documentConnector
            ->expects($this->once())
            ->method('affectedRows')
            ->willReturn(0);

        \arConnectorMap::register(new \ilTermsOfServiceDocument(), $documentConnector);

        $this->assertFalse($form->saveObject());
        $this->assertTrue($form->hasTranslatedError());
        $this->assertNotEmpty($form->getTranslatedError());
    }

    /**
     *
     */
    public function testFormCanBeSavedWithEnabledServiceWhenNoDocumentsExistButServiceIsAlreadyEnabled()
    {
        $tos = $this->getMockBuilder(\ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();

        $tos
            ->expects($this->any())
            ->method('getStatus')
            ->willReturn(true);

        $tos
            ->expects($this->once())
            ->method('saveStatus');

        $form = $this->getMockBuilder(\ilTermsOfServiceSettingsFormGUI::class)
            ->setConstructorArgs([
                $tos,
                '', 'save', true
            ])
            ->setMethods(['checkInput', 'getInput'])
            ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form
            ->expects($this->exactly(2))
            ->method('getInput')
            ->willReturn(1);

        $_POST = [
            'tos_status' => 1
        ];

        $form->setCheckInputCalled(true);

        $documentConnector = $this->getMockBuilder(\arConnector::class)->getMock();#

        $documentConnector
            ->expects($this->once())
            ->method('affectedRows')
            ->willReturn(0);

        \arConnectorMap::register(new \ilTermsOfServiceDocument(), $documentConnector);

        $this->assertTrue($form->saveObject());
        $this->assertFalse($form->hasTranslatedError());
        $this->assertEmpty($form->getTranslatedError());
    }
}
