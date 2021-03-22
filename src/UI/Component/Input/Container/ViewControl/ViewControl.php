<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\ViewControl;

use ILIAS\UI\Component\Component;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This describes a View Control Container.
 */
interface ViewControl extends Component
{
    /**
     * Get the contained controls.
     *
     * @return array<string,\ILIAS\UI\Component\Input\Field\Input>
     */
    public function getInputs() : array;

    public function withRequest(ServerRequestInterface $request) : ViewControl;

    /**
     * @return array<string,mixed>
     */
    public function getData() : array;
}
