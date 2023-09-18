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

use ILIAS\UI\Component\Input\Field\Checkbox;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Stephan Kergomard
 */
class ilObjectPropertyHeaderActionVisibility implements ilObjectProperty
{
    private const DEFAULT_HEADER_ACTION_VISIBILITY = true;

    private const INPUT_LABEL = 'obj_show_header_actions';

    public function __construct(
        private bool $header_action_visibility = self::DEFAULT_HEADER_ACTION_VISIBILITY
    ) {
    }

    public function getVisibility(): bool
    {
        return $this->header_action_visibility;
    }

    public function toForm(
        \ilLanguage $language,
        FieldFactory $field_factory,
        Refinery $refinery
    ): Checkbox {
        $trafo = $refinery->custom()->transformation(
            function ($v): ilObjectProperty {
                return new ilObjectPropertyHeaderActionVisibility($v);
            }
        );
        return $field_factory->checkbox($language->txt(self::INPUT_LABEL))
            ->withAdditionalTransformation($trafo)
            ->withValue($this->getVisibility());
    }


    public function toLegacyForm(
        \ilLanguage $language
    ): ilCheckboxInputGUI {
        $top_actions_visibility_input = new ilCheckboxInputGUI(
            $language->txt(self::INPUT_LABEL),
            'show_top_actions'
        );
        $top_actions_visibility_input->setValue('1');
        $top_actions_visibility_input->setChecked($this->getVisibility());

        return $top_actions_visibility_input;
    }
}
