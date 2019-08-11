<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ilException;

interface AnswerOption
{

    /**
     * @param string $storage_type
     *
     * @return bool
     */
    function satisfy(string $storage_type) : bool;


    /**
     *
     */
    public function create();


    /**
     * @throws ilException
     */
    public function delete();
}