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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

class ilAssNestedOrderingElementsInputGUI extends ilIdentifiedMultiValuesInputGUI
{
    private const POSTVAR_CONTENT = 'content';
    private const POSTVAR_POSITION = 'position';
    private const POSTVAR_INDENTATION = 'indentation';

    public const CONTEXT_QUESTION_PREVIEW = 'QuestionPreview';
    public const CONTEXT_CORRECT_SOLUTION_PRESENTATION = 'CorrectSolutionPresent';
    public const CONTEXT_USER_SOLUTION_PRESENTATION = 'UserSolutionPresent';
    public const CONTEXT_USER_SOLUTION_SUBMISSION = 'UserSolutionSubmit';

    private const DEFAULT_THUMBNAIL_PREFIX = 'thumb.';

    private ?string $context = null;
    private ?int $unique_prefix = null;
    private ?int $ordering_type = null;
    private string $thumbnail_filename_prefix = self::DEFAULT_THUMBNAIL_PREFIX;
    private ?string $element_image_path = null;
    private bool $show_correctness_icons_enabled = false;
    private ?ilAssOrderingElementList $correctness_true_element_list = null;
    private bool $interaction_enabled = true;
    private bool $nesting_enabled = true;
    private bool $styling_disabled = false;
    private ?ilTemplate $list_tpl = null;

    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;

    public function __construct(ilAssOrderingFormValuesObjectsConverter $converter, $postVar)
    {
        global $DIC;
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $manipulator = new ilAssOrderingDefaultElementFallback();
        $this->addFormValuesManipulator($manipulator);

        parent::__construct('', $postVar);

        $this->addFormValuesManipulator($converter);
    }

    public function setInteractionEnabled(bool $interaction_enabled): void
    {
        $this->interaction_enabled = $interaction_enabled;
    }

    public function setNestingEnabled(bool $nesting_enabled): void
    {
        $this->nesting_enabled = $nesting_enabled;
    }

    public function setStylingDisabled(bool $styling_disabled): void
    {
        $this->styling_disabled = $styling_disabled;
    }

    private function getGlobalTpl()
    {
        return isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['tpl'] : $GLOBALS['tpl'];
    }

    private function initListTemplate(): void
    {
        $this->list_tpl = new ilTemplate('tpl.prop_nested_ordering_list.html', true, true, 'components/ILIAS/TestQuestionPool');
    }

    public function setElementList(ilAssOrderingElementList $elementList): void
    {
        $this->setIdentifiedMultiValues($elementList->getRandomIdentifierIndexedElements());
    }

    public function getElementList(int $questionId): ilAssOrderingElementList
    {
        return ilAssOrderingElementList::buildInstance($questionId, $this->getIdentifiedMultiValues());
    }

    public function prepareReprintable(assQuestion $question): void
    {
        $elementList = $this->getElementList($question->getId());

        $elementList->completeContentsFromElementList(
            $question->getOrderingElementList()
        );

        $this->setElementList($elementList);
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(string $context): void
    {
        $this->context = $context;
    }

    public function getUniquePrefix(): int
    {
        return $this->unique_prefix;
    }

    public function setUniquePrefix(int $unique_prefix): void
    {
        $this->unique_prefix = $unique_prefix;
    }

    public function setOrderingType(int $ordering_type): void
    {
        $this->ordering_type = $ordering_type;
    }

    public function getOrderingType(): ?int
    {
        return $this->ordering_type;
    }

    public function setElementImagePath(string $element_image_path): void
    {
        $this->element_image_path = $element_image_path;
    }

    public function getElementImagePath(): ?string
    {
        return $this->element_image_path;
    }

    public function setThumbPrefix(string $thumbnail_filename_prefix): void
    {
        $this->thumbnail_filename_prefix = $thumbnail_filename_prefix;
    }

    public function getThumbPrefix(): string
    {
        return $this->thumbnail_filename_prefix;
    }

    public function setShowCorrectnessIconsEnabled(bool $show_correctness_icons_enabled): void
    {
        $this->show_correctness_icons_enabled = $show_correctness_icons_enabled;
    }

    public function isShowCorrectnessIconsEnabled(): bool
    {
        return $this->show_correctness_icons_enabled;
    }

    public function getCorrectnessTrueElementList(): ?ilAssOrderingElementList
    {
        return $this->correctness_true_element_list;
    }

    public function setCorrectnessTrueElementList(ilAssOrderingElementList $correctness_true_element_list): void
    {
        $this->correctness_true_element_list = $correctness_true_element_list;
    }

    private function getCorrectness(int $identifier): bool
    {
        return $this->getCorrectnessTrueElementList()->elementExistByRandomIdentifier($identifier);
    }

    private function getCorrectnessIcon($correctness): string
    {
        $icon_name = 'standard/icon_not_ok.svg';
        $label = $this->lng->txt("answer_is_wrong");
        if ($correctness === 'correct') {
            $icon_name = 'standard/icon_ok.svg';
            $label = $this->lng->txt("answer_is_right");
        }
        $path = ilUtil::getImagePath($icon_name);
        $icon = $this->ui_factory->symbol()->icon()->custom(
            $path,
            $label
        );
        return $this->ui_renderer->render($icon);
    }

    private function getItemTemplate(): ilTemplate
    {
        return new ilTemplate('tpl.prop_ass_nested_order_elem.html', true, true, 'components/ILIAS/TestQuestionPool');
    }

    private function getThumbnailFilename($element): string
    {
        return $this->getThumbPrefix() . $element['content'];
    }

    private function getThumbnailSource($element): string
    {
        return $this->getElementImagePath() . $this->getThumbnailFilename($element);
    }

    private function getItemHtml(
        array $element,
        string $identifier,
        string $content_post_var,
        string $position_post_var,
        string $indentation_post_var
    ): string {
        $tpl = $this->getItemTemplate();

        switch ($this->getOrderingType()) {
            case assOrderingQuestion::OQ_TERMS:
            case assOrderingQuestion::OQ_NESTED_TERMS:

                $tpl->setCurrentBlock('item_text');
                $tpl->setVariable('ITEM_CONTENT', ilLegacyFormElementsUtil::prepareFormOutput($element['content']));
                $tpl->parseCurrentBlock();
                break;

            case assOrderingQuestion::OQ_PICTURES:
            case assOrderingQuestion::OQ_NESTED_PICTURES:

                $tpl->setCurrentBlock('item_image');
                $tpl->setVariable('ITEM_SOURCE', $this->getThumbnailSource($element));
                $tpl->setVariable('ITEM_CONTENT', $this->getThumbnailFilename($element));
                $tpl->parseCurrentBlock();
                break;
        }

        if ($this->isShowCorrectnessIconsEnabled()) {
            $correctness = 'not_correct';
            if ($this->getCorrectness($identifier)) {
                $correctness = 'correct';
            }
            $tpl->setCurrentBlock('correctness_icon');

            $tpl->setVariable('ICON_OK', $this->getCorrectnessIcon($correctness));
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock('item');
        $tpl->setVariable('ITEM_CONTENT_POSTVAR', $content_post_var);
        $tpl->setVariable('ITEM_CONTENT', ilLegacyFormElementsUtil::prepareFormOutput($element['content']));
        $tpl->setVariable('ITEM_POSITION_POSTVAR', $position_post_var);
        $tpl->setVariable('ITEM_POSITION', $element['ordering_position']);
        $tpl->setVariable('ITEM_INDENTATION_POSTVAR', $indentation_post_var);
        $tpl->setVariable('ITEM_INDENTATION', $element['ordering_indentation']);
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    public function isPostSubmit($data): bool
    {
        if (!is_array($data)) {
            return false;
        }

        if (!isset($data[$this->getPostVar()])) {
            return false;
        }

        if (!count($data[$this->getPostVar()])) {
            return false;
        }

        return true;
    }

    private function renderListItem(array $value, string $identifier, string $children): string
    {
        $list_item_tpl = new ilTemplate('tpl.prop_nested_ordering_list_item.html', true, true, 'components/ILIAS/TestQuestionPool');
        $content_post_var = $this->getMultiValuePostVarSubField($identifier, self::POSTVAR_CONTENT);
        $position_post_var = $this->getMultiValuePostVarSubField($identifier, self::POSTVAR_POSITION);
        $indentation_post_var = $this->getMultiValuePostVarSubField($identifier, self::POSTVAR_INDENTATION);

        $list_item_tpl->setVariable('LIST_ITEM_VALUE', $this->getItemHtml(
            $value,
            $identifier,
            $content_post_var,
            $position_post_var,
            $indentation_post_var
        ));

        if ($this->nesting_enabled) {
            $list_item_tpl->setCurrentBlock('nested_list');
            $list_item_tpl->setVariable('SUB_LIST', $children);
            $list_item_tpl->parseCurrentBlock();
        }
        return $list_item_tpl->get();
    }

    private function renderMainList(): string
    {
        $this->initListTemplate();

        $values = $this->addIdentifierToValues($this->getIdentifiedMultiValues());
        if ($this->nesting_enabled) {
            $values = $this->buildHierarchicalTreeFromDBValues($values);
        }

        $this->list_tpl->setVariable(
            'LIST_ITEMS',
            $this->buildHTMLView(
                $values,
                static fn(array $a, array $b): int => $a['ordering_position'] - $b['ordering_position']
            )
        );

        return $this->list_tpl->get();
    }

    private function addIdentifierToValues(array $values): array
    {
        foreach (array_keys($values) as $k) {
            $values[$k]['identifier'] = str_replace(assOrderingQuestionGUI::F_NESTED_IDENTIFIER_PREFIX, '', $k);
        }
        return $values;
    }

    private function buildHierarchicalTreeFromDBValues(array $values): array
    {
        $values_with_parent = [];
        $levels_array = [];
        foreach ($values as $k => $v) {
            $v['parent'] = null;
            $v['children'] = [];

            if ($v['ordering_indentation'] > 0) {
                $v['parent'] = $levels_array[$v['ordering_indentation'] - 1];
            }
            $levels_array[$v['ordering_indentation']] = $k;
            $values_with_parent[$k] = $v;
        }

        uasort(
            $values_with_parent,
            static fn(array $a, array $b): int => $b['ordering_indentation'] - $a['ordering_indentation']
        );

        foreach (array_keys($values_with_parent) as $k) {
            $v = $values_with_parent[$k];
            if ($v['parent'] !== null) {
                $values_with_parent[$v['parent']]['children'][$k] = $v;
            }
        }

        return array_filter(
            $values_with_parent,
            static fn(array $v): bool => $v['ordering_indentation'] === 0
        );
    }

    private function buildHTMLView(array $array, \Closure $sort_closure): string
    {
        usort($array, $sort_closure);
        return array_reduce(
            $array,
            function (string $c, array $v) use ($sort_closure): string {
                $children = '';
                if ($this->nesting_enabled && $v['children'] !== []) {
                    $children = $this->buildHTMLView($v['children'], $sort_closure);
                }
                $c .= $this->renderListItem($v, $v['identifier'], $children);
                return $c;
            },
            ''
        );
    }

    public function render(string $a_mode = ''): string
    {
        if (!$this->styling_disabled) {
            $this->getGlobalTpl()->addCss('assets/css/content.css');
        }

        if ($this->interaction_enabled) {
            $this->initializePlayerJS();
        }

        return $this->renderMainList();
    }

    public function onCheckInput(): bool
    {
        return true;
    }

    public function getHTML(): string
    {
        return $this->render();
    }

    private function initializePlayerJS(): void
    {
        $this->getGlobalTpl()->addJavascript('assets/js/orderingvertical.js');
        $this->getGlobalTpl()->addOnLoadCode(
            'il.test.orderingvertical.init(document.querySelector("#nestable_ordering"));'
        );
    }
}
