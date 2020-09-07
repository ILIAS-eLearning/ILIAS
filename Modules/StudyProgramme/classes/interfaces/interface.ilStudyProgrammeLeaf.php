<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * The interface a class has to fullfill if it should be used as leaf in a
 * program.
 *
 * ATTENTION: This serves documentary purpose atm. These are the methods on the
 * leaf objects that are really used by the StudyProgramme. Maybe some day this
 * could be tagged on ilCourseReference and other objects.
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */

interface ilStudyProgrammeLeaf
{
    /**
     * Get the ILIAS object id of the leaf.
     *
     * @return int
     */
    public function getId();

    /**
     * Get the ILIAS reference id of the leaf.
     *
     * @return int | null
     */
    public function getRefId();
    
    /**
     * Create a reference id for this object.
     */
    public function createReference();
    
    /**
     * Put the leaf object in the repository tree under object identified by
     * $a_ref_id.
     *
     * @param int	$a_ref_id
     */
    public function putInTree($a_ref_id);
}
