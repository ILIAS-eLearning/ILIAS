<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObject2.php';

/**
 * Class ilObjPDFGeneration
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilObjPDFGeneration extends ilObject2
{
    /**
     *
     */
    protected function initType()
    {
        $this->type = 'pdfg';
    }
}
