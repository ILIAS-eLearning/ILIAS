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
use ILIAS\Data\Range;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Entity\Entity;
use ILIAS\UI\Component\Listing\Entity\Mapping;
use ILIAS\UI\Component\Listing\Entity\DataRetrieval;
use ILIAS\UI\Component\Listing\Entity\RecordToEntity;

/**
 * ---
 * description: >
 *   Example for rendering a confirmation message box with entity list.
 *
 * expected output: >
 *   ILIAS shows a yellow box with a listing of entities and two buttons.
 *   Clicking the buttons does not do anything.
 * ---
 */
function confirmationWithEntityList(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buttons = [$f->button()->standard('Confirm', '#'), $f->button()->standard('Cancel', '#')];

    $record_to_entity = new class () implements RecordToEntity {
        public function map(UIFactory $ui_factory, mixed $record): Entity
        {
            [$abbreviation, $login, $email, $name, $last_seen, $active] = $record;
            $avatar = $ui_factory->symbol()->avatar()->letter($abbreviation);

            return $ui_factory->entity()->standard($name, $avatar)
                ->withMainDetails(
                    $ui_factory->listing()->property()
                        ->withProperty('login', $login)
                        ->withProperty('mail', $email, false)
                );
        }
    };

    $data = new class () implements DataRetrieval {
        protected array $data = [
            ['jw', 'jimmywilson', 'jimmywilson@example.com', 'Jimmy Wilson', '2022-03-15 13:20:10', true],
            ['eb', 'emilybrown', 'emilybrown@example.com', 'Emily Brown', '2022-03-16 10:45:32', false],
            ['ms', 'michaelscott', 'michaelscott@example.com', 'Michael Scott', '2022-03-14 08:15:05', true],
            ['kj', 'katiejones', 'katiejones@example.com', 'Katie Jones', '2022-03-17 15:30:50', true]
        ];

        public function getEntities(
            Mapping $mapping,
            ?Range $range,
            ?array $additional_parameters
        ): Generator {
            foreach ($this->data as $usr) {
                yield $mapping->map($usr);
            }
        }
    };

    $buttons = [$f->button()->standard('Confirm', '#'), $f->button()->standard('Cancel', '#')];

    return $renderer->render(
        $f->messageBox()
            ->confirmation(
                'Do you really want to delete these items'
            )->withEntityListing(
                $f->listing()->entity()->standard(
                    $record_to_entity
                )->withData($data)
            )->withButtons(
                $buttons
            )
    );
}
