<?php

declare(strict_types=1);

namespace ILIAS\UI\Examples\Listing\Entity\Standard;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Listing\Entity\RecordToEntity;
use ILIAS\UI\Component\Listing\Entity\DataRetrieval;
use ILIAS\UI\Component\Entity\Entity;
use ILIAS\UI\Component\Listing\Entity\Mapping;
use ILIAS\Data\Range;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $record_to_entity = new class () implements RecordToEntity {
        public function map(UIFactory $ui_factory, mixed $record): Entity
        {
            list($abbreviation, $login, $email, $name, $last_seen, $active) = $record;
            $avatar = $ui_factory->symbol()->avatar()->letter($abbreviation);
            return $ui_factory->entity()->standard($name, $avatar)
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
    };

    $data = new class () implements DataRetrieval {
        protected $data = [
            ['jw', 'jimmywilson','jimmywilson@example.com', 'Jimmy Wilson', '2022-03-15 13:20:10', true],
            ['eb', 'emilybrown','emilybrown@example.com','Emily Brown','2022-03-16 10:45:32', false],
            ['ms', 'michaelscott','michaelscott@example.com','Michael Scott','2022-03-14 08:15:05', true],
            ['kj', 'katiejones','katiejones@example.com','Katie Jones','2022-03-17 15:30:50',true]
        ];

        public function getEntities(
            Mapping $mapping,
            ?Range $range,
            ?array $additional_parameters
        ): \Generator {
            foreach ($this->data as $usr) {
                yield $mapping->map($usr);
            }
        }
    };

    $listing = $f->listing()->entity()->standard($record_to_entity)
        ->withData($data);

    return $renderer->render($listing);
}
