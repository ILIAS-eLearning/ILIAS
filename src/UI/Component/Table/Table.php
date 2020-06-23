<?php
/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

interface Table extends \ILIAS\UI\Component\Component
{
    public function withTitle(string $title) : Table;
    public function getTitle() : string;
}
