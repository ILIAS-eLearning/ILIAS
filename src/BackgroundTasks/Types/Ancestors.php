<?php

namespace ILIAS\BackgroundTasks\Types;

interface Ancestors
{

    /**
     * returns the hierarchy of this type. E.g. ["AbstractValue", "ScalarValue", "IntegerValue", "UserIdValue"]
     *
     * @return Type[]
     */
    public function getAncestors();
}