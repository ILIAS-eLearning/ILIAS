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

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component as C;

class Factory implements C\Input\Factory
{
    protected SignalGeneratorInterface $signal_generator;
    protected Field\Factory $field_factory;
    protected Container\Factory $container_factory;
    protected ViewControl\Factory $control_factory;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        Field\Factory $field_factory,
        Container\Factory $container_factory,
        ViewControl\Factory $control_factory
    ) {
        $this->signal_generator = $signal_generator;
        $this->field_factory = $field_factory;
        $this->container_factory = $container_factory;
        $this->control_factory = $control_factory;
    }

    public function field(): Field\Factory
    {
        return $this->field_factory;
    }

    public function container(): Container\Factory
    {
        return $this->container_factory;
    }

    public function viewControl(): ViewControl\Factory
    {
        return $this->control_factory;
    }
}
