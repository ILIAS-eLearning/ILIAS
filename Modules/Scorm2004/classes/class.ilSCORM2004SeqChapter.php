<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Chapter class for SCORM 2004 Editing
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSCORM2004SeqChapter extends ilSCORM2004Chapter
{
    public $tree;

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_slm_object, $a_id = 0)
    {
        parent::__construct($a_slm_object, $a_id);
        $this->setType("seqc");
    }
}
