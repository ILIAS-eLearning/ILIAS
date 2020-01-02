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
include_once "Modules/SurveyQuestionPool/classes/class.ilSurveyCategory.php";

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
    * @var ilLogger
    */
    protected $log;

    /**
    * Category container
    *
    * An array containing the categories of a nominal or
    * ordinal question object
    *
    * @var array
    */
    public $categories;

    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        $this->categories = array();
        $this->log = ilLoggerFactory::getLogger("svy");
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
    public function getCategoryCount()
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
    public function addCategoryAtPosition($categoryname, $position, $other = 0, $neutral = 0, $label = null)
    {
        if (array_key_exists($position, $this->categories)) {
            $head = array_slice($this->categories, 0, $position);
            $tail = array_slice($this->categories, $position);
            $this->categories = array_merge($head, array(new ilSurveyCategory($categoryname, $other, $neutral, $label)), $tail);
        } else {
            array_push($this->categories, new ilSurveyCategory($categoryname, $other, $neutral, $label));
        }
    }
    
    public function moveCategoryUp($index)
    {
        if ($index > 0) {
            $temp = $this->categories[$index-1];
            $this->categories[$index - 1] = $this->categories[$index];
            $this->categories[$index] = $temp;
        }
    }
    
    public function moveCategoryDown($index)
    {
        if ($index < (count($this->categories)-1)) {
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
    public function addCategory($categoryname, $other = 0, $neutral = 0, $label = null, $scale = null)
    {
        array_push($this->categories, new ilSurveyCategory($categoryname, $other, $neutral, $label, $scale));
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
    public function addCategoryArray($categories)
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
    public function removeCategory($index)
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
    public function removeCategories($array)
    {
        foreach ($array as $index) {
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
    public function removeCategoryWithName($name)
    {
        foreach ($this->categories as $index => $category) {
            if (strcmp($category->title, $name) == 0) {
                $this->removeCategory($index);
                return;
            }
        }
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
    public function getCategory($index)
    {
        if (array_key_exists($index, $this->categories)) {
            return $this->categories[$index];
        } else {
            return "";
        }
    }

    /**
    * Returns the name of a category for a given index
    *
    * @param integer $scale The scale of the category
    * @return string Category object
    */
    public function getCategoryForScale($scale)
    {
        foreach ($this->categories as $cat) {
            if ($cat->scale == $scale) {
                return $cat;
            }
        }
        return null;
    }

    /**
    * Returns the index of a category with a given name.
    *
    * @param string $name The name of the category
    * @access public
    * @see $categories
    */
    public function getCategoryIndex($name)
    {
        foreach ($this->categories as $index => $category) {
            if (strcmp($category->title, $name) == 0) {
                return $index;
            }
        }
        return null;
    }

    /**
    * Returns the index of a category
    *
    * @param string $category The category object
    * @access public
    * @see $categories
    */
    public function getIndex($category)
    {
        foreach ($this->categories as $index => $cat) {
            if ($cat == $category) {
                return $index;
            }
        }
        return null;
    }
    
    public function getNewScale()
    {
        $max = 0;
        foreach ($this->categories as $index => $category) {
            if (is_object($category) && $category->scale > 0) {
                if ($category->scale > $max) {
                    $max = $category->scale;
                }
            }
        }
        return $max+1;
    }
    
    public function getScale($index)
    {
        $obj = $this->categories[$index];
        if (is_object($obj) && $obj->scale > 0) {
            $this->log->debug("getScale has scale =" . $obj->scale);
            return $obj->scale;
        } else {
            $obj->scale = $this->getNewScale();
            $this->log->debug("getScale needed new scale, scale =" . $obj->scale);
            return $obj->scale;
        }
    }
    
    /**
    * Empties the categories list
    *
    * Empties the categories list
    *
    * @access public
    * @see $categories
    */
    public function flushCategories()
    {
        $this->categories = array();
    }

    /**
     * Get categories
     *
     * @param
     * @return
     */
    public function getCategories()
    {
        return $this->categories;
    }
}
