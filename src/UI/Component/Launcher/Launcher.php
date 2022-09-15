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

namespace ILIAS\UI\Component\Launcher;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Chart\ProgressMeter;
use ILIAS\UI\Component\Symbol\Icon;
use ILIAS\UI\Component\Input\Container\Form\Form;

interface Launcher extends Component, Form
{
    public function withDescription(string $description) : self;

    public function withInputs(Field $fields) : self;

    /**
     * @param Icon | ProgressMeter $status
     */
    public function withStatus(Component $status): self;

    public function withButtonLabel(string $label, bool $launchable = true): self;

    /**
     * @inheritdoc
     *
     * If not Inputs have been configured, the method will always return true.
     */
    public function getData();
}
