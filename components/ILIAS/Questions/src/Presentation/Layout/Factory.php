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

namespace ILIAS\Questions\Presentation\Layout;

use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\HTTP\Services as HttpService;
use ILIAS\Language\Language;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\URLBuilder;

class Factory
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly HttpService $http,
        private readonly Language $lng
    ) {
    }

    public function getEditOverview(
        Environment $environment,
        URLBuilder $target_to_edit_basic_answer_form_properties
    ): EditOverview {
        return new EditOverview(
            $environment,
            $target_to_edit_basic_answer_form_properties
        );
    }

    public function getEditForm(
        Input|InputsBuilder $main_section_inputs,
        URLBuilder $default_form_action,
        ?URLBuilder $back_form_action,
        bool $is_final_step
    ): EditForm {
        return new EditForm(
            $this->ui_factory,
            $this->lng,
            $main_section_inputs,
            $default_form_action,
            $back_form_action,
            $is_final_step
        );
    }

    public function getAsync(
        InterruptiveModal|RoundTripModal|MessageBox $content
    ): Async {
        return new Async(
            $this->http,
            $content
        );
    }

    public function getSessionBasedInputsBuilder(
        string $storage_key,
        Transformation $to_inputs
    ): InputsBuilderSession {
        return new InputsBuilderSession(
            $storage_key,
            $to_inputs
        );
    }

    public function getUIFactory(): UIFactory
    {
        return $this->ui_factory;
    }
}
