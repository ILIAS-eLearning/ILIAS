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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Component\Input\Container\Form\FormInput;

class ilMDSettingsModalService
{
    protected UIFactory $ui_factory;

    public function __construct(UIFactory $ui_factory)
    {
        $this->ui_factory = $ui_factory;
    }

    public function modalWithForm(
        string $modal_title,
        string $post_url,
        FormInput ...$inputs
    ): RoundTrip {
        return $this->ui_factory->modal()->roundtrip(
            $modal_title,
            null,
            $inputs,
            $post_url
        );
    }
}
