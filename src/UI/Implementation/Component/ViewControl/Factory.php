<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component\ViewControl as VC;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Component;

class Factory implements VC\Factory
{
    protected SignalGeneratorInterface $signal_generator;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function mode(array $labelled_actions, string $aria_label) : VC\Mode
    {
        return new Mode($labelled_actions, $aria_label);
    }

    /**
     * @inheritdoc
     */
    public function section(Button $previous_action, Component $button, Button $next_action) : VC\Section
    {
        return new Section($previous_action, $button, $next_action);
    }

    /**
     * @inheritdoc
     */
    public function sortation(array $options) : VC\Sortation
    {
        return new Sortation($options, $this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function pagination() : VC\Pagination
    {
        return new Pagination($this->signal_generator);
    }
}
