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
 * Get members of a certain source. While, e.g., groups and courses share the
 * common participant-mechanism, roles and orgus have differnt concepts.
 * This is to provide a facade for StudyProgrammes.
 */
interface ilStudyProgrammeMembershipSourceReader
{
    /**
     * @return int[]
     */
    public function getMemberIds(): array;
}
