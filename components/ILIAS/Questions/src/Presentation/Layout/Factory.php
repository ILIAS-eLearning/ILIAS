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

use ILIAS\Questions\Presentation\Definitions\CarryWrapper;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Definitions\Leaf;
use ILIAS\Data\URI;
use ILIAS\HTTP\Services as HttpService;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Language\Language;
use ILIAS\Refinery\Custom\Transformation as CustomTransformation;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Input\Input;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;

class Factory
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly Refinery $refinery,
        private readonly HttpService $http,
        private readonly Language $lng
    ) {
    }

    public function getEditOverview(
        Environment $environment,
        URI $uri_to_edit_basic_answer_form_properties
    ): EditOverview {
        return new EditOverview(
            $this->ui_factory,
            $this->lng,
            $this->http->request(),
            $environment,
            $uri_to_edit_basic_answer_form_properties
        );
    }

    /**
     * @param URLBuilder $url_builder The url_builder MUST have the step set,
     * to which the form shall be sent.
     */
    public function getEditForm(
        URI $form_target_uri,
        Section $main_section_inputs,
        bool $is_final_step,
        ?Group $carry_inputs = null
    ): EditForm {
        return new EditForm(
            $this->ui_factory,
            $this->lng,
            $form_target_uri,
            $main_section_inputs,
            $is_final_step,
            $carry_inputs
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

    /**
     * @param CustomTransformation $to_inputs This MUST return an `array` of
     * inputs that will then be used in the form. The transformation will receive
     * the string produced by `$to_carry` as parameter.
     * @param Input|array|null $inputs If you provide inputs it is assumed that no
     * carry is present and you want to use them directly.
     */
    public function getInputsBuilder(
        CustomTransformation $to_inputs,
        Input|array|null $inputs = null
    ): InputsBuilder {
        return new InputsBuilder(
            $this->refinery,
            $to_inputs,
            $inputs
        );
    }

    public function getCarrySectionData(
        ArrayBasedRequestWrapper $post_wrapper
    ): CarryWrapper {
        return new CarryWrapper(
            array_reduce(
                $post_wrapper->keys(),
                function (array $c, string $v) use ($post_wrapper): array {
                    $value = new Leaf(
                        $post_wrapper->retrieve($v, $this->refinery->identity())
                    );
                    foreach (array_reverse(explode('/', $v)) as $path_element) {
                        $value = [$path_element => $value];
                    }
                    return array_merge_recursive($c, $value);
                },
                []
            )['form'][EditForm::CARRY_SECTION_NAME] ?? []
        );
    }
}
