<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

use ILIAS\UI\Component\Component;

interface Table extends Component
{
    public function withTitle(string $title) : Table;

    public function getTitle() : string;
}
