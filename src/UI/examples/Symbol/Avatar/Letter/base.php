<?php

function base()
{
    $examples = array(
        0  => 'so',
        1  => 'cq',
        2  => 'jl',
        3  => 'mi',
        4  => 'er',
        5  => 'rm',
        6  => 'ob',
        7  => 'li',
        8  => 'qb',
        9  => 'aj',
        10 => 'ot',
        11 => 'il',
        12 => 'mf',
        13 => 'ui',
        14 => 'fa',
        15 => 'rq',
        16 => 'bj',
        17 => 'ur',
        18 => 'hu',
        19 => 'ut',
        20 => 'ea',
        21 => 'bi',
        22 => 'kg',
        23 => 'om',
        24 => 'lt',
        25 => 'gk',
    );

    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    $avatars = [];
    foreach ($examples as $abbreviation) {
        $avatars[] = $f->symbol()->avatar()->letter($abbreviation);
    }

    return $r->render($avatars);
}
