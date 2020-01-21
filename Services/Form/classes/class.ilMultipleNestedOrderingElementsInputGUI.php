<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilIdentifiedMultiValuesInputGUI.php';
require_once 'Modules/Test/classes/inc.AssessmentConstants.php';
/**
 *
 * @author Nadia Ahmad <nahmad@databay.de>
 * @version $Id: $
 * @ingroup	ServicesForm
 */

abstract class ilMultipleNestedOrderingElementsInputGUI extends ilIdentifiedMultiValuesInputGUI
{
    const HTML_LIST_TAG_UL = 'ul';
    const HTML_LIST_TAG_OL = 'ol';
    
    const CSS_LIST_CLASS = 'dd-list';
    const CSS_ITEM_CLASS = 'dd-item';
    const CSS_HANDLE_CLASS = 'dd-handle';
    
    const POSTVAR_SUBFIELD_NEST_ELEM = 'content';
    const POSTVAR_SUBFIELD_NEST_INDENT = 'indentation';
    
    const DEFAULT_INSTANCE_ID = 'default';
    
    protected $instanceId = self::DEFAULT_INSTANCE_ID;
    
    protected $interactionEnabled = true;
    
    protected $nestingEnabled = true;
    
    protected $stylingDisabled = false;
    
    protected $listTpl = null;
    
    protected $cssListClass = self::CSS_LIST_CLASS;
    
    protected $cssItemClass = self::CSS_ITEM_CLASS;
    
    protected $cssHandleClass = self::CSS_HANDLE_CLASS;
    
    protected $htmlListTag = self::HTML_LIST_TAG_OL;
    
    public function __construct($a_title = '', $a_postvar = '')
    {
        parent::__construct($a_title, $a_postvar);
        
        require_once 'Services/Form/classes/class.ilMultipleNestedOrderingElementsAdditionalIndexLevelRemover.php';
        $manipulator = new ilMultipleNestedOrderingElementsAdditionalIndexLevelRemover();
        $this->addFormValuesManipulator($manipulator);
    }

    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;
    }

    public function getInstanceId()
    {
        return $this->instanceId;
    }
    
    public function setInteractionEnabled($interactionEnabled)
    {
        $this->interactionEnabled = $interactionEnabled;
    }
    
    public function isInteractionEnabled()
    {
        return $this->interactionEnabled;
    }
    
    public function isNestingEnabled()
    {
        return $this->nestingEnabled;
    }
    
    public function setNestingEnabled($nestingEnabled)
    {
        $this->nestingEnabled = $nestingEnabled;
    }
    
    public function isStylingDisabled()
    {
        return $this->stylingDisabled;
    }
    
    public function setStylingDisabled($stylingDisabled)
    {
        $this->stylingDisabled = $stylingDisabled;
    }
    
    protected function isStylingEnabled()
    {
        return !$this->isStylingDisabled();
    }
    
    /**
     * @return string
     */
    public function getCssListClass()
    {
        return $this->cssListClass;
    }
    
    /**
     * @param string $cssListClass
     */
    public function setCssListClass($cssListClass)
    {
        $this->cssListClass = $cssListClass;
    }
    
    /**
     * @return string
     */
    public function getCssItemClass()
    {
        return $this->cssItemClass;
    }
    
    /**
     * @return string
     */
    public function getCssHandleClass()
    {
        return $this->cssHandleClass;
    }
    
    /**
     * @param string $cssHandleClass
     */
    public function setCssHandleClass($cssHandleClass)
    {
        $this->cssHandleClass = $cssHandleClass;
    }
    
    /**
     * @param string $cssItemClass
     */
    public function setCssItemClass($cssItemClass)
    {
        $this->cssItemClass = $cssItemClass;
    }
    
    /**
     * @return string
     */
    public function getHtmlListTag()
    {
        return $this->htmlListTag;
    }
    
    /**
     * @param string $htmlListTag
     */
    public function setHtmlListTag($htmlListTag)
    {
        $this->htmlListTag = $htmlListTag;
    }
    
    /**
     * @return ilTemplate
     */
    protected function getGlobalTpl()
    {
        return isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['tpl'] : $GLOBALS['tpl'];
    }
    
    /**
     * @return ilTemplate
     */
    public function getListTpl()
    {
        return $this->listTpl;
    }
    
    /**
     * @param ilTemplate $listTpl
     */
    public function setListTpl($listTpl)
    {
        $this->listTpl = $listTpl;
    }
    
    protected function initListTemplate()
    {
        $this->setListTpl(
            new ilTemplate('tpl.prop_nested_ordering_list.html', true, true, 'Services/Form')
        );
    }
    
    protected function fetchListHtml()
    {
        return $this->getListTpl()->get();
    }
    
    protected function renderListContainer()
    {
        $this->getListTpl()->setCurrentBlock('list_container');
        $this->getListTpl()->setVariable('INSTANCE_ID', $this->getInstanceId());
        $this->getListTpl()->parseCurrentBlock();
    }
    
    protected function renderListSnippet()
    {
        $this->getListTpl()->setCurrentBlock('list_snippet');
        $this->getListTpl()->parseCurrentBlock();
    }

    protected function renderListItem($value, $identifier, $position)
    {
        $subPostVar = $this->getMultiValuePostVarSubField($identifier, self::POSTVAR_SUBFIELD_NEST_ELEM);
        $subFieldId = $this->getMultiValueSubFieldId($identifier, self::POSTVAR_SUBFIELD_NEST_ELEM);
    
        $this->getListTpl()->setCurrentBlock('item_value');
        
        $this->getListTpl()->setVariable('ILC_HANDLE_CSS_CLASS', $this->getCssHandleClass());
        
        $this->getListTpl()->setVariable('LIST_ITEM_VALUE', $this->getItemHtml(
            $value,
            $identifier,
            $position,
            $subPostVar,
            $subFieldId
        ));
        
        $this->getListTpl()->parseCurrentBlock();
        
        $this->renderListSnippet();
    }
    
    /**
     * @param $value
     * @param $identifier
     * @param $position
     * @param $itemSubFieldPostVar
     * @param $itemSubFieldId
     * @return mixed
     */
    abstract protected function getItemHtml($value, $identifier, $position, $itemSubFieldPostVar, $itemSubFieldId);
    
    protected function renderBeginListItem($identifier)
    {
        $this->getListTpl()->setCurrentBlock('begin_list_item');
        $this->getListTpl()->setVariable('LIST_ITEM_ID', $identifier);
        $this->getListTpl()->setVariable('ILC_ITEM_CSS_CLASS', $this->getCssItemClass());
        $this->getListTpl()->parseCurrentBlock();
        $this->renderListSnippet();
    }
    
    protected function renderEndListItem()
    {
        $this->getListTpl()->setCurrentBlock('end_list_item');
        $this->getListTpl()->touchBlock('end_list_item');
        $this->getListTpl()->parseCurrentBlock();
        $this->renderListSnippet();
    }
    
    protected function renderBeginSubList()
    {
        $this->getListTpl()->setCurrentBlock('begin_sublist');
        $this->getListTpl()->setVariable('BEGIN_HTML_LIST_TAG', $this->getHtmlListTag());
        $this->getListTpl()->setVariable('ILC_LIST_CSS_CLASS', $this->getCssListClass());
        $this->getListTpl()->parseCurrentBlock();
        $this->renderListSnippet();
    }
    
    protected function renderEndSubList()
    {
        $this->getListTpl()->setCurrentBlock('end_sublist');
        $this->getListTpl()->setVariable('END_HTML_LIST_TAG', $this->getHtmlListTag());
        $this->getListTpl()->parseCurrentBlock();
        $this->renderListSnippet();
    }
    
    /**
     * @param array $elementValues
     * @param integer $elementCounter
     * @return integer $currentDepth
     */
    abstract protected function getCurrentIndentation($elementValues, $elementCounter);
    
    /**
     * @param array $elementValues
     * @param integer $elementCounter
     * @return integer $nextDepth
     */
    abstract protected function getNextIndentation($elementValues, $elementCounter);
    
    protected function renderMainList()
    {
        $this->initListTemplate();
        $this->renderBeginSubList();
        
        
        $values = array_values($this->getIdentifiedMultiValues());
        $keys = array_keys($this->getIdentifiedMultiValues());
        $prevIndent = 0;
        
        foreach ($values as $counter => $value) {
            $identifier = $keys[$counter];
            
            if ($this->isNestingEnabled()) {
                $curIndent = $this->getCurrentIndentation($values, $counter);
                $nextIndent = $this->getNextIndentation($values, $counter);
            } else {
                $curIndent = $nextIndent = 0;
            }
            
            if ($prevIndent == $curIndent) {
                // pcn = Previous, Current, Next -> Depth
                // pcn:  000, 001, 110, 220
                if ($curIndent == $nextIndent) {
                    // (1) pcn: 000
                    //						echo"(1)";
                    $this->renderBeginListItem($identifier);
                    $this->renderListItem($value, $identifier, $counter);
                    $this->renderEndListItem();
                } elseif ($curIndent > $nextIndent) {
                    if ($prevIndent == $nextIndent) {
                        // wenn prev = cur ist und cur > next, wie soll prev = next sein !?
                        
                        // (8) pcn: 110
                        //							echo"(8)";
                        $this->renderBeginListItem($identifier);
                        $this->renderListItem($value, $identifier, $counter);
                        $this->renderEndListItem();
                        $this->renderEndSubList();
                        $this->renderEndListItem();
                    } elseif ($prevIndent > $nextIndent) {
                        // (12) pcn: 220
                        //							echo"(12)";
                        $this->renderBeginListItem($identifier);
                        $this->renderListItem($value, $identifier, $counter);
                        
                        for ($openlists = $nextIndent; $openlists < $curIndent; $openlists++) {
                            $this->renderEndListItem();
                            $this->renderEndSubList();
                            $this->renderEndListItem();
                        }
                    }
                } elseif ($curIndent < $nextIndent) {
                    // (2) pcn: 001
                    //						echo"(2)";
                    $this->renderBeginListItem($identifier);
                    $this->renderListItem($value, $identifier, $counter);
                    $this->renderBeginSubList();
                }
            } elseif ($prevIndent > $curIndent) {
                if ($curIndent == $nextIndent) {
                    // (6) pcn: 100
                    //						echo"(6)";
                    $this->renderBeginListItem($identifier);
                    $this->renderListItem($value, $identifier, $counter);
                    $this->renderEndListItem();
                } elseif ($curIndent > $nextIndent) {
                    // (11) pcn: 210
                    //						echo"(11)";
                    $this->renderBeginListItem($identifier);
                    $this->renderListItem($value, $identifier, $counter);
                    $this->renderEndListItem();
                    $this->renderEndSubList();
                } elseif ($curIndent < $nextIndent) {
                    if ($prevIndent == $nextIndent) {
                        // (7) pcn: 101
                        //							echo"(7)";
                        $this->renderBeginListItem($identifier);
                        $this->renderListItem($value, $identifier, $counter);
                        $this->renderBeginSubList();
                    } elseif ($prevIndent > $nextIndent) {
                        // (10) pcn: 201
                        //							echo"(10)";
                        $this->renderBeginListItem($identifier);
                        $this->renderListItem($value, $identifier, $counter);
                        for ($openlists = $nextIndent; $openlists < $curIndent; $openlists++) {
                            $this->renderEndSubList();
                        }
                        $this->renderBeginSubList();
                    }
                }
            } elseif ($prevIndent < $curIndent) {
                if ($curIndent == $nextIndent) {
                    // (4) pcn: 011
                    //						echo"(4)";
                    $this->renderBeginListItem($identifier);
                    $this->renderListItem($value, $identifier, $counter);
                    $this->renderEndListItem();
                } elseif ($curIndent > $nextIndent) {
                    if ($prevIndent == $nextIndent) {
                        // (3) pcn: 010,
                        //							echo"(3)";
                        $this->renderBeginListItem($identifier);
                        $this->renderListItem($value, $identifier, $counter);
                        $this->renderEndListItem();
                        $this->renderEndSubList();
                        $this->renderEndListItem();
                    } elseif ($prevIndent > $nextIndent) {
                        // (9) pcn: 120
                        //							echo"(9)";
                        $this->renderBeginListItem($identifier);
                        $this->renderListItem($value, $identifier, $counter);
                        for ($openlists = $nextIndent; $openlists < $curIndent; $openlists++) {
                            $this->renderEndListItem();
                            $this->renderEndSubList();
                        }
                    }
                } elseif ($curIndent < $nextIndent) {
                    // (5) pcn: 012
                    //						echo"(5)";
                    $this->renderBeginListItem($identifier);
                    $this->renderListItem($value, $identifier, $counter);
                    $this->renderBeginSubList();
                }
            }
            
            $prevIndent = $curIndent;
        }
        
        $this->renderEndSubList();
        $this->renderListContainer();
        
        return $this->fetchListHtml();
    }
    
    protected function renderJsInit()
    {
        $jsTpl = new ilTemplate('tpl.prop_nested_ordering_js.html', true, true, 'Services/Form');
        
        if (!$this->isNestingEnabled()) {
            $jsTpl->setCurrentBlock('avoid_nesting');
            $jsTpl->touchBlock('avoid_nesting');
            $jsTpl->parseCurrentBlock();
        }
        
        $jsTpl->setCurrentBlock('nested_ordering_init');
        $jsTpl->setVariable('INSTANCE_ID', $this->getInstanceId());
        $jsTpl->setVariable('INDENTATION_POSTVAR', $this->getPostVarSubField('indentation'));
        $jsTpl->setVariable('HTML_LIST_TAG', $this->getHtmlListTag());
        $jsTpl->setVariable('CSS_LIST_CLASS', $this->getCssListClass());
        $jsTpl->setVariable('CSS_ITEM_CLASS', $this->getCssItemClass());
        $jsTpl->parseCurrentBlock();
        
        return $jsTpl->get();
    }
    
    public function render($a_mode = "")
    {
        if ($this->isStylingEnabled()) {
            $this->getGlobalTpl()->addCss('Services/Form/css/nested_ordering.css');
        }
        
        if ($this->isInteractionEnabled()) {
            require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
            
            iljQueryUtil::initjQuery();
            iljQueryUtil::initjQueryUI();
            
            $this->getGlobalTpl()->addJavaScript('./libs/bower/bower_components/nestable2/jquery.nestable.js');
            
            return $this->renderMainList() . $this->renderJsInit();
        }
        
        return $this->renderMainList();
    }
    
    public function onCheckInput()
    {
        return true;
    }
    
    public function getHTML()
    {
        return $this->render();
    }
}
