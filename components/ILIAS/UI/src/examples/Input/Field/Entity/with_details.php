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
use InvalidArgumentException;
use ILIAS\UI\Component\Entity\Entity;
use ILIAS\UI\Component\Entity\EntityRetrieval;

/**
 * ---
 * description: >
 *   Entity Input with fully populated Entity components (avatar, main details,
 *   details). Unlike the base example, these are not rendered in the compact
 *   brief layout.
 *
 * expected output: >
 *   A form shows a list of rich entity cards. Submitting the form displays the posted ids.
 * ---
 */
function with_details(): string
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $form = $factory->input()->container()->form()->standard('#', [
        $factory->input()->field()->entity(new EntityInputDetailedRetrieval())
            ->withValue([0, 1, 2]),
    ]);

    if ($request->getMethod() === 'POST') {
        $form = $form->withRequest($request);
        $data = $form->getData();

        return $renderer->render($form)
            . '<pre>' . htmlspecialchars(print_r($data, true), ENT_QUOTES, 'UTF-8') . '</pre>';
    }

    return $renderer->render($form);
}

class EntityInputDetailedRetrieval implements EntityRetrieval
{
    protected array $data = [
        ['jw', 'jimmywilson', 'jimmywilson@example.com', 'Jimmy Wilson', '2022-03-15 13:20:10', true],
        ['eb', 'emilybrown', 'emilybrown@example.com', 'Emily Brown', '2022-03-16 10:45:32', false],
        ['ms', 'michaelscott', 'michaelscott@example.com', 'Michael Scott', '2022-03-14 08:15:05', true],
    ];

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
            if (!isset($this->data[$entity_id])) {
                throw new InvalidArgumentException('Unknown entity id: ' . $entity_id);
            }
            yield $this->mapRecord($ui_factory, $entity_id, $this->data[$entity_id]);
        }
    }

    protected function mapRecord(\ILIAS\UI\Factory $ui_factory, int|string $id, array $record): Entity
    {
        [$abbreviation, $login, $email, $name, $last_seen, $active] = $record;
        $avatar = $ui_factory->symbol()->avatar()->letter($abbreviation);

        return $ui_factory->entity()->standard($id, $name, $avatar)
            ->withMainDetails(
                $ui_factory->listing()->property()
                    ->withProperty('login', $login)
                    ->withProperty('mail', $email, false)
            )
            ->withDetails(
                $ui_factory->listing()->property()
                    ->withItems([
                        ['last seen', $last_seen],
                        ['active', $active ? 'yes' : 'no'],
                    ])
            );
    }
}
