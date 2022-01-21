<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\UI\Component\Tree\Node\Bylined as BylinedInterface;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class Bylined extends Simple implements BylinedInterface
{
    private string $byline;

    public function __construct(string $label, string $byline, Icon $icon = null)
    {
        parent::__construct($label, $icon);

        $this->byline = $byline;
    }

    public function getByline() : string
    {
        return $this->byline;
    }
}
