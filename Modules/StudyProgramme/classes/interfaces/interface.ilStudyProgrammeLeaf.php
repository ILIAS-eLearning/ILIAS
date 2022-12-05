<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
     */
    public function getId(): int;

    /**
     * Get the ILIAS reference id of the leaf.
     */
    public function getRefId(): ?int;

    /**
     * Create a reference id for this object.
     */
    public function createReference(): int;

    /**
     * Put the leaf object in the repository tree under object identified by
     * $a_ref_id.
     */
    public function putInTree(int $a_ref_id);
}
