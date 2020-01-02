<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclSelectionRecordRepresentation
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class ilDclSelectionRecordRepresentation extends ilDclBaseRecordRepresentation
{

    // those should be overwritten by subclasses
    const PROP_SELECTION_TYPE = '';
    const PROP_SELECTION_OPTIONS = '';


    /**
     * @param bool $link
     *
     * @return string
     */
    public function getHTML($link = true)
    {
        $record_field_value = $this->getRecordField()->getValue();
        $values = ilDclSelectionOption::getValues($this->getField()->getId(), $record_field_value);

        return is_array($values) ? implode('<br>', $values) : $values;
    }
}
