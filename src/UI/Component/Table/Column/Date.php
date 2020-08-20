<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nhaagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table\Column;

interface Date extends Column
{
    public function getFormat() : \ILIAS\Data\DateFormat\DateFormat;
}
