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

namespace ILIAS\UI\examples\MessageBox\Confirmation;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Entity\Entity;
use ILIAS\UI\Component\Entity\EntityRetrieval;

/**
 * ---
 * expected output: >
 *   ILIAS shows a confirmation message box with an entity listing.
 * ---
 */
function confirmationWithEntityList()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buttons = [$f->button()->standard('Confirm', '#'), $f->button()->standard('Cancel', '#')];

    return $renderer->render(
        $f->messageBox()->confirmation('some message box')
            ->withButtons($buttons)
            ->withEntityListing($f->listing()->entity()->standard(new DemoEntityRetrieval()))
    );
}

class DemoEntityRetrieval implements EntityRetrieval
{
    protected array $data = [
        ['jw', 'jimmywilson', 'jimmywilson@example.com', 'Jimmy Wilson'],
        ['eb', 'emilybrown', 'emilybrown@example.com', 'Emily Brown'],
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
        [$abbreviation, $login, $email, $name] = $record;
        $avatar = $ui_factory->symbol()->avatar()->letter($abbreviation);

        return $ui_factory->entity()->standard($id, $name, $avatar)
            ->withMainDetails(
                $ui_factory->listing()->property()
                    ->withProperty('login', $login)
                    ->withProperty('mail', $email, false)
            );
    }
}
