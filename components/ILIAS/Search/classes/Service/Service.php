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

namespace ILIAS\Search\Service;

use ILIAS\DI\Container;
use ILIAS\Search\Presentation\Service\Service as PresentationService;
use ILIAS\Search\GUI\Service\Service as GUIService;

class Service
{
    protected PresentationService $presentation;
    protected GUIService $gui;

    public function __construct(
        protected Container $dic
    ) {
        $this->dic->language()->loadLanguageModule('search');

        $this->presentation = new PresentationService(
            $this->dic
        );
        $this->gui = new GUIService(
            $this->dic,
            $this->presentation
        );
    }

    public function dic(): Container
    {
        return $this->dic;
    }

    public function presentation(): PresentationService
    {
        return $this->presentation;
    }

    public function gui(): GUIService
    {
        return $this->gui;
    }
}
