<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilStudyProgrammeContainerObjectMock
 *
 * This object is required to make the ilObjCourseReferenceListGUI work correctly.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilStudyProgrammeContainerObjectMock
{
    /**
     * @var ilObject
     */
    public $object;
    
    public function __construct(ilObject $object)
    {
        $this->object = $object;
    }
}
