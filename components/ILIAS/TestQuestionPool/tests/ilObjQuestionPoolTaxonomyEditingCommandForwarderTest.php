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

use ILIAS\Taxonomy\Service;

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*
* This test was automatically generated.
*/
class ilObjQuestionPoolTaxonomyEditingCommandForwarderTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilObjQuestionPoolTaxonomyEditingCommandForwarder $object;

    protected function setUp(): void
    {
        parent::setUp();

        $poolOBJ = $this->createMock(ilObjQuestionPool::class);
        $db = $this->createMock(ilDBInterface::class);
        $component_repository = $this->createMock(ilComponentRepository::class);
        $ctrl = $this->createMock(ilCtrl::class);
        $tabs = $this->createMock(ilTabsGUI::class);
        $lng = $this->createMock(ilLanguage::class);
        $taxonomy = $this->createMock(Service::class);

        $this->object = new ilObjQuestionPoolTaxonomyEditingCommandForwarder($poolOBJ, $db, $component_repository, $ctrl, $tabs, $lng, $taxonomy);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilObjQuestionPoolTaxonomyEditingCommandForwarder::class, $this->object);
    }
}