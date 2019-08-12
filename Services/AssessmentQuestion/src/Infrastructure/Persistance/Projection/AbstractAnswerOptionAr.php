<?php
namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ActiveRecord;
use ilException;

abstract class AbstractAnswerOptionAr extends AbstractProjectionAr implements AnswerOption
{
    /**
     * @param string $storage_type
     *
     * @return bool
     */
    abstract function satisfy(string $storage_type):bool;
}