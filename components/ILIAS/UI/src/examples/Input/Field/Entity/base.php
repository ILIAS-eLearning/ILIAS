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

namespace ILIAS\UI\examples\Input\Field\Entity;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Entity\Entity;
use ILIAS\UI\Component\Entity\EntityRetrieval;

/**
 * ---
 * description: >
 *   Example of the Entity Input in a Standard Form with compact (brief)
 *   entities: only primary identifier, empty secondary identifier, no further
 *   sections. The field posts entity ids and is not operated by the user.
 *
 * expected output: >
 *   A form shows a compact list of entity titles. Submitting the form displays the posted ids.
 * ---
 */
function base(): string
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $form = $factory->input()->container()->form()->standard('#', [
        $factory->input()->field()->entity(new EntityInputExampleRetrieval())
            ->withValue([0, 1]),
    ]);

    if ($request->getMethod() === 'POST') {
        $form = $form->withRequest($request);
        $data = $form->getData();

        return $renderer->render($form)
            . '<pre>' . htmlspecialchars(print_r($data, true), ENT_QUOTES, 'UTF-8') . '</pre>';
    }

    return $renderer->render($form);
}

class EntityInputExampleRetrieval implements EntityRetrieval
{
    public function getEntities(
        \ILIAS\UI\Factory $ui_factory,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters,
    ): Generator {
        yield from [];
    }

    public function getEntitiesByIds(
        \ILIAS\UI\Factory $ui_factory,
        Order $order,
        array $entity_ids,
    ): Generator {
        foreach ($entity_ids as $entity_id) {
            yield $ui_factory->entity()->standard($entity_id, 'Entity ' . $entity_id, '');
        }
    }
}
