<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilMultipleNestedOrderingElementsInputGUI.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssNestedOrderingElementsInputGUI extends ilMultipleNestedOrderingElementsInputGUI
{
    const CONTEXT_QUESTION_PREVIEW = 'QuestionPreview';
    const CONTEXT_CORRECT_SOLUTION_PRESENTATION = 'CorrectSolutionPresent';
    const CONTEXT_USER_SOLUTION_PRESENTATION = 'UserSolutionPresent';
    const CONTEXT_USER_SOLUTION_SUBMISSION = 'UserSolutionSubmit';
    
    const ILC_CSS_CLASS_LIST = 'ilc_qordul_OrderList';
    const ILC_CSS_CLASS_ITEM = 'ilc_qordli_OrderListItem';

    const DEFAULT_THUMBNAIL_PREFIX = 'thumb.';

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
    
    /**
     * ilAssNestedOrderingElementsInputGUI constructor.
     *
     * @param ilAssOrderingFormValuesObjectsConverter $converter
     * @param string $postVar
     */
    public function __construct(ilAssOrderingFormValuesObjectsConverter $converter, $postVar)
    {
        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingDefaultElementFallback.php';
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
    public function setElementList(ilAssOrderingElementList $elementList)
    {
        $this->setIdentifiedMultiValues($elementList->getRandomIdentifierIndexedElements());
    }
    
    /**
     * @param $questionId
     * @return ilAssOrderingElementList
     */
    public function getElementList($questionId)
    {
        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElementList.php';
        return ilAssOrderingElementList::buildInstance($questionId, $this->getIdentifiedMultiValues());
    }
    
    /**
     * @param assOrderingQuestion $question
     */
    public function prepareReprintable(assQuestion $question)
    {
        $elementList = $this->getElementList($question->getId());
        
        $elementList->completeContentsFromElementList(
            $question->getOrderingElementList()
        );

        $this->setElementList($elementList);
    }
    
    public function getInstanceId()
    {
        if (!$this->getContext() || !$this->getUniquePrefix()) {
            return parent::getInstanceId();
        }
        
        return $this->getContext() . '_' . $this->getUniquePrefix();
    }
    
    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }
    
    /**
     * @param string $context
     */
    public function setContext($context)
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
    public function setUniquePrefix($uniquePrefix)
    {
        $this->uniquePrefix = $uniquePrefix;
    }
    
    /**
     * @param mixed $orderingType
     */
    public function setOrderingType($orderingType)
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
    public function setElementImagePath($elementImagePath)
    {
        $this->elementImagePath = $elementImagePath;
    }
    
    /**
     * @return string
     */
    public function getElementImagePath()
    {
        return $this->elementImagePath;
    }
    
    /**
     * @param string $thumbnailFilenamePrefix
     */
    public function setThumbPrefix($thumbnailFilenamePrefix)
    {
        $this->thumbnailFilenamePrefix = $thumbnailFilenamePrefix;
    }
    
    /**
     * @return string
     */
    public function getThumbPrefix()
    {
        return $this->thumbnailFilenamePrefix;
    }
    
    /**
     * @param $showCorrectnessIconsEnabled
     */
    public function setShowCorrectnessIconsEnabled($showCorrectnessIconsEnabled)
    {
        $this->showCorrectnessIconsEnabled = $showCorrectnessIconsEnabled;
    }
    
    /**
     * @return bool
     */
    public function isShowCorrectnessIconsEnabled()
    {
        return $this->showCorrectnessIconsEnabled;
    }
    
    /**
     * @param bool $correctness
     * @return string
     */
    public function getCorrectnessIconFilename($correctness)
    {
        if((bool) $correctness){
            return ilUtil::getImagePath('icon_ok.svg');
        }
        return ilUtil::getImagePath('icon_not_ok.svg');
    }

    /**
     * @param bool $correctness
     * @return string
     */
    public function getCorrectnessText($correctness)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $lng = $DIC['lng'];
        if ((bool) $correctness){
            return $lng->txt('answer_is_right');
        }
        return $lng->txt('answer_is_wrong');

    }
    
    /**
     * @return ilAssOrderingElementList
     */
    public function getCorrectnessTrueElementList()
    {
        return $this->correctnessTrueElementList;
    }
    
    /**
     * @param ilAssOrderingElementList $correctnessTrueElementList
     */
    public function setCorrectnessTrueElementList(ilAssOrderingElementList $correctnessTrueElementList)
    {
        $this->correctnessTrueElementList = $correctnessTrueElementList;
    }
    
    /**
     * @param $identifier
     * @return bool
     */
    protected function getCorrectness($identifier)
    {
        return $this->getCorrectnessTrueElementList()->elementExistByRandomIdentifier($identifier);
    }
    
    /**
     * @return ilTemplate
     */
    protected function getItemTemplate()
    {
        return new ilTemplate('tpl.prop_ass_nested_order_elem.html', true, true, 'Modules/TestQuestionPool');
    }
    
    /**
     * @param array $element
     * @return string
     */
    protected function getThumbnailFilename($element)
    {
        return $this->getThumbPrefix() . $element['content'];
    }
    
    /**
     * @param array $element
     * @return string
     */
    protected function getThumbnailSource($element)
    {
        return $this->getElementImagePath() . $this->getThumbnailFilename($element);
    }
    
    /**
     * @param ilAssOrderingElement $element
     * @param string $identifier
     * @param iunteger $position
     * @param string $itemSubFieldPostVar
     * @param string $itemSubFieldId
     * @return string
     */
    protected function getItemHtml($element, $identifier, $position, $itemSubFieldPostVar, $itemSubFieldId)
    {
        $tpl = $this->getItemTemplate();
        
        switch ($this->getOrderingType()) {
            case assOrderingQuestion::OQ_TERMS:
            case assOrderingQuestion::OQ_NESTED_TERMS:
            
                $tpl->setCurrentBlock('item_text');
                $tpl->setVariable("ITEM_CONTENT", ilUtil::prepareFormOutput($element['content']));
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
            $tpl->setCurrentBlock('correctness_icon');
            $correctness = $this->getCorrectness($element['random_id']);
            $tpl->setVariable("ICON_SRC", $this->getCorrectnessIconFilename($correctness));
            $tpl->setVariable("ICON_TEXT", $this->getCorrectnessText($correctness));
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setCurrentBlock('item');
        $tpl->setVariable("ITEM_ID", $itemSubFieldId);
        $tpl->setVariable("ITEM_POSTVAR", $itemSubFieldPostVar);
        $tpl->setVariable("ITEM_CONTENT", ilUtil::prepareFormOutput($element['content']));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }
    
    /**
     * @param array $elementValues
     * @param integer $elementCounter
     * @return integer $currentDepth
     */
    protected function getCurrentIndentation($elementValues, $elementCounter)
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
    protected function getNextIndentation($elementValues, $elementCounter)
    {
        if (!isset($elementValues[$elementCounter + 1])) {
            return 0;
        }
        
        return $elementValues[$elementCounter + 1]['ordering_indentation'];
    }
    
    public function isPostSubmit($data)
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
