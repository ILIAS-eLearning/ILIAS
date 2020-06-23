<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nhaagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\ViewControl;

use ILIAS\UI\Component\Input\Container\ViewControl as I;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use Psr\Http\Message\ServerRequestInterface;

class ViewControl implements I\ViewControl
{
    use ComponentHelper;

    /**
     * @var array
     */
    protected $controls;

    public function __construct(array $controls)
    {
        $this->controls = $controls;
    }

    public function getInputs() : array
    {
        return [];
    }

    public function withRequest(ServerRequestInterface $request) : I\ViewControl
    {
        return $this;
    }

    public function getData() : array
    {
        return [];
    }
}
