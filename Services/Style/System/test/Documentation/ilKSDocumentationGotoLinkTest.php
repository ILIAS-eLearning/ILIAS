<?php

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

class ilKSDocumentationGotoLinkTest extends TestCase
{
    protected ilKSDocumentationGotoLink $goto_link;

    protected function setUp() : void
    {
        $this->goto_link = new ilKSDocumentationGotoLink();
    }

    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilKSDocumentationGotoLink::class, $this->goto_link);
    }

    public function testGenerateGotoLink() : void
    {
        $link = $this->goto_link->generateGotoLink('nodeId', 'skinId', 'styleId');
        $this->assertEquals('_nodeId_skinId_styleId', $link);
    }

    public function testRedirectWithGotoLink() : void
    {
        $ctrl_observer = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->onlyMethods([
            'setParameterByClass',
            'setTargetScript',
            'redirectByClass'
        ])->getMock();

        $ctrl_observer->expects($this->once())
                      ->method('redirectByClass')
                      ->with([
                          'ilAdministrationGUI',
                          'ilObjStyleSettingsGUI',
                          'FilSystemStyleMainGUI',
                          'ilSystemStyleDocumentationGUI'
                      ], 'entries');

        $params = ['something', 'something', 'nodeId', 'skinId', 'styleId'];
        $this->goto_link->redirectWithGotoLink('ref_id', $params, $ctrl_observer);
    }
}
