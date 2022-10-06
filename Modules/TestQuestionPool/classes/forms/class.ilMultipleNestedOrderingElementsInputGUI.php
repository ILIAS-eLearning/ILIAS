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

/**
 *
 * @author Nadia Ahmad <nahmad@databay.de>
 */
abstract class ilMultipleNestedOrderingElementsInputGUI extends ilIdentifiedMultiValuesInputGUI
{
    public const HTML_LIST_TAG_UL = 'ul';
    public const HTML_LIST_TAG_OL = 'ol';

    public const CSS_LIST_CLASS = 'dd-list';
    public const CSS_ITEM_CLASS = 'dd-item';
    public const CSS_HANDLE_CLASS = 'dd-handle';

    public const POSTVAR_SUBFIELD_NEST_ELEM = 'content';
    public const POSTVAR_SUBFIELD_NEST_INDENT = 'indentation';

    public const DEFAULT_INSTANCE_ID = 'default';

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

        $manipulator = new ilMultipleNestedOrderingElementsAdditionalIndexLevelRemover();
        $this->addFormValuesManipulator($manipulator);
    }

    public function setInstanceId($instanceId): void
    {
        $this->instanceId = $instanceId;
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }

    public function setInteractionEnabled($interactionEnabled): void
    {
        $this->interactionEnabled = $interactionEnabled;
    }

    public function isInteractionEnabled(): bool
    {
        return $this->interactionEnabled;
    }

    public function isNestingEnabled(): bool
    {
        return $this->nestingEnabled;
    }

    public function setNestingEnabled($nestingEnabled): void
    {
        $this->nestingEnabled = $nestingEnabled;
    }

    public function isStylingDisabled(): bool
    {
        return $this->stylingDisabled;
    }

    public function setStylingDisabled($stylingDisabled): void
    {
        $this->stylingDisabled = $stylingDisabled;
    }

    protected function isStylingEnabled(): bool
    {
        return !$this->isStylingDisabled();
    }

    /**
     * @return string
     */
    public function getCssListClass(): string
    {
        return $this->cssListClass;
    }

    /**
     * @param string $cssListClass
     */
    public function setCssListClass($cssListClass): void
    {
        $this->cssListClass = $cssListClass;
    }

    /**
     * @return string
     */
    public function getCssItemClass(): string
    {
        return $this->cssItemClass;
    }

    /**
     * @return string
     */
    public function getCssHandleClass(): string
    {
        return $this->cssHandleClass;
    }

    /**
     * @param string $cssHandleClass
     */
    public function setCssHandleClass($cssHandleClass): void
    {
        $this->cssHandleClass = $cssHandleClass;
    }

    /**
     * @param string $cssItemClass
     */
    public function setCssItemClass($cssItemClass): void
    {
        $this->cssItemClass = $cssItemClass;
    }

    /**
     * @return string
     */
    public function getHtmlListTag(): string
    {
        return $this->htmlListTag;
    }

    /**
     * @param string $htmlListTag
     */
    public function setHtmlListTag($htmlListTag): void
    {
        $this->htmlListTag = $htmlListTag;
    }

    protected function getGlobalTpl()
    {
        return isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['tpl'] : $GLOBALS['tpl'];
    }

    public function getListTpl()
    {
        return $this->listTpl;
    }

    public function setListTpl($listTpl): void
    {
        $this->listTpl = $listTpl;
    }

    protected function initListTemplate(): void
    {
        $this->setListTpl(
            new ilTemplate('tpl.prop_nested_ordering_list.html', true, true, 'Services/Form')
        );
    }

    protected function fetchListHtml(): string
    {
        return $this->getListTpl()->get();
    }

    protected function renderListContainer(): void
    {
        $this->getListTpl()->setCurrentBlock('list_container');
        $this->getListTpl()->setVariable('INSTANCE_ID', $this->getInstanceId());
        $this->getListTpl()->parseCurrentBlock();
    }

    protected function renderListSnippet(): void
    {
        $this->getListTpl()->setCurrentBlock('list_snippet');
        $this->getListTpl()->parseCurrentBlock();
    }

    protected function renderListItem($value, $identifier, $position): void
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

    protected function renderBeginListItem($identifier): void
    {
        $this->getListTpl()->setCurrentBlock('begin_list_item');
        $this->getListTpl()->setVariable('LIST_ITEM_ID', $identifier);
        $this->getListTpl()->setVariable('ILC_ITEM_CSS_CLASS', $this->getCssItemClass());
        $this->getListTpl()->parseCurrentBlock();
        $this->renderListSnippet();
    }

    protected function renderEndListItem(): void
    {
        $this->getListTpl()->setCurrentBlock('end_list_item');
        $this->getListTpl()->touchBlock('end_list_item');
        $this->getListTpl()->parseCurrentBlock();
        $this->renderListSnippet();
    }

    protected function renderBeginSubList(): void
    {
        $this->getListTpl()->setCurrentBlock('begin_sublist');
        $this->getListTpl()->setVariable('BEGIN_HTML_LIST_TAG', $this->getHtmlListTag());
        $this->getListTpl()->setVariable('ILC_LIST_CSS_CLASS', $this->getCssListClass());
        $this->getListTpl()->parseCurrentBlock();
        $this->renderListSnippet();
    }

    protected function renderEndSubList(): void
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
    abstract protected function getCurrentIndentation($elementValues, $elementCounter): int;

    /**
     * @param array $elementValues
     * @param integer $elementCounter
     * @return integer $nextDepth
     */
    abstract protected function getNextIndentation($elementValues, $elementCounter): int;

    protected function renderMainList(): string
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

    protected function renderJsInit(): string
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

    public function render(string $a_mode = ""): string
    {
        if ($this->isStylingEnabled()) {
            $this->getGlobalTpl()->addCss('Services/Form/css/nested_ordering.css');
        }

        if ($this->isInteractionEnabled()) {
            iljQueryUtil::initjQuery();
            iljQueryUtil::initjQueryUI();

            $this->getGlobalTpl()->addJavaScript('./node_modules/nestable2/dist/jquery.nestable.min.js');

            return $this->renderMainList() . $this->renderJsInit();
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
}
