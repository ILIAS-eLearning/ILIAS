<?php

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

declare(strict_types=1);

namespace ILIAS\MetaData\Services\Paths;

use ILIAS\MetaData\Paths\PathInterface;

interface PathsInterface
{
    /**
     * Path to general > title > string.
     */
    public function title(): PathInterface;

    /**
     * Path to general > description > string.
     */
    public function descriptions(): PathInterface;

    /**
     * Path to general > keyword > string.
     */
    public function keywords(): PathInterface;

    /**
     * Path to lifeCycle > contribute > entity, where the contribute
     * has a role > value with value 'author'.
     */
    public function authors(): PathInterface;

    /**
     * Path to educational > typicalLearningTime > duration, restricted
     * to the first instance of educational.
     */
    public function firstTypicalLearningTime(): PathInterface;

    /**
     * Path to rights > description > string.
     */
    public function copyright(): PathInterface;

    /**
     * Get a builder to construct custom paths.
     */
    public function custom(): BuilderInterface;
}
