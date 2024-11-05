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

use ILIAS\UI\Component\Component;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\ViewControl\ViewControl as ViewControlContainer;
use ILIAS\UI\Component\Button\Standard as StdButton;

interface Sequence extends Component
{
    /**
     * Rendering the sequence must be done using the current request:
     * it (the request) will carry parameters determining e.g. the sequence's
     * position and other 'moving' parts
     */
    public function withRequest(ServerRequestInterface $request): static;

    /**
     * You may add view controls to the sequences's player that alter the way, order
     * or focus in which the segements are presented.
     * You probably SHOULD NOT use a pagination here, though.
     */
    public function withViewControls(ViewControlContainer $viewcontrols): static;

    /**
     * Add additional actions to the Sequence. These actions should target global/outer context,
     * or be available regardless of the currently displayd segment.
     * See Segment::withActions, too.
     */
    public function withActions(StdButton ...$actions): static;

    /**
     * The Sequence comes with a storage to keep ViewControl-settings throughout requests.
     * Set an Id to enable the storage and identify the distinct sequence.
     */
    public function withId(string $id): static;
}
