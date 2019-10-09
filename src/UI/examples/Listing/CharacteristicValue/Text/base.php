<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

function base()
{
    global $DIC; /* @var \ILIAS\DI\Container $DIC */
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    $items = [
        'Any Label for the First Item' => 'Item 1',
        'Another Label for the Second Item' => 'Item 2',
        'Third Item Label' => 'Item 3',
        'Fourth Item Label' => 'Item 4'
    ];

    $listing = $f->listing()->characteristicValue()->text($items);

    return $r->render($listing);
}
