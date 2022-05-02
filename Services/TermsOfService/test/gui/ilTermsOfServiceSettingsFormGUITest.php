<?php declare(strict_types=1);

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
 * Class ilTermsOfServiceSettingsFormGUITest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceSettingsFormGUITest extends ilTermsOfServiceBaseTest
{
    public function testFormCanBeProperlyBuilt() : void
    {
        $tos = $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();

        $tos
            ->method('getStatus')
            ->willReturn(true);

        $lng = $this->getLanguageMock();

        $lng
            ->method('txt')
            ->willReturn('translation');

        $this->setGlobalVariable('lng', $lng);

        $form = new ilTermsOfServiceSettingsFormGUI(
            $tos,
            '',
            'save',
            true
        );

        $this->assertCount(1, $form->getCommandButtons(), 'Failed asserting save button is given if form is editable');
        $this->assertArrayHasKey(
            0,
            $form->getCommandButtons(),
            'Failed asserting save button ist given if form is editable'
        );
        $this->assertSame(
            'save',
            $form->getCommandButtons()[0]['cmd'],
            'Failed asserting save button ist given if form is editable'
        );

        $form = new ilTermsOfServiceSettingsFormGUI(
            $tos,
            '',
            'save',
            false
        );

        $this->assertCount(
            0,
            $form->getCommandButtons(),
            'Failed asserting no button is given if form is not editable'
        );
    }

    public function testFormCanBeSavedWithDisabledService() : void
    {
        $tos = $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();

        $tos
            ->method('getStatus')
            ->willReturn(false);

        $tos
            ->expects($this->once())
            ->method('saveStatus')
            ->with(false);

        $form = $this->getMockBuilder(ilTermsOfServiceSettingsFormGUI::class)
                     ->setConstructorArgs([
                         $tos,
                         '',
                         'save',
                         true
                     ])
                     ->onlyMethods(['checkInput', 'getInput'])
                     ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form
            ->expects($this->exactly(2))
            ->method('getInput')
            ->willReturn(0);

        $form->setCheckInputCalled(true);

        $this->assertTrue($form->saveObject());
        $this->assertFalse($form->hasTranslatedError());
        $this->assertEmpty($form->getTranslatedError());
    }

    public function testFormCanBeSavedWithEnabledServiceWhenAtLeastOneDocumentExists() : void
    {
        $tos = $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();

        $tos
            ->method('getStatus')
            ->willReturn(false);

        $tos
            ->expects($this->once())
            ->method('saveStatus')
            ->with(true);

        $form = $this->getMockBuilder(ilTermsOfServiceSettingsFormGUI::class)
                     ->setConstructorArgs([
                         $tos,
                         '',
                         'save',
                         true
                     ])
                     ->onlyMethods(['checkInput', 'getInput'])
                     ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form
            ->expects($this->exactly(3))
            ->method('getInput')
            ->willReturn(1);

        $form->setCheckInputCalled(true);

        $documentConnector = $this->getMockBuilder(arConnector::class)->getMock();

        $documentConnector
            ->expects($this->once())
            ->method('affectedRows')
            ->willReturn(2);

        arConnectorMap::register(new ilTermsOfServiceDocument(), $documentConnector);

        $this->assertTrue($form->saveObject());
        $this->assertFalse($form->hasTranslatedError());
        $this->assertEmpty($form->getTranslatedError());
    }

    public function testFormCannotBeSavedWithEnabledServiceWhenNoDocumentsExistAndServiceIsCurrentlyDisabled() : void
    {
        $lng = $this->getLanguageMock();

        $lng
            ->method('txt')
            ->willReturn('translation');

        $this->setGlobalVariable('lng', $lng);

        $tos = $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();

        $tos
            ->method('getStatus')
            ->willReturn(false);

        $tos
            ->expects($this->never())
            ->method('saveStatus');

        $form = $this->getMockBuilder(ilTermsOfServiceSettingsFormGUI::class)
                     ->setConstructorArgs([
                         $tos,
                         '',
                         'save',
                         true
                     ])
                     ->onlyMethods(['checkInput', 'getInput'])
                     ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form
            ->expects($this->once())
            ->method('getInput')
            ->willReturn(1);

        $form->setCheckInputCalled(true);

        $documentConnector = $this->getMockBuilder(arConnector::class)->getMock();#

        $documentConnector
            ->expects($this->once())
            ->method('affectedRows')
            ->willReturn(0);

        arConnectorMap::register(new ilTermsOfServiceDocument(), $documentConnector);

        $this->assertFalse($form->saveObject());
        $this->assertTrue($form->hasTranslatedError());
        $this->assertNotEmpty($form->getTranslatedError());
    }

    public function testFormCanBeSavedWithEnabledServiceWhenNoDocumentsExistButServiceIsAlreadyEnabled() : void
    {
        $tos = $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();

        $tos
            ->method('getStatus')
            ->willReturn(true);

        $tos
            ->expects($this->once())
            ->method('saveStatus');

        $form = $this->getMockBuilder(ilTermsOfServiceSettingsFormGUI::class)
                     ->setConstructorArgs([
                         $tos,
                         '',
                         'save',
                         true
                     ])
                     ->onlyMethods(['checkInput', 'getInput'])
                     ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form
            ->expects($this->exactly(3))
            ->method('getInput')
            ->willReturn(1);

        $form->setCheckInputCalled(true);

        $documentConnector = $this->getMockBuilder(arConnector::class)->getMock();#

        $documentConnector
            ->expects($this->once())
            ->method('affectedRows')
            ->willReturn(0);

        arConnectorMap::register(new ilTermsOfServiceDocument(), $documentConnector);

        $this->assertTrue($form->saveObject());
        $this->assertFalse($form->hasTranslatedError());
        $this->assertEmpty($form->getTranslatedError());
    }
}
