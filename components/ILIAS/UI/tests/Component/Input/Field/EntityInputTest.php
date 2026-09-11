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

require_once __DIR__ . '/../../../../../../../vendor/composer/vendor/autoload.php';
require_once __DIR__ . '/../../../Base.php';
require_once __DIR__ . '/CommonFieldRendering.php';

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use Psr\Http\Message\ServerRequestInterface;

class EntityInputTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    public function testEntityFactoryAndWithValueGeneratesHiddenInputs(): void
    {
        $retrieval = $this->getEntityRetrieval();
        $field = $this->getFieldFactory()->entity($retrieval)->withValue([11, 22]);

        $this->assertInstanceOf(C\Input\Field\Entity::class, $field);
        $this->assertCount(2, $field->getGeneratedDynamicInputs());
        $this->assertSame([11, 22], array_values($field->getValue()));
        $this->assertSame($retrieval, $field->getEntityRetrieval());
    }

    public function testEntityWithValueRejectsNonArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getFieldFactory()->entity($this->getEntityRetrieval())->withValue('11');
    }

    public function testEntityFormGetDataReadsIdsFromRequest(): void
    {
        $field_factory = $this->getFieldFactory();
        $form_factory = new I\Input\Container\Form\Factory($field_factory, new I\SignalGenerator());
        $form = $form_factory->standard('https://example.com/go', [
            $field_factory->entity($this->getEntityRetrieval())->withValue([11, 22]),
        ]);

        $entity_input = array_values($form->getInputs())[0];
        $this->assertInstanceOf(C\Input\Field\Entity::class, $entity_input);

        $parent_name = $entity_input->getName();
        $this->assertNotNull($parent_name);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([
            $parent_name => [
                'input_0' => ['11', '22'],
            ],
        ]);

        $data = $form->withRequest($request)->getData();
        $this->assertIsArray($data);
        $this->assertSame(['11', '22'], array_values((array) array_values($data)[0]));
    }

    private function getEntityRetrieval(): C\Entity\EntityRetrieval
    {
        return new class () implements C\Entity\EntityRetrieval {
            public function getEntities(
                ILIAS\UI\Factory $ui_factory,
                ILIAS\Data\Range $range,
                ILIAS\Data\Order $order,
                mixed $additional_viewcontrol_data,
                mixed $filter_data,
                mixed $additional_parameters,
            ): Generator {
                yield from [];
            }

            public function getEntitiesByIds(
                ILIAS\UI\Factory $ui_factory,
                ILIAS\Data\Order $order,
                array $entity_ids,
            ): Generator {
                yield from [];
            }
        };
    }
}
