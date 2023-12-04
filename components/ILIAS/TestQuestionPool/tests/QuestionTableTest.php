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

        $ui_factory = $this->createMock(UIFactory::class);
        $ui_renderer = $this->createMock(UIRenderer::class);
        $data_factory = $this->createMock(DataFactory::class);
        $url_builder = $this->createMock(URLBuilder::class);
        $action_parameter_token = $this->createMock(URLBuilderToken::class);
        $row_id_token = $this->createMock(URLBuilderToken::class);
        $db = $this->createMock(ilDBInterface::class);
        $lng = $this->createMock(ilLanguage::class);
        $component_repository = $this->createMock(ilComponentRepository::class);
        $rbac = $this->createMock(ilRbacSystem::class);
        $taxonomy = $this->createMock(TaxonomyService::class);
        $parent_obj_id = 0;
        $request_ref_id = 0;

        $this->object = new QuestionTable(
            $ui_factory,
            $ui_renderer,
            $data_factory,
            $url_builder,
            $action_parameter_token,
            $row_id_token,
            $db,
            $lng,
            $component_repository,
            $rbac,
            $taxonomy,
            $parent_obj_id,
            $request_ref_id
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(QuestionTable::class, $this->object);
    }
}