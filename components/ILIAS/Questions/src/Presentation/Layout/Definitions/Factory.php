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

namespace ILIAS\Questions\Presentation\Layout\Definitions;

use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\Ordering as OrderingTable;

class Factory
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly Language $lng
    ) {
    }

    public function getEditOverview(
        Editability $editability,
        URLBuilder $url_builder,
        DataTable|OrderingTable $answer_elements_table,
        Properties $answer_form_properties
    ): EditOverview {
        return new EditOverview(
            $this->ui_factory,
            $this->lng,
            $editability,
            $url_builder,
            $answer_elements_table,
            $answer_form_properties
        );
    }

    public function getEditForm(
        URLBuilder $url_builder,
        Section $main_section_inputs,
        bool $is_final_step,
        ?Group $carry_inputs = null
    ): EditForm {
        return new EditForm(
            $this->ui_factory->input()->container()->form(),
            $this->lng,
            $url_builder,
            $main_section_inputs,
            $is_final_step,
            $carry_inputs
        );
    }

    public function getCarrySectionData(
        ArrayBasedRequestWrapper $post_wrapper,
        Refinery $refinery
    ): CarryWrapper {
        return new CarryWrapper(
            array_reduce(
                $post_wrapper->keys(),
                function (array $c, string $v) use ($post_wrapper, $refinery): array {
                    $value = new Leaf(
                        $post_wrapper->retrieve($v, $refinery->identity())
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
