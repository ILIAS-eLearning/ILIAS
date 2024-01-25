<?php

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

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

class ilKSDocumentationGotoLinkTest extends TestCase
{
    protected ilKSDocumentationGotoLink $goto_link;

    protected function setUp(): void
    {
        $this->goto_link = new ilKSDocumentationGotoLink();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilKSDocumentationGotoLink::class, $this->goto_link);
    }

    public function testGenerateGotoLink(): void
    {
        $link = $this->goto_link->generateGotoLink('nodeId', 'skinId', 'styleId');
        $this->assertEquals('nodeId/skinId/styleId', $link);
    }

    public function testRedirectWithGotoLink(): void
    {
        $ctrl_observer = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->onlyMethods([
            'setParameterByClass',
            'setTargetScript',
            'getLinkTargetByClass'
        ])->getMock();


        $ctrl_observer->expects($this->once())
                      ->method('getLinkTargetByClass')
                      ->with([
                          'ilAdministrationGUI',
                          'ilObjStyleSettingsGUI',
                          'ilSystemStyleMainGUI',
                          'ilSystemStyleDocumentationGUI'
                      ], 'entries');

        $params = ['something', 'something', 'something', 'nodeId', 'skinId', 'styleId'];
        $this->goto_link->generateRedirectURL($ctrl_observer, 1, $params[3], $params[4], $params[5]);
    }
}
