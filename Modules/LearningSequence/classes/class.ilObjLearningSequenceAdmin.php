<?php declare(strict_types=1);

/**
 * Class ilObjLearningSequenceAdmin
 *
 */
class ilObjLearningSequenceAdmin extends ilObject2
{
    const SETTING_POLL_INTERVAL = 'lso_polling_interval';
    const POLL_INTERVAL_DEFAULT = 10; //in seconds

    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function initType()
    {
        $this->type = "lsos";
    }
}
