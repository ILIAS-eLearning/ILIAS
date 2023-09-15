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

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Stephan Kergomard
 */
class ilObjectPropertyTitleAndDescription implements ilObjectProperty
{
    private const TITLE_LABEL = 'title';
    private const DESCRIPTION_LABEL = 'description';
    private const GROUP_LABEL = 'title_and_description';

    public function __construct(
        private string $title = '',
        private string $long_description = ''
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return substr($this->long_description, 0, ilObject::DESC_LENGTH);
    }

    public function getLongDescription(): string
    {
        return $this->long_description;
    }

    public function toForm(
        \ilLanguage $language,
        FieldFactory $field_factory,
        Refinery $refinery
    ): FormInput {
        $trafo = $refinery->custom()->transformation(
            function ($vs): ilObjectProperty {
                list($title, $long_description) = $vs;
                return new ilObjectPropertyTitleAndDescription(
                    $title,
                    $long_description
                );
            }
        );

        $title_input = $field_factory->text($language->txt(self::TITLE_LABEL))
            ->withMaxLength(ilObject::TITLE_LENGTH)
            ->withRequired(true)
            ->withValue($this->title);
        $description_input = $field_factory->textarea($language->txt(self::DESCRIPTION_LABEL))
            ->withMaxLimit(ilObject::LONG_DESC_LENGTH)
            ->withValue($this->long_description);
        return $field_factory->group([$title_input, $description_input], self::GROUP_LABEL)
            ->withAdditionalTransformation($trafo);
    }
}
