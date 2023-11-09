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

namespace ILIAS\MetaData\Editor\Full\Services\Actions;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Button\Standard as StandardButton;
use ILIAS\UI\Component\Button\Shy as ShyButton;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Elements\ElementInterface;

class ButtonFactory
{
    protected UIFactory $factory;
    protected PresenterInterface $presenter;

    public function __construct(
        UIFactory $factory,
        PresenterInterface $presenter
    ) {
        $this->factory = $factory;
        $this->presenter = $presenter;
    }

    public function delete(
        FlexibleSignal $signal,
        bool $is_shy = false,
        bool $long_text = false
    ): Button {
        $label = $this->presenter->utilities()->txt(
            $long_text ? 'meta_delete_this_element' : 'delete'
        );
        if ($is_shy) {
            return $this->getShyButton($label, $signal);
        }
        return $this->getStandardButton($label, $signal);
    }

    public function update(
        FlexibleSignal $signal
    ): ShyButton {
        $label = $this->presenter->utilities()->txt('edit');
        return $this->getShyButton($label, $signal);
    }

    public function create(
        FlexibleSignal $signal,
        ElementInterface $element,
        bool $is_shy = false
    ): Button {
        $label = $this->presenter->utilities()->txtFill(
            'meta_add_element',
            $this->presenter->elements()->name($element)
        );
        if ($is_shy) {
            return $this->getShyButton($label, $signal);
        }
        return $this->getStandardButton($label, $signal);
    }

    protected function getShyButton(
        string $label,
        FlexibleSignal $signal
    ): ShyButton {
        return $this->factory->button()->shy($label, $signal->get());
    }

    protected function getStandardButton(
        string $label,
        FlexibleSignal $signal
    ): StandardButton {
        return $this->factory->button()->standard($label, $signal->get());
    }
}
