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

namespace ILIAS\MetaData\Editor\Full\Services;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\Data\URI;
use ILIAS\MetaData\Editor\Full\Services\Actions\Actions;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Full\Services\Inputs\InputFactory;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Editor\Dictionary\DictionaryInterface as EditorDictionary;
use ILIAS\MetaData\Editor\Full\Services\Actions\LinkProvider;

class FormFactory
{
    protected UIFactory $ui_factory;
    protected LinkProvider $link_provider;
    protected InputFactory $input_factory;
    protected EditorDictionary $editor_dictionary;
    protected NavigatorFactoryInterface $navigator_factory;

    public function __construct(
        UIFactory $ui_factory,
        LinkProvider $link_provider,
        InputFactory $input_factory,
        EditorDictionary $editor_dictionary,
        NavigatorFactoryInterface $navigator_factory
    ) {
        $this->ui_factory = $ui_factory;
        $this->link_provider = $link_provider;
        $this->input_factory = $input_factory;
        $this->editor_dictionary = $editor_dictionary;
        $this->navigator_factory = $navigator_factory;
    }

    public function getUpdateForm(
        PathInterface $base_path,
        ElementInterface $element,
        bool $with_title = true
    ): StandardForm {
        $link = $this->link_provider->update($base_path, $element);

        return $this->getFormForElement(
            $base_path,
            $element,
            $element,
            $link,
            $with_title,
            false
        );
    }

    public function getCreateForm(
        PathInterface $base_path,
        ElementInterface $element,
        bool $with_title = true
    ): StandardForm {
        $link = $this->link_provider->create($base_path, $element);
        $editor_tag = $this->editor_dictionary->tagForElement($element);
        $context_element = $element;
        if ($created_with = $editor_tag?->createdWith()) {
            $element = $this->navigator_factory->navigator(
                $created_with,
                $element
            )->lastElementAtFinalStep();
        }
        return $this->getFormForElement(
            $base_path,
            $element,
            $context_element,
            $link,
            $with_title,
            !$created_with && !$editor_tag?->isLastInTree()
        );
    }

    protected function getFormForElement(
        PathInterface $base_path,
        ElementInterface $element,
        ElementInterface $context_element,
        URI $link,
        bool $with_title,
        bool $empty
    ): StandardForm {
        $section = [];
        if (!$empty) {
            $section = [$this->input_factory->getInputFields(
                $element,
                $context_element,
                $with_title
            )
            ];
        }
        return $this->ui_factory->input()->container()->form()->standard(
            (string) $link,
            $section
        );
    }
}
