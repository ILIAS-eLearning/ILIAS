<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Avatar\Letter;

function base()
{
    $examples = array(
        1 => 'om',
        2 => 'gk',
        3 => 'bj',
        4 => 'ea',
        5 => 'mf',
        6 => 'ob',
        7 => 'bi',
        8 => 'hu',
        9 => 'fa',
        10 => 'so',
        11 => 'il',
        12 => 'ut',
        13 => 'ur',
        14 => 'lt',
        15 => 'kg',
        16 => 'jl',
        17 => 'qb',
        18 => 'rq',
        19 => 'ot',
        20 => 'cq',
        21 => 'rm',
        22 => 'aj',
        23 => 'li',
        24 => 'er',
        25 => 'ui',
        26 => 'mi',
    );

    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    $avatars = [];
    foreach ($examples as $abbreviation) {
        $letter = $f->symbol()->avatar()->letter($abbreviation);
        $avatars[] = $letter;
    }

    return $r->render($avatars);
}
