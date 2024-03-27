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

namespace ILIAS\UI\Implementation\Component\Input\Container\ViewControl;

use ILIAS\UI\Component\Input\Container\ViewControl as V;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\UI\Implementation\Component\Input\ViewControl\Factory as ViewControlFactory;
use ILIAS\Data\Factory as DataFactory;

/**
 * Factory for the View Control Containers
 */
class Factory implements V\Factory
{
    protected SignalGeneratorInterface $signal_generator;
    protected ViewControlFactory $view_control_factory;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        ViewControlFactory $view_control_factory
    ) {
        $this->signal_generator = $signal_generator;
        $this->view_control_factory = $view_control_factory;
    }

    public function standard(array $controls): V\Standard
    {
        return new Standard(
            $this->signal_generator,
            new Input\FormInputNameSource(),
            $this->view_control_factory,
            $controls
        );
    }
}
