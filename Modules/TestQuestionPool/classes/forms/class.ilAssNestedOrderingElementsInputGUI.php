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

use ILIAS\DI\UIServices;

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssNestedOrderingElementsInputGUI extends ilMultipleNestedOrderingElementsInputGUI
{
    public const CONTEXT_QUESTION_PREVIEW = 'QuestionPreview';
    public const CONTEXT_CORRECT_SOLUTION_PRESENTATION = 'CorrectSolutionPresent';
    public const CONTEXT_USER_SOLUTION_PRESENTATION = 'UserSolutionPresent';
    public const CONTEXT_USER_SOLUTION_SUBMISSION = 'UserSolutionSubmit';

    public const ILC_CSS_CLASS_LIST = 'ilc_qordul_OrderList';
    public const ILC_CSS_CLASS_ITEM = 'ilc_qordli_OrderListItem';

    /**
     * @var string
     */
    protected $context = null;

    /**
     * @var integer
     */
    protected $uniquePrefix = null;

    /**
     * @var mixed
     */
    protected $orderingType = null;

    public const DEFAULT_THUMBNAIL_PREFIX = 'thumb.';

    /**
     * @var string
     */
    protected $thumbnailFilenamePrefix = self::DEFAULT_THUMBNAIL_PREFIX;

    /**
     * @var string
     */
    protected $elementImagePath = null;

    /**
     * @var bool
     */
    protected $showCorrectnessIconsEnabled = false;

    /**
     * @var ilAssOrderingElementList
     */
    protected $correctnessTrueElementList = null;

    private UIServices $ui;

    /**
     * ilAssNestedOrderingElementsInputGUI constructor.
     *
     * @param ilAssOrderingFormValuesObjectsConverter $converter
     * @param string $postVar
     */
    public function __construct(ilAssOrderingFormValuesObjectsConverter $converter, $postVar)
    {
        global $DIC;
        $this->ui = $DIC->ui();
        $manipulator = new ilAssOrderingDefaultElementFallback();
        $this->addFormValuesManipulator($manipulator);

        parent::__construct('', $postVar);

        $this->addFormValuesManipulator($converter);

        $this->setHtmlListTag(parent::HTML_LIST_TAG_UL);
        $this->setCssListClass($this->getCssListClass() . ' ' . self::ILC_CSS_CLASS_LIST);
        $this->setCssItemClass($this->getCssItemClass() . ' ' . self::ILC_CSS_CLASS_ITEM);
        $this->setCssHandleClass($this->getCssHandleClass());
    }

    /**
     * @param ilAssOrderingElementList $elementList
     */
    public function setElementList(ilAssOrderingElementList $elementList): void
    {
        $this->setIdentifiedMultiValues($elementList->getRandomIdentifierIndexedElements());
    }

    /**
     * @param $questionId
     * @return ilAssOrderingElementList
     */
    public function getElementList($questionId): ilAssOrderingElementList
    {
        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElementList.php';
        return ilAssOrderingElementList::buildInstance($questionId, $this->getIdentifiedMultiValues());
    }

    /**
     * @param assOrderingQuestion $question
     */
    public function prepareReprintable(assQuestion $question): void
    {
        $elementList = $this->getElementList($question->getId());

        $elementList->completeContentsFromElementList(
            $question->getOrderingElementList()
        );

        $this->setElementList($elementList);
    }

    public function getInstanceId(): string
    {
        if (!$this->getContext() || !$this->getUniquePrefix()) {
            return parent::getInstanceId();
        }

        return $this->getContext() . '_' . $this->getUniquePrefix();
    }

    /**
     * @return string
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * @param string $context
     */
    public function setContext($context): void
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getUniquePrefix()
    {
        return $this->uniquePrefix;
    }

    /**
     * @param string $uniquePrefix
     */
    public function setUniquePrefix($uniquePrefix): void
    {
        $this->uniquePrefix = $uniquePrefix;
    }

    /**
     * @param mixed $orderingType
     */
    public function setOrderingType($orderingType): void
    {
        $this->orderingType = $orderingType;
    }

    /**
     * @return mixed
     */
    public function getOrderingType()
    {
        return $this->orderingType;
    }

    /**
     * @param string $elementImagePath
     */
    public function setElementImagePath($elementImagePath): void
    {
        $this->elementImagePath = $elementImagePath;
    }

    /**
     * @return string
     */
    public function getElementImagePath(): ?string
    {
        return $this->elementImagePath;
    }

    /**
     * @param string $thumbnailFilenamePrefix
     */
    public function setThumbPrefix($thumbnailFilenamePrefix): void
    {
        $this->thumbnailFilenamePrefix = $thumbnailFilenamePrefix;
    }

    /**
     * @return string
     */
    public function getThumbPrefix(): string
    {
        return $this->thumbnailFilenamePrefix;
    }

    /**
     * @param $showCorrectnessIconsEnabled
     */
    public function setShowCorrectnessIconsEnabled($showCorrectnessIconsEnabled): void
    {
        $this->showCorrectnessIconsEnabled = $showCorrectnessIconsEnabled;
    }

    /**
     * @return bool
     */
    public function isShowCorrectnessIconsEnabled(): bool
    {
        return $this->showCorrectnessIconsEnabled;
    }

    /**
     * @return ilAssOrderingElementList
     */
    public function getCorrectnessTrueElementList(): ?ilAssOrderingElementList
    {
        return $this->correctnessTrueElementList;
    }

    /**
     * @param ilAssOrderingElementList $correctnessTrueElementList
     */
    public function setCorrectnessTrueElementList(ilAssOrderingElementList $correctnessTrueElementList): void
    {
        $this->correctnessTrueElementList = $correctnessTrueElementList;
    }

    /**
     * @param $identifier
     * @return bool
     */
    protected function getCorrectness($identifier): bool
    {
        return $this->getCorrectnessTrueElementList()->elementExistByRandomIdentifier($identifier);
    }

    private function getCorrectnessIcon($correctness): string
    {
        $icon_name = 'icon_not_ok.svg';
        $label = $this->lng->txt("answer_is_wrong");
        if ($correctness === 'correct') {
            $icon_name = 'icon_ok.svg';
            $label = $this->lng->txt("answer_is_right");
        }
        $path = ilUtil::getImagePath($icon_name);
        $icon = $this->ui->factory()->symbol()->icon()->custom(
            $path,
            $label
        );
        return $this->ui->renderer()->render($icon);
    }

    /**
     * @return ilTemplate
     */
    protected function getItemTemplate(): ilTemplate
    {
        return new ilTemplate('tpl.prop_ass_nested_order_elem.html', true, true, 'Modules/TestQuestionPool');
    }

    /**
     * @return string
     */
    protected function getThumbnailFilename($element): string
    {
        return $this->getThumbPrefix() . $element['content'];
    }

    /**
     * @return string
     */
    protected function getThumbnailSource($element): string
    {
        return $this->getElementImagePath() . $this->getThumbnailFilename($element);
    }

    /**
     * @param ilAssOrderingElement $element
     * @param string $identifier
     * @param int $position
     * @param string $itemSubFieldPostVar
     * @param string $itemSubFieldId
     * @return string
     */
    protected function getItemHtml($element, $identifier, $position, $itemSubFieldPostVar, $itemSubFieldId): string
    {
        $tpl = $this->getItemTemplate();

        switch ($this->getOrderingType()) {
            case assOrderingQuestion::OQ_TERMS:
            case assOrderingQuestion::OQ_NESTED_TERMS:

                $tpl->setCurrentBlock('item_text');
                $tpl->setVariable("ITEM_CONTENT", ilLegacyFormElementsUtil::prepareFormOutput($element['content']));
                $tpl->parseCurrentBlock();
                break;

            case assOrderingQuestion::OQ_PICTURES:
            case assOrderingQuestion::OQ_NESTED_PICTURES:

                $tpl->setCurrentBlock('item_image');
                $tpl->setVariable("ITEM_SOURCE", $this->getThumbnailSource($element));
                $tpl->setVariable("ITEM_CONTENT", $this->getThumbnailFilename($element));
                $tpl->parseCurrentBlock();
                break;
        }

        if ($this->isShowCorrectnessIconsEnabled()) {
            $correctness = 'not_correct';
            if ($this->getCorrectness($identifier)) {
                $correctness = 'correct';
            }
            $tpl->setCurrentBlock('correctness_icon');
            $tpl->setVariable("ICON_OK", $this->getCorrectnessIcon($correctness));
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock('item');
        $tpl->setVariable("ITEM_ID", $itemSubFieldId);
        $tpl->setVariable("ITEM_POSTVAR", $itemSubFieldPostVar);
        $tpl->setVariable("ITEM_CONTENT", ilLegacyFormElementsUtil::prepareFormOutput($element['content']));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * @param array $elementValues
     * @param integer $elementCounter
     * @return integer $currentDepth
     */
    protected function getCurrentIndentation($elementValues, $elementCounter): int
    {
        if (!isset($elementValues[$elementCounter])) {
            return 0;
        }

        return $elementValues[$elementCounter]['ordering_indentation'];
    }

    /**
     * @param array $elementValues
     * @param integer $elementCounter
     * @return integer $nextDepth
     */
    protected function getNextIndentation($elementValues, $elementCounter): int
    {
        if (!isset($elementValues[$elementCounter + 1])) {
            return 0;
        }

        return $elementValues[$elementCounter + 1]['ordering_indentation'];
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
}
