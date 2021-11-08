<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */
/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

class ilObjLearningSequenceAdmin extends ilObject2
{
    public function __construct(int $id = 0, bool $call_by_reference = true)
    {
        parent::__construct($id, $call_by_reference);
    }

    protected function initType()
    {
        $this->type = "lsos";
    }
}
