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

namespace ILIAS\UI\Component\Input\Container\ViewControl;

use ILIAS\UI\Component\Component;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * This describes a View Control Container.
 */
interface ViewControl extends Component
{
    /**
     * Get the contained controls.
     *
     * @return array<string,Input>
     */
    public function getInputs(): array;

    public function withRequest(ServerRequestInterface $request);

    /**
     * @return array<string,mixed>
     */
    public function getData(): array;
}
