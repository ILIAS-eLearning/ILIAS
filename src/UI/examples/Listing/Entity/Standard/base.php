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

    $data = [1,2,3,4];

    $mapping = new class () extends EntityListing\EntityFactory {
        public function get(
            UIFactory $ui_factory,
            mixed $data
        ): \Generator {
            foreach ($data as $obj) {
                yield $ui_factory->entity()->standard('something', 'secondary');
            }
        }
    };


    $listing = $f->listing()->entity()->standard($mapping)
        ->withData($data);

    return $renderer->render($listing);
}
