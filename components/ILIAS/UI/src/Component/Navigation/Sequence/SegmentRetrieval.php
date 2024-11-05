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

namespace ILIAS\UI\Component\Navigation\Sequence;

use Psr\Http\Message\ServerRequestInterface;

/**
 * The SegmentRetrieval defines available stops for the sequence and builds
 * it's segments.
 */
interface SegmentRetrieval
{
    /**
     * Provides available positions as an iterable list of (sets of) information
     * necessary to identify and factor a segment.
     * Data provided by a position for a certain index is relayed to getSegment.
     */
    public function getAllPositions(
        ServerRequestInterface $request,
        mixed $viewcontrol_values,
        mixed $filter_values
    ): array;

    /**
     * Receives position data (provided by getAllPositions) and builds a segment.
     */
    public function getSegment(
        ServerRequestInterface $request,
        mixed $position_data,
        mixed $viewcontrol_values,
        mixed $filter_values
    ): Segment;
}
