<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjBibliographicAdmin
 *
 * @author  Theodor Truffer
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version $Id$
 *
 * @ingroup ModulesBibliographic
 */
class ilObjBibliographicAdmin extends ilObject
{
    /**
     * Constructor
     *
     * @param    integer    reference_id or object_id
     * @param    boolean    treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = 'bibs';
        parent::__construct($a_id, $a_call_by_reference);
    }
}
