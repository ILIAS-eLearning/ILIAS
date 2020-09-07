<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilPDSelectedItemsBlockProvider
 */
interface ilPDSelectedItemsBlockProvider
{
    /**
     * @param array $object_type_white_list An optional array of object_types used for filter purposes
     * @return array An array of repository items, each given as a structured array
     */
    public function getItems($object_type_white_list = array());
}
