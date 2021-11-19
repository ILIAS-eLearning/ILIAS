<?php declare(strict_types=1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilStudyProgrammeContainerObjectMock
 *
 * This object is required to make the ilObjCourseReferenceListGUI work correctly.
 */
class ilStudyProgrammeContainerObjectMock
{
    public ilObject $object;
    
    public function __construct(ilObject $object)
    {
        $this->object = $object;
    }
}
