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
	const POST_VARIABLE_NAME = 'ordering';
	
	const CONTEXT_QUESTION_PREVIEW = 'QuestionPreview';
	const CONTEXT_CORRECT_SOLUTION_PRESENTATION = 'CorrectSolutionPresent';
	const CONTEXT_USER_SOLUTION_PRESENTATION = 'UserSolutionPresent';
	const CONTEXT_USER_SOLUTION_SUBMISSION = 'UserSolutionSubmit';
	
	/**
	 * @var string
	 */
	protected $context = null;
	
	/**
	 * @var integer
	 */
	protected $questionId = null;
	
	/**
	 * @var mixed
	 */
	protected $orderingType = null;
	
	const DEFAULT_THUMBNAIL_PREFIX = 'thumb.';
	
	/**
	 * @var string
	 */
	protected $thumbnailFilenamePrefix = self::DEFAULT_THUMBNAIL_PREFIX;
	
	/**
	 * @var string
	 */
	protected $elementImagePath = null;
	
	const CORRECTNESS_ICON_TRUE = 'icon_ok.svg';
	const CORRECTNESS_LNGVAR_TRUE = 'answer_is_right';
	
	const CORRECTNESS_ICON_FALSE = 'icon_not_ok.svg';
	const CORRECTNESS_LNGVAR_FALSE = 'answer_is_wrong';
	
	/**
	 * @var array
	 */
	protected $correctnessIcons = array(
		true => self::CORRECTNESS_ICON_TRUE, false => self::CORRECTNESS_ICON_FALSE
	);
	
	/**
	 * @var array
	 */
	protected $correctnessLngVars = array(
		true => self::CORRECTNESS_LNGVAR_TRUE, false => self::CORRECTNESS_LNGVAR_FALSE
	);
	
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
	 */
	public function __construct($questionId)
	{
		$this->setQuestionId($questionId);
		
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingDefaultElementFallback.php';
		$manipulator = new ilAssOrderingDefaultElementFallback();
		$this->addFormValuesManipulator($manipulator);
		
		$lng = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['lng'] : $GLOBALS['lng'];
		parent::__construct($lng->txt("answers"), self::POST_VARIABLE_NAME);
		
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingFormValuesObjectsConverter.php';
		$manipulator = new ilAssOrderingFormValuesObjectsConverter();
		$manipulator->setContext(ilAssOrderingFormValuesObjectsConverter::CONTEXT_MAINTAIN_HIERARCHY);
		$manipulator->setPostVar($this->getPostVar());
		$this->addFormValuesManipulator($manipulator);
	}
	
	/**
	 * @param ilAssOrderingElementList $elementList
	 */
	public function setElementList(ilAssOrderingElementList $elementList)
	{
		$this->setMultiValues( $elementList->getRandomIdentifierIndexedElements() );
	}
	
	/**
	 * @return ilAssOrderingElementList
	 */
	public function getElementList()
	{
		$elementList = $this->buildElementList();
		$elementList->setElements($this->getMultiValues());
		return $elementList;
	}
	
	protected function buildElementList()
	{
		require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElementList.php';
		$elementList = new ilAssOrderingElementList();
		
		$elementList->setQuestionId($this->getQuestionId());
		
		return $elementList;
	}
	
	public function getInstanceId()
	{
		if( !$this->getContext() || !$this->getQuestionId() )
		{
			return parent::getInstanceId();
		}
		
		return $this->getContext() . '_' . $this->getQuestionId();
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
	 * @return int
	 */
	public function getQuestionId()
	{
		return $this->questionId;
	}
	
	/**
	 * @param int $questionId
	 */
	public function setQuestionId($questionId)
	{
		$this->questionId = $questionId;
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
		return $this->correctnessIcons[(bool)$correctness];
	}
	
	/**
	 * @param bool $correctness
	 * @param string $iconFilename
	 */
	public function setCorrectnessIconFilename($correctness, $iconFilename)
	{
		$this->correctnessIcons[(bool)$correctness] = $iconFilename;
	}
	
	/**
	 * @param bool $correctness
	 * @return string
	 */
	public function getCorrectnessLangVar($correctness)
	{
		return $this->correctnessLngVars[(bool)$correctness];
	}
	
	/**
	 * @param bool $correctness
	 * @param string $langVar
	 */
	public function setCorrectnessLangVar($correctness, $langVar)
	{
		$this->correctnessLngVars[(bool)$correctness] = $langVar;
	}
	
	/**
	 * @param bool $correctness
	 * @return string
	 */
	public function getCorrectnessText($correctness)
	{
		$lng = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['lng'] : $GLOBALS['lng'];
		return $lng->txt( $this->correctnessLngVars[(bool)$correctness] );
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
	 * @return string
	 */
	protected function getItemHtml($element, $identifier, $position)
	{
		$tpl = $this->getItemTemplate();
		
		switch( $this->getOrderingType() )
		{
			case OQ_TERMS:
			case OQ_NESTED_TERMS:
			
				$tpl->setCurrentBlock('item_text');
				$tpl->setVariable("ITEM_CONTENT", ilUtil::prepareFormOutput($element['content']));
				$tpl->parseCurrentBlock();
				break;
				
			case OQ_PICTURES:
			case OQ_NESTED_PICTURES:
				
				$tpl->setCurrentBlock('item_image');
				$tpl->setVariable("ITEM_SOURCE", $this->getThumbnailSource($element));
				$tpl->setVariable("ITEM_CONTENT", $this->getThumbnailFilename($element));
				$tpl->parseCurrentBlock();
				break;
		}
		
		if( $this->isShowCorrectnessIconsEnabled() )
		{
			$tpl->setCurrentBlock('correctness_icon');
			$tpl->setVariable("ICON_SRC", $this->getCorrectnessIconFilename( $this->getCorrectness($identifier) ));
			$tpl->setVariable("ICON_TEXT", $this->getCorrectnessText( $this->getCorrectness($identifier) ));
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setCurrentBlock('item');
		$tpl->setVariable("ITEM_ID", $this->getMultiValueSubFieldId($identifier, 'content'));
		$tpl->setVariable("ITEM_POSTVAR", $this->getMultiValuePostVarSubField($identifier, 'content'));
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
		if( !isset($elementValues[$elementCounter]) )
		{
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
		if( !isset($elementValues[$elementCounter + 1]) )
		{
			return 0;
		}
		
		return $elementValues[$elementCounter + 1]['ordering_indentation'];
	}
	
	public function isPostSubmit($data)
	{
		if( !is_array($data) )
		{
			return false;
		}
		
		if( !isset($data[self::POST_VARIABLE_NAME]) )
		{
			return false;
		}
		
		if( !count($data[self::POST_VARIABLE_NAME]) )
		{
			return false;
		}
		
		return true;
	}

}