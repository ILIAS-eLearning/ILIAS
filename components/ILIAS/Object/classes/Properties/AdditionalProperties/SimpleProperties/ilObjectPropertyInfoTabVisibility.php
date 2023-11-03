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
class ilObjectPropertyInfoTabVisibility implements ilObjectProperty
{
    private const DEFAULT_INFO_TAB_VISIBILITY = true;
    private const INPUT_LABEL = 'obj_tool_setting_info_tab';

    public function __construct(
        private bool $info_tab_visibility = self::DEFAULT_INFO_TAB_VISIBILITY
    ) {
    }

    public function getVisibility(): bool
    {
        return $this->info_tab_visibility;
    }

    public function toForm(
        \ilLanguage $language,
        FieldFactory $field_factory,
        Refinery $refinery
    ): Checkbox {
        $trafo = $refinery->custom()->transformation(
            function ($v): ilObjectProperty {
                return new ilObjectPropertyInfoTabVisibility($v);
            }
        );

        return $field_factory->checkbox($language->txt(self::INPUT_LABEL))
            ->withAdditionalTransformation($trafo)
            ->withValue($this->getVisibility());
    }
}
