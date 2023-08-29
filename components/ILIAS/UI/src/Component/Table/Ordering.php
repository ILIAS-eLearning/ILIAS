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

namespace ILIAS\UI\Component\Table;

use ILIAS\UI\Component\Input\ViewControl\ViewControl;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Data\Order;
use ILIAS\Data\Range;

/**
 * This describes a Table to specify the order of its data (rows).
 */
interface Ordering extends Table
{
    /**
     * @param array<string, Action\Action>    $actions
     */
    public function withActions(array $actions): static;

    public function withRequest(ServerRequestInterface $request): static;
}
