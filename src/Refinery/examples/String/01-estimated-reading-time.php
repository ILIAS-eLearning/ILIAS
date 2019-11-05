<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

function estimatedReadingTime()
{
    global $DIC;

    $refinery = $DIC->refinery();

    $transformation = $refinery->string()->estimatedReadingTime(true);

    $result = $transformation->transform('Lorem ipsum dolor sit amet, consetetur sadipscing elitr,  <img src="#"/> ...');

    return assert(1 === $result);
}
