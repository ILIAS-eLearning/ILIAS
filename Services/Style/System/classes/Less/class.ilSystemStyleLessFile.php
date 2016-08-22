<?php
require_once("./Services/Style/System/classes/Less/class.ilSystemStyleLessItem.php");
require_once("./Services/Style/System/classes/Less/class.ilSystemStyleLessCategory.php");
require_once("./Services/Style/System/classes/Less/class.ilSystemStyleLessComment.php");
require_once("./Services/Style/System/classes/Less/class.ilSystemStyleLessVariable.php");


/***
 * This data abstracts a complete less file. A less file is composed of categories, variables and random comments
 * (unclassified information)
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$
 *
 */
class ilSystemStyleLessFile
{
	/**
	 * List of items (variabe, category or comment) this file contains
	 *
	 * @var ilSystemStyleLessVariable[]
	 */
	protected $items = array();

	/**
	 * Separated array with all comments ids (performance reasons)
	 *
	 * @var array
	 */
	protected $comments_ids = array();

	/**
	 * Separated array with all variable ids (performance reasons)
	 *
	 * @var array
	 */
	protected $variables_ids= array();

	/**
	 * Separated array with all category ids (performance reasons)
	 *
	 * @var array
	 */
	protected $categories_ids = array();

	/**
	 * Complete path the the variables file on the file system
	 *
	 * @var string
	 */
	protected $less_variables_file_path = "";

	/**
	 * KitchenSinkLessFile constructor.
	 * @param string $less_variables_file
	 */
	public function __construct($less_variables_file)
	{
		$this->less_variables_file = $less_variables_file;
		$this->read();
	}

	/**
	 * Reads the file from the file system
	 *
	 * @throws ilSystemStyleException
	 */
	public function read(){
		$last_variable_comment = null;
		$last_category_id = null;
		$last_category_name = null;

		$regex_category = '/\/\/==\s(.*)/';
		$regex_category_comment = '/\/\/##\s(.*)/';
		$regex_variable = '/^@(.*)/';
		$regex_variable_comment = '/\/\/\*\*\s(.*)/';
		$regex_variable_name = '/(?:@)(.*)(?:\:)/';
		$regex_variable_value = '/(?::)(.*)(?:;)/';
		$regex_variable_references = '/(?:@)([a-zA-Z0-9_-]*)/';
		try{
			$handle = fopen($this->getLessVariablesFile(), "r");
		}catch(Exception $e){
			throw new ilSystemStyleException(ilSystemStyleException::FILE_OPENING_FAILED, $this->getLessVariablesFile());
		}


		if ($handle) {
			$line_number = 1;
			//Reads file line by line
			while (($line = fgets($handle)) !== false) {


				if(preg_match($regex_category, $line, $out)){
					//Check Category
					$last_category_id = $this->addItem(new ilSystemStyleLessCategory($out[1]));
					$last_category_name = $out[1];
					//Line bellow Category name belongs to Category
					fgets($handle);
				} else if(preg_match($regex_category_comment, $line, $out)){
					//Check Comment Category
					$last_category = $this->getItemById($last_category_id);
					$last_category->setComment($out[1]);
				} else if(preg_match($regex_variable_comment, $line, $out)){
					//Check Variables Comment
					$last_variable_comment = $out[1];
				} else if(preg_match($regex_variable, $line, $out)){
					//Check Variables

					//Name
					preg_match($regex_variable_name, $out[0], $variable);

					//Value
					preg_match($regex_variable_value, $line, $value);

					//References
					$temp_value = $value[0];
					$references = array();
					while(preg_match($regex_variable_references,$temp_value,$reference)){
						$references[] = $reference[1];
						$temp_value = str_replace($reference,"",$temp_value);
					}

					$this->addItem(new ilSystemStyleLessVariable(
						$variable[1],
						ltrim ( $value[1] ," \t\n\r\0\x0B" ),
						$last_variable_comment,
						$last_category_name,
						$references));
					$last_variable_comment = "";

				}else{
					$this->addItem(new ilSystemStyleLessComment($line));
				}


				$line_number++;
			}
			fclose($handle);
		} else {
			throw new ilSystemStyleException(ilSystemStyleException::FILE_OPENING_FAILED);
		}
	}

	/**
	 * Write the complete file back to the file system (including comments and random content)
	 */
	public function write(){
		file_put_contents($this->getLessVariablesFile(),$this->getContent());
	}

	/**
	 * @return string
	 */
	public function getContent(){
		$output = "";

		foreach($this->items as $item){
			$output .= $item->__toString();
		}
		return $output;
	}

	/**
	 * @param ilSystemStyleLessItem $item
	 * @return int
	 */
	public function addItem(ilSystemStyleLessItem $item){
		$id = array_push($this->items,$item)-1;


		if(get_class($item)=="ilSystemStyleLessComment"){
			$this->comments_ids[] = $id;
		}else if(get_class($item)=="ilSystemStyleLessCategory"){
			$this->categories_ids[] = $id;
		}else if(get_class($item)=="ilSystemStyleLessVariable"){
			$this->variables_ids[] = $id;
		}

		return $id;
	}

	/**
	 * @return ilSystemStyleLessCategory[]
	 */
	public function getCategories(){
		$categories = array();

		foreach($this->categories_ids as $category_id){
			$categories[] = $this->items[$category_id];
		}

		return $categories;

	}

	/**
	 * @param string $category
	 * @return ilSystemStyleLessVariable[]|null
	 */
	public function getVariablesPerCategory($category = ""){
		$variables = array();

		foreach($this->variables_ids as $variables_id){
			if(!$category || $this->items[$variables_id]->getCategoryName() == $category){
				$variables[] = $this->items[$variables_id];
			}
		}

		return $variables;
	}

	/**
	 * @param $id
	 * @return ilSystemStyleLessVariable
	 */
	public function getItemById($id){
		return $this->items[$id];
	}

	/**
	 * @param string $name
	 * @return ilSystemStyleLessVariable|null
	 */
	public function getVariableByName($name = ""){
		foreach($this->variables_ids as $variables_id){
			if($this->items[$variables_id]->getName() == $name){
				return $this->items[$variables_id];
			}
		}
		return null;

	}

	/**
	 * @param $variable_name
	 * @return array
	 */
	public function getReferencesToVariable($variable_name){
		$references = [];

		foreach($this->variables_ids as $id){
			foreach($this->items[$id]->getReferences() as $reference){
				if($variable_name == $reference)
				$references[] = $this->items[$id]->getName();
			}
		}
		return $references;
	}

	/**
	 * @param $variable_name
	 * @return string
	 */
	public function getReferencesToVariableAsString($variable_name){
		$references_string = "";
		foreach($this->getReferencesToVariable($variable_name) as $reference){
			$references_string .= "$reference; ";
		}
		return $references_string;
	}

	/**
	 * @return string
	 */
	public function getLessVariablesFile()
	{
		return $this->less_variables_file;
	}

	/**
	 * @param string $less_variables_file
	 */
	public function setLessVariablesFile($less_variables_file)
	{
		$this->less_variables_file = $less_variables_file;
	}

	/**
	 * @return ilSystemStyleLessVariable[]
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @return array
	 */
	public function getCommentsIds()
	{
		return $this->comments_ids;
	}

	/**
	 * @return array
	 */
	public function getVariablesIds()
	{
		return $this->variables_ids;
	}

	/**
	 * @return array
	 */
	public function getCategoriesIds()
	{
		return $this->categories_ids;
	}
}