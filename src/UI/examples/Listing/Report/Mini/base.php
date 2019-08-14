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
        'Third Item Comes as Component' => $f->legacy('Item 3'),
        'Fourth Item Comes as Component' => $f->legacy('Item 4')
    ];

    $mini = $f->listing()->report()->mini($items);

    return $r->render($mini);
}