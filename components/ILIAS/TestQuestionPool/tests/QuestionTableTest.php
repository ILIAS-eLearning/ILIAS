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

use ILIAS\TestQuestionPool\Questions\Presentation\QuestionTable;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Taxonomy\DomainService as TaxonomyService;

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*
* This test was automatically generated.
*/
class QuestionTableTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private QuestionTable $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new QuestionTable(
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class),
            $this->createMock(DataFactory::class),
            $this->createMock(ILIAS\Refinery\Factory::class),
            $this->createMock(URLBuilder::class),
            $this->createMock(URLBuilderToken::class),
            $this->createMock(URLBuilderToken::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilComponentRepository::class),
            $this->createMock(ilRbacSystem::class),
            $this->createMock(ilObjUser::class),
            $this->createMock(TaxonomyService::class),
            $this->createMock(ILIAS\Notes\Service::class),
            0,
            0
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(QuestionTable::class, $this->object);
    }
}
