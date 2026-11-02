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

namespace ILIAS\UI\Examples\Listing\Entity\Standard;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Entity\Entity;
use ILIAS\UI\Component\Entity\EntityRetrieval;

/**
 * ---
 * description: >
 *      A component to list many entities. Has multiple columns on very large screens.
 * expected output: >
 *      ILIAS shows a list of entities. If there is a lot of space available, the list will switch to a layout with two
 *      columns.
 * ---
 */
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $listing = $f->listing()->entity()->standard(new DemoEntityRetrieval());

    return $renderer->render($listing);
}

class DemoEntityRetrieval implements EntityRetrieval
{
    protected array $data = [
        ['jw', 'jimmywilson', 'jimmywilson@example.com', 'Jimmy Wilson', '2022-03-15 13:20:10', true],
        ['eb', 'emilybrown', 'emilybrown@example.com', 'Emily Brown', '2022-03-16 10:45:32', false],
        ['ms', 'michaelscott', 'michaelscott@example.com', 'Michael Scott', '2022-03-14 08:15:05', true],
        ['kj', 'katiejones', 'katiejones@example.com', 'Katie Jones', '2022-03-17 15:30:50', true],
    ];

    public function getEntities(
        \ILIAS\UI\Factory $ui_factory,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters,
    ): Generator {
        foreach ($this->data as $index => $record) {
            yield $this->mapRecord($ui_factory, $index, $record);
        }
    }

    public function getEntitiesByIds(
        \ILIAS\UI\Factory $ui_factory,
        Order $order,
        array $entity_ids,
    ): Generator {
        foreach ($entity_ids as $entity_id) {
            if (!isset($this->data[$entity_id])) {
                continue;
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
