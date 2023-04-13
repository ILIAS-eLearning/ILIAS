<?php

declare(strict_types=1);

namespace ILIAS\UI\Examples\Listing\Entity\Standard;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component\Listing\Entity as EntityListing;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $data = [
        ['jw', 'jimmywilson','jimmywilson@example.com', 'Jimmy Wilson', '2022-03-15 13:20:10', true],
        ['eb', 'emilybrown','emilybrown@example.com','Emily Brown','2022-03-16 10:45:32', false],
        ['ms', 'michaelscott','michaelscott@example.com','Michael Scott','2022-03-14 08:15:05', true],
        ['kj', 'katiejones','katiejones@example.com','Katie Jones','2022-03-17 15:30:50',true]
    ];

    $mapping = new class () extends EntityListing\EntityFactory {
        public function get(
            UIFactory $ui_factory,
            mixed $data
        ): \Generator {
            foreach ($data as $usr) {
                list($abbreviation, $login, $email, $name, $last_seen, $active) = $usr;
                $avatar = $ui_factory->symbol()->avatar()->letter($abbreviation);
                yield $ui_factory->entity()->standard($name, $avatar)
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
    };

    $listing = $f->listing()->entity()->standard($mapping)
        ->withData($data);

    return $renderer->render($listing);
}
