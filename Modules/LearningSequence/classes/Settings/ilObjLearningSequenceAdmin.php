<?php

class ilObjLearningSequenceAdmin extends ilObject2
{
    /**
     * @param    integer    reference_id or object_id
     * @param    boolean    treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function initType()
    {
        $this->type = "lsos";
    }
}
