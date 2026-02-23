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

namespace ILIAS\UI\Component\Legacy;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Navigation\Sequence\Segment as NavigationSegment;
use ILIAS\UI\Component\Button;

interface Segment extends Component, NavigationSegment
{
    /**
     * Segments MAY add actions to the sequence.
     * Those actions MUST target the actually displayed contents rather
     * than changing context entirely (i.e. breaking the sequence).
     */
    public function withSegmentActions(Button\Standard ...$actions): static;
}
