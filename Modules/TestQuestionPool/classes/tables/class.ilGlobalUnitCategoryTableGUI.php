<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/tables/class.ilUnitCategoryTableGUI.php';

/**
 * Class ilGlobalUnitCategoryTableGUI
 */
class ilGlobalUnitCategoryTableGUI extends ilUnitCategoryTableGUI
{
    /**
     *
     */
    protected function populateTitle()
    {
        $this->setTitle($this->lng->txt('un_global_units') . ': ' . $this->lng->txt('categories'));
    }
}
