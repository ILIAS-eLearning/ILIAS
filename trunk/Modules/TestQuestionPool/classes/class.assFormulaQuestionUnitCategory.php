<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Formula Question Unit Category
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version	$Id: class.assFormulaQuestionUnitCategory.php 404 2009-04-27 04:56:49Z hschottm $
 * @ingroup ModulesTestQuestionPool
 */
class assFormulaQuestionUnitCategory
{
	/**
	 * @var int
	 */
	private $id = 0;

	/**
	 * @var string
	 */
	private $category = '';

	/**
	 * @var int
	 */
	private $question_fi = 0;
	
	/**
	 * @param int $id Category id
	 * @param string $category Category name
	  * @param int $question_fi Question id context
	*/
	public function __construct() 
	{
	}

	/**
	 * @param array $data
	 */
	public function initFormArray(array $data)
	{
		$this->id          = $data['category_id'];
		$this->category    = $data['category'];
		$this->question_fi = $data['question_fi'];
	}

	/**
	 * @param $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param $category
	 */
	public function setCategory($category)
	{
		$this->category = $category;
	}

	/**
	 * @return string
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * @param int $question_fi
	 */
	public function setQuestionFi($question_fi)
	{
		$this->question_fi = $question_fi;
	}

	/**
	 * @return int
	 */
	public function getQuestionFi()
	{
		return $this->question_fi;
	}

	/**
	 * @return string
	 */
	public function getDisplayString()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$category = $this->getCategory();
		if(strcmp('-qpl_qst_formulaquestion_' . $category . '-', $lng->txt('qpl_qst_formulaquestion_' . $category)) != 0)
		{
			$category = $lng->txt('qpl_qst_formulaquestion_' . $category);
		}
		return $category;
	}
}
