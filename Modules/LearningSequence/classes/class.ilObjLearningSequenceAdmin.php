<?php declare(strict_types=1);

/**
 * Class ilObjLearningSequenceAdmin
 *
 */
class ilObjLearningSequenceAdmin extends ilObject2
{
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function initType()
    {
        $this->type = "lsos";
    }
}
