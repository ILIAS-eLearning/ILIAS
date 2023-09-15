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

class Actions
{
    protected ButtonFactory $button_factory;
    protected ModalFactory $modal_factory;
    protected LinkProvider $link_provider;

    public function __construct(
        LinkProvider $link_provider,
        ButtonFactory $button_factory,
        ModalFactory $modal_factory
    ) {
        $this->link_provider = $link_provider;
        $this->button_factory = $button_factory;
        $this->modal_factory = $modal_factory;
    }

    public function getModal(): ModalFactory
    {
        return $this->modal_factory;
    }

    public function getButton(): ButtonFactory
    {
        return $this->button_factory;
    }

    public function getLink(): LinkProvider
    {
        return $this->link_provider;
    }
}
