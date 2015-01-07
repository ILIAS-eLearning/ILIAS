<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
 *
 * @author Nadia Ahmad <nahmad@databay.de>
 * @version $Id: $
 * @ingroup	ServicesForm
 */

class ilNestedOrderingGUI extends ilNonEditableValueGUI
{
	protected $instanceId = 'default';

	public $answers = array();
	public $ordering_type = NULL;
	public $image_path = null;
	public $image_path_web = null;
	public $thumb_prefix = 'thumb.';
	
	private $perform_js = 1;
	
	private $graphical_output = FALSE;

	public function __construct($a_title = "", $a_postvar = "", $graphical_output = false)
	{
		parent::__construct($a_title, $a_postvar);
		$this->graphical_output = (bool)$graphical_output;
	}

	public function setInstanceId($instanceId)
	{
		$this->instanceId = $instanceId;
	}

	public function getInstanceId()
	{
		return $this->instanceId;
	}

	/**
	 * @param $a_tpl ilTemplate
	 * @return int|void
	 */
	public function insert(&$a_tpl)
	{
		/**
		 * @var $a_tpl ilTemplate 
		 */
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $this->getHtml());
		$a_tpl->parseCurrentBlock();
	}

	
	public function setOrderingType($a_ordering_type)
	{
		$this->ordering_type = $a_ordering_type;
	}
	public function getOrderingType()
	{
		return $this->ordering_type;
	}
	
	public function setImagePath($a_image_path)
	{
		$this->image_path = $a_image_path;
	}
	public function getImagePath()
	{
		return $this->image_path;
	}
	public function setImagePathWeb($a_image_path_web)
	{
		$this->image_path_web = $a_image_path_web;
	}
	public function getImagePathWeb()
	{
		return $this->image_path_web;
	}
	public function setThumbPrefix($a_thumb_prefix)
	{
		$this->thumb_prefix = $a_thumb_prefix;
	}
	public function getThumbPrefix()
	{
		return 'thumb.';
	}

	/**
	 * @param bool $graphical_output
	 */
	public function setGraphicalOutput($graphical_output)
	{
		$this->graphical_output = $graphical_output;
	}

	public function getGraphicalOutput()
	{
		return $this->graphical_output;
	}
	
	
	
	/***
	 * @param array $a_answers_array  contain objects  
	 * @param bool $shuffle
	 */
	public function setObjAnswersArray($a_answers_array, $shuffle = false)
	{
		if($shuffle == true)
		{
			shuffle($a_answers_array);
		}
		
		$i = 0;
		foreach($a_answers_array as $obj_answer)
		{
			$this->answers[$i]['random_id']        = $obj_answer->getRandomId();
			$this->answers[$i]['answertext']       = (string)$obj_answer->getAnswertext();
			$this->answers[$i]['answer_id']        = $obj_answer->getAnswerId();
			$this->answers[$i]['ordering_depth']   = $obj_answer->getOrderingDepth();

			$i++;
		}
	}

	public function renderBeginSortableContainer()
	{
		$js_include_tpl = new ilTemplate("tpl.il_as_qpl_nested_ordering_output_html.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$js_include_tpl->setCurrentBlock('begin_sortablecontainer');
		$js_include_tpl->setVariable('INSTANCE_ID', $this->getInstanceId());
		$js_include_tpl->parseCurrentBlock();

		return $js_include_tpl->get();
	}
	public function renderEndSortableContainer()
	{
		$js_include_tpl = new ilTemplate("tpl.il_as_qpl_nested_ordering_output_html.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$js_include_tpl->touchBlock('end_sortablecontainer');

		return $js_include_tpl->get();
	}

	public function renderListItem($cur_answer, $i)
	{
		$js_include_tpl = new ilTemplate("tpl.il_as_qpl_nested_ordering_output_html.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$js_include_tpl->touchBlock('begin_list_item');

		$js_include_tpl->setCurrentBlock('item_value');
		$js_include_tpl->setVariable('LIST_ITEM_VALUE', $this->renderAnswerOutput($cur_answer['answertext'], $i));
		$js_include_tpl->setVariable('LIST_ITEM_ID', $i);

		if(isset($cur_answer['ok']) && $this->getGraphicalOutput() == true)
		{
			global $lng;
			
			if($cur_answer['ok'] === true)
			{
				$icon_ok = ilUtil::getImagePath("icon_ok.svg");
				$text_ok = $lng->txt("answer_is_right");
			}
			else
			{
				$icon_ok = ilUtil::getImagePath("icon_not_ok.svg");
				$text_ok = $lng->txt("answer_is_wrong");
			}

			$js_include_tpl->setVariable('ICON_OK', $icon_ok);
			$js_include_tpl->setVariable('TEXT_OK', $text_ok);
		}
		
		$js_include_tpl->parseCurrentBlock();

		return $js_include_tpl->get();
	}
	
	public function renderEndListItem()
	{
		$js_include_tpl = new ilTemplate("tpl.il_as_qpl_nested_ordering_output_html.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$js_include_tpl->touchBlock('end_list_item');
		
		return $js_include_tpl->get();
	}

	public function renderBeginSubList()
	{
		$js_include_tpl = new ilTemplate("tpl.il_as_qpl_nested_ordering_output_html.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$js_include_tpl->touchBlock('begin_sublist');
		
		return $js_include_tpl->get();
	}

	public function renderEndSubList()
	{
		$js_include_tpl = new ilTemplate("tpl.il_as_qpl_nested_ordering_output_html.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$js_include_tpl->touchBlock('end_sublist');

		return $js_include_tpl->get();
	}

	/**
	 * @param string $text_value
	 * @param integer $i
	 * @return string
	 */
	public function renderAnswerOutput($text_value, $i)	
	{
		global $lng;
		$input_tpl = new ilTemplate("tpl.prop_nestable_value.html", true, true, "Modules/TestQuestionPool");
		
		if(strlen($text_value))
		{
			if($this->getOrderingType() == OQ_NESTED_TERMS)
			{
				$input_tpl->setCurrentBlock('nested_terms');
				$input_tpl->setVariable("VALUE", ilUtil::prepareFormOutput($text_value));
				$input_tpl->setVariable("INPUT_POST_VAR", $this->getPostVar() . "[$i]");
				$input_tpl->setVariable("NON_EDITABLE_ID", $this->getPostVar() . "[$i]");
				$input_tpl->setVariable("HVALUE", ilUtil::prepareFormOutput($text_value));

				$input_tpl->setVariable("ID", $this->getFieldId() . "[$i]");
			}
			else if($this->getOrderingType() == OQ_NESTED_PICTURES)
			{
				$input_tpl->setCurrentBlock('nested_pictures');
				
				$thumbweb = $this->getImagePathWeb() . $this->getThumbPrefix() .$text_value;
				
				$input_tpl->setVariable("PICTURE_HREF",$thumbweb);
				$input_tpl->setVariable("THUMB_ALT", $lng->txt("thumbnail"));
				$input_tpl->setVariable("THUMB_TITLE", $lng->txt("thumbnail"));
				$input_tpl->setVariable("NON_EDITABLE_ID", $this->getPostVar() . "[$i]");
				$input_tpl->setVariable("HVALUE", $text_value);
				$input_tpl->setVariable("ID", $this->getFieldId() . "[$i]");
			}
		}


		$input_tpl->parseCurrentBlock();

		return $input_tpl->get();
	}
	
	public function checkInput()
	{
		return true;
	}
	 
	public function getHtml($shuffle_depth = 0)
	{
		$html_output = '';
		$html_output .= $this->renderBeginSortableContainer();
		$html_output .= $this->renderBeginSubList();

		$i                  = 0;

		if($shuffle_depth == 1)
		{
			// this is for output in test
			foreach($this->answers as $cur_answer)
			{
				$html_output .= $this->renderListItem($cur_answer,$cur_answer['random_id']);
				$html_output .= $this->renderEndListItem();
			}
		}
		else
		{	
			$previous_depth 	= 0;
	
			$tmp_array = $this->answers;
			$tmp_array[count($tmp_array)]['ordering_depth'] = 'END_OF_LIST';
	
			foreach($this->answers as $cur_answer)
			{
				$current_depth 	= $cur_answer['ordering_depth'];
				$next_depth 	= $tmp_array[$i+1]['ordering_depth'];
	
				if($next_depth == 'END_OF_LIST')
				{
					$next_depth = 0;
				}
	
				if($previous_depth == $current_depth)
				{
					// pcn = Previous, Current, Next -> Depth
					// pcn:  000, 001, 110, 220 
					if($current_depth == $next_depth)
					{
						// (1) pcn: 000
//						echo"(1)";
						$html_output .= $this->renderListItem($cur_answer, $cur_answer['random_id']);
						$html_output .= $this->renderEndListItem();
					}
					else if($current_depth > $next_depth)
					{
						if($previous_depth == $next_depth)
						{
							// (8) pcn: 110 
//							echo"(8)";
							$html_output .= $this->renderListItem($cur_answer, $cur_answer['random_id']);
							$html_output .= $this->renderEndListItem();
							$html_output .= $this->renderEndSubList();
							$html_output .= $this->renderEndListItem();
						}
						else if($previous_depth > $next_depth)
						{
							// (12) pcn: 220 
//							echo"(12)";
							$html_output .= $this->renderListItem($cur_answer, $cur_answer['random_id']);
	
							for($openlists = $next_depth; $openlists < $current_depth; $openlists++)
							{
								$html_output .= $this->renderEndListItem();
								$html_output .= $this->renderEndSubList();
								$html_output .= $this->renderEndListItem();
							}
						}
					}
					else if($current_depth < $next_depth)
					{
						// (2) pcn: 001
//						echo"(2)";
						$html_output .= $this->renderListItem($cur_answer, $cur_answer['random_id']);
						$html_output .= $this->renderBeginSubList();
					}
				}
				else if($previous_depth > $current_depth)
				{
					if($current_depth == $next_depth)
					{
						// (6) pcn: 100  
//						echo"(6)";
						$html_output .= $this->renderListItem($cur_answer, $cur_answer['random_id']);
						$html_output .= $this->renderEndListItem();
					}
					else if($current_depth > $next_depth)
					{
						// (11) pcn: 210
//						echo"(11)";
						$html_output .= $this->renderListItem($cur_answer, $cur_answer['random_id']);
						$html_output .= $this->renderEndListItem();
						$html_output .= $this->renderEndSubList();
					}
					else if($current_depth < $next_depth)
					{
						if($previous_depth == $next_depth)
						{
							// (7) pcn: 101
//							echo"(7)";
							$html_output .= $this->renderListItem($cur_answer, $cur_answer['random_id']);
							$html_output .= $this->renderBeginSubList();
						}
						else if($previous_depth > $next_depth)
						{
							// (10) pcn: 201 
//							echo"(10)";
							$html_output .= $this->renderListItem($cur_answer, $cur_answer['random_id']);
							for($openlists = $next_depth; $openlists < $current_depth; $openlists++)
							{
								$html_output .= $this->renderEndSubList();
							}
							$html_output .= $this->renderBeginSubList();
						}
					}
				}
				else if($previous_depth < $current_depth)
				{
					if($current_depth == $next_depth)
					{
						// (4) pcn: 011  
//						echo"(4)";
						$html_output .= $this->renderListItem($cur_answer, $cur_answer['random_id']);
						$html_output .= $this->renderEndListItem();
					}
					else if($current_depth > $next_depth)
					{
						if($previous_depth == $next_depth)
						{
							// (3) pcn: 010, 
//							echo"(3)";
							$html_output .= $this->renderListItem($cur_answer, $cur_answer['random_id']);
							$html_output .= $this->renderEndListItem();
							$html_output .= $this->renderEndSubList();
							$html_output .= $this->renderEndListItem();
	
						}
						else if($previous_depth > $next_depth)
						{
							// (9) pcn: 120 
//							echo"(9)";
							$html_output .= $this->renderListItem($cur_answer, $cur_answer['random_id']);
							for($openlists = $next_depth; $openlists < $current_depth; $openlists++)
							{
								$html_output .= $this->renderEndListItem();
								$html_output .= $this->renderEndSubList();
							}
						}
					}
					else if($current_depth < $next_depth)
					{
						// (5) pcn: 012 
//						echo"(5)";
						$html_output .= $this->renderListItem($cur_answer, $cur_answer['random_id']);
						$html_output .= $this->renderBeginSubList();
					}
				}
				$previous_depth = $current_depth;
				$i++;
			}
		}
		$html_output .= $this->renderEndSubList();
		$html_output .= $this->renderEndSortableContainer();
		
		include_once "Services/jQuery/classes/class.iljQueryUtil.php";
		global $tpl;
		
		$tpl->addJavaScript("./Services/jQuery/js/jquery.nestable.js");
		iljQueryUtil::initjQuery();
		iljQueryUtil::initjQueryUI();
		$js_output = '';

		$js_include_tpl = new ilTemplate("tpl.il_as_qpl_nested_ordering_output_javascript.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$js_include_tpl->setCurrentBlock('js_include');

		$js_include_tpl->setVariable('PERFORM_JAVASCRIPT', $this->getPerformJavascript());
		$js_include_tpl->setVariable('INSTANCE_ID', $this->getInstanceId());

		$js_include_tpl->setVariable('HTML_OUTPUT', $html_output);

		$js_include_tpl->parseCurrentBlock();

		return $js_include_tpl->get();
	}
	
	/***
	 * @param $a_solution_array (contains ordering_depth, answertext)
	 * @return string
	 */
	public function getSolutionHTML($a_solution_array)
	{
		$html_output = '';
		$html_output .= $this->renderBeginSortableContainer();
		$html_output .= $this->renderBeginSubList();

		$i                  = 0;
		$previous_depth 	= 0;

			$tmp_array = $a_solution_array;
			$tmp_array[count($tmp_array)]['depth'] = 'END_OF_LIST';

			foreach($a_solution_array as $cur_answer)
			{	
				$current_depth 	= $cur_answer['depth'];
				$next_depth 	= $tmp_array[$i+1]['depth'];
				if($next_depth == 'END_OF_LIST')
				{
					$next_depth = 0;
				}

				if($previous_depth == $current_depth)
				{
					// pcn = Previous, Current, Next -> Depth
					// pcn:  000, 001, 110, 220 
					if($current_depth == $next_depth)
					{
						// (1) pcn: 000
						//					echo"(1)";
						$html_output .= $this->renderListItem($cur_answer, $i);
						$html_output .= $this->renderEndListItem();
					}
					else if($current_depth > $next_depth)
					{
						if($previous_depth == $next_depth)
						{
							// (8) pcn: 110 
							//						echo"(8)";
							$html_output .= $this->renderListItem($cur_answer, $i);
							$html_output .= $this->renderEndListItem();
							$html_output .= $this->renderEndSubList();
							$html_output .= $this->renderEndListItem();
						}
						else if($previous_depth > $next_depth)
						{
							// (12) pcn: 220 
							//						echo"(12)";
							$html_output .= $this->renderListItem($cur_answer, $i);

							for($openlists = $next_depth; $openlists < $current_depth; $openlists++)
							{
								$html_output .= $this->renderEndListItem();
								$html_output .= $this->renderEndSubList();
								$html_output .= $this->renderEndListItem();
							}
						}
					}
					else if($current_depth < $next_depth)
					{
						// (2) pcn: 001
						//					echo"(2)";
						$html_output .= $this->renderListItem($cur_answer, $i);
						$html_output .= $this->renderBeginSubList();
				
					}
				}
				else if($previous_depth > $current_depth)
				{
					if($current_depth == $next_depth)
					{
						// (6) pcn: 100  
						//					echo"(6)";
						$html_output .= $this->renderListItem($cur_answer, $i);
						$html_output .= $this->renderEndListItem();
					}
					else if($current_depth > $next_depth)
					{
						// (11) pcn: 210
						//					echo"(11)";
						$html_output .= $this->renderListItem($cur_answer, $i);
						$html_output .= $this->renderEndListItem();
						$html_output .= $this->renderEndSubList();
					}
					else if($current_depth < $next_depth)
					{
						if($previous_depth == $next_depth)
						{
							// (7) pcn: 101
							//						echo"(7)";
							$html_output .= $this->renderListItem($cur_answer, $i);
							$html_output .= $this->renderBeginSubList();
						}
						else if($previous_depth > $next_depth)
						{
							// (10) pcn: 201 
							//						echo"(10)";
							$html_output .= $this->renderListItem($cur_answer, $i);
							for($openlists = $next_depth; $openlists < $current_depth; $openlists++)
							{
								$html_output .= $this->renderEndSubList();
							}
							$html_output .= $this->renderBeginSubList();
						}
					}
				}
				else if($previous_depth < $current_depth)
				{
					if($current_depth == $next_depth)
					{
						// (4) pcn: 011  
						//					echo"(4)";
						$html_output .= $this->renderListItem($cur_answer, $i);
						$html_output .= $this->renderEndListItem();
					}
					else if($current_depth > $next_depth)
					{
						if($previous_depth == $next_depth)
						{
							// (3) pcn: 010, 
							//						echo"(3)";
							$html_output .= $this->renderListItem($cur_answer, $i);
							$html_output .= $this->renderEndListItem();
							$html_output .= $this->renderEndSubList();
							$html_output .= $this->renderEndListItem();

						}
						else if($previous_depth > $next_depth)
						{
							// (9) pcn: 120 
							//						echo"(9)";
							$html_output .= $this->renderListItem($cur_answer, $i);
							for($openlists = $next_depth; $openlists < $current_depth; $openlists++)
							{
								$html_output .= $this->renderEndListItem();
								$html_output .= $this->renderEndSubList();
							}
						}
					}
					else if($current_depth < $next_depth)
					{
						// (5) pcn: 012 
						//					echo"(5)";
						$html_output .= $this->renderListItem($cur_answer, $i);
						$html_output .= $this->renderBeginSubList();
					}
				}
				$previous_depth = $current_depth;
				$i++;
			}
		
		$html_output .= $this->renderEndSubList();
		$html_output .= $this->renderEndSortableContainer();

//		$js_include_tpl = new ilTemplate("tpl.il_as_qpl_nested_ordering_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
 
		include_once "Services/jQuery/classes/class.iljQueryUtil.php";
		global $tpl;

		$tpl->addJavaScript("./Services/jQuery/js/jquery.nestable.js");
		iljQueryUtil::initjQuery();
		iljQueryUtil::initjQueryUI();
		$js_output = '';

		$js_include_tpl = new ilTemplate("tpl.il_as_qpl_nested_ordering_output_javascript.html", TRUE, TRUE, "Modules/TestQuestionPool");

		$js_include_tpl->setCurrentBlock('js_include');
		$js_include_tpl->setVariable('PERFORM_JAVASCRIPT', $this->getPerformJavascript());

///// */

		$js_include_tpl->setVariable('HTML_OUTPUT', $html_output);

		$js_include_tpl->parseCurrentBlock();

		return $js_include_tpl->get();
	}
	
	public function setPerformJavascript($a_bool)
	{
		$this->perform_js = $a_bool;
	}
	
	public function getPerformJavascript()
	{
		return (int)$this->perform_js;
	}
}
?>
