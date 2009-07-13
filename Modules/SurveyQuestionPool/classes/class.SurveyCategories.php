<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Class SurveyCategories
* 
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesSurveyQuestionPool
*/

class SurveyCategories
{
/**
* Category container
*
* An array containing the categories of a nominal or
* ordinal question object
*
* @var array
*/
  var $categories;

	/**
	* Constructor
	* @access	public
	*/
	function SurveyCategories()
	{
		$this->categories = array();
	}

/**
* Returns the number of categories
*
* Returns the number of categories
*
* @return integer The number of contained categories
* @access public
* @see $categories
*/
	function getCategoryCount() 
	{
		return count($this->categories);
	}

/**
* Adds a category at a given position
*
* Adds a category at a given position
*
* @param string $categoryname The name of the category
* @param integer $position The position of the category (starting with index 0)
* @access public
* @see $categories
*/
	function addCategoryAtPosition($categoryname, $position) 
	{
		if (array_key_exists($position, $this->categories))
		{
			$head = array_slice($this->categories, 0, $position);
			$tail = array_slice($this->categories, $position);
			$this->categories = array_merge($head, array($categoryname), $tail);
		}
		else
		{
			array_push($this->categories, $categoryname);
		}
	}
	
	function moveCategoryUp($index)
	{
		if ($index > 0)
		{
			$temp = $this->categories[$index-1];
			$this->categories[$index - 1] = $this->categories[$index];
			$this->categories[$index] = $temp;
		}
	}
	
	function moveCategoryDown($index)
	{
		if ($index < (count($this->categories)-1))
		{
			$temp = $this->categories[$index+1];
			$this->categories[$index + 1] = $this->categories[$index];
			$this->categories[$index] = $temp;
		}
	}

/**
* Adds a category
*
* Adds a category
*
* @param integer $categoryname The name of the category
* @access public
* @see $categories
*/
	function addCategory($categoryname) 
	{
		array_push($this->categories, $categoryname);
	}
	
/**
* Adds a category array
*
* Adds a category array
*
* @param array $categories An array with categories
* @access public
* @see $categories
*/
	function addCategoryArray($categories) 
	{
		$this->categories = array_merge($this->categories, $categories);
	}
	
/**
* Removes a category from the list of categories
*
* Removes a category from the list of categories
*
* @param integer $index The index of the category to be removed
* @access public
* @see $categories
*/
	function removeCategory($index)
	{
		unset($this->categories[$index]);
		$this->categories = array_values($this->categories);
	}

/**
* Removes many categories from the list of categories
*
* Removes many categories from the list of categories
*
* @param array $array An array containing the index positions of the categories to be removed
* @access public
* @see $categories
*/
	function removeCategories($array)
	{
		foreach ($array as $index)
		{
			unset($this->categories[$index]);
		}
		$this->categories = array_values($this->categories);
	}

/**
* Removes a category from the list of categories
*
* Removes a category from the list of categories
*
* @param string $name The name of the category to be removed
* @access public
* @see $categories
*/
	function removeCategoryWithName($name)
	{
		$index = array_search($name, $this->categories);
		$this->removeCategory($index);
	}
	
/**
* Returns the name of a category for a given index
*
* Returns the name of a category for a given index
*
* @param integer $index The index of the category
* @result string Category name
* @access public
* @see $categories
*/
	function getCategory($index)
	{
		if (array_key_exists($index, $this->categories))
		{
			return $this->categories[$index];
		}
		else
		{
			return "";
		}
	}

/**
* Returns the index of a category with a given name.
*
* Returns the index of a category with a given name.
*
* @param string $name The name of the category
* @access public
* @see $categories
*/
	function getCategoryIndex($name)
	{
		return array_search($name, $this->categories);
	}
	
	function getScale($index)
	{
		return $index + 1;
	}
	
/**
* Empties the categories list
*
* Empties the categories list
*
* @access public
* @see $categories
*/
	function flushCategories() 
	{
		$this->categories = array();
	}
		
} // END class.SurveyCategories
?>
