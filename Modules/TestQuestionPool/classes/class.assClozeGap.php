<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. | 
   +----------------------------------------------------------------------------+
*/

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for cloze question gaps
* 
* assClozeGap is a class for the abstraction of cloze gaps. It represents a
* text gap.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/

class assClozeGap 
{
/**
* Type of gap
* 
* An integer value indicating the type of the gap
* 0 == text gap, 1 == select gap, 2 == numeric gap
*
* @var int
*/
	var $type;

/**
* List of items in the gap
* 
* List of items in the gap
*
* @var array
*/
	var $items;

	/**
	* Indicates if the items should be shuffled in the output
	* 
	* @var boolean
	*/
	var $shuffle;

/**
* assClozeGap constructor
* 
* @param string $answertext A string defining the answer text
* @param double $points The number of points given for the selected answer
* @param boolean $correctness A boolean value indicating the correctness of the answer
* @param integer $order A nonnegative value representing a possible display or sort order
* @param integer $cloze_type An integer representing the answer type
* @access public
*/
	function assClozeGap($a_type)
	{
		$this->type = $a_type;
		$this->items = array();
		$this->shuffle = FALSE;
	}
  
/**
* Gets the cloze gap type
* 
* Gets the cloze gap type
*
* @return integer cloze gap type
* @access public
* @see $type
*/
	function getType() 
	{
		return $this->type;
	}

/**
* Sets the cloze gap type
* 
* Sets the cloze gap type
*
* @param integer $a_type Cloze gap type
* @access public
* @see $type
*/
  function setType($a_type = 0) 
	{
		$this->type = $a_type;
	}

/**
* Gets the items of a cloze gap
* 
* Gets the items of a cloze gap
*
* @return array The list of items
* @access public
* @see $items
*/
  function getItems() 
	{
		if ($this->shuffle)
		{
			return $this->arrayShuffle($this->items);
		}
		else
		{
			return $this->items;
		}
	}

	/**
	* Gets the items of a cloze gap
	* 
	* Gets the items of a cloze gap
	*
	* @return array The list of items
	* @access public
	* @see $items
	*/
	function getItemsRaw()
	{
		return $this->items;
	}
  
/**
* Gets the item count
* 
* Gets the item count
*
* @return integer The item count
* @access public
* @see $items
*/
	function getItemCount() 
	{
		return count($this->items);
	}

/**
* Adds a gap item
* 
* Adds a gap item
*
* @param object $a_item Cloze gap item
* @access public
* @see $items
*/
	function addItem($a_item) 
	{
		array_push($this->items, $a_item);
	}

/**
* Sets the points for a given item
* 
* Sets the points for a given item
*
* @param integer $order Order of the item
* @param double $points Points of the item
* @access public
* @see $items
*/
	function setItemPoints($order, $points) 
	{
		foreach ($this->items as $key => $item)
		{
			if ($item->getOrder() == $order)
			{
				$item->setPoints($points);
			}
		}
	}

	/**
	* Deletes an item at a given index
	* 
	* Deletes an item at a given index
	*
	* @param integer $0order Order of the item
	* @access public
	* @see $items
	*/
	function deleteItem($order) 
	{
		if (array_key_exists($order, $this->items))
		{
			unset($this->items[$order]);
			$order = 0;
			foreach ($this->items as $key => $item)
			{
				$this->items[$key]->setOrder($order);
				$order++;
			}
		}
	}

/**
* Sets the lower bound for a given item
* 
* Sets the lower bound for a given item
*
* @param integer $order Order of the item
* @param double $bound Lower bounds of the item
* @access public
* @see $items
*/
	function setItemLowerBound($order, $bound) 
	{
		foreach ($this->items as $key => $item)
		{
			if ($item->getOrder() == $order)
			{
				$item->setLowerBound($bound);
			}
		}
	}

/**
* Sets the upper bound for a given item
* 
* Sets the upper bound for a given item
*
* @param integer $order Order of the item
* @param double $bound Upper bound of the item
* @access public
* @see $items
*/
	function setItemUpperBound($order, $bound) 
	{
		foreach ($this->items as $key => $item)
		{
			if ($item->getOrder() == $order)
			{
				$item->setUpperBound($bound);
			}
		}
	}

/**
* Gets the item with a given index
* 
* Gets the item with a given index
*
* @param integer $a_index Item index
* @access public
* @see $items
*/
	function getItem($a_index) 
	{
		if (array_key_exists($a_index, $this->items))
		{
			return $this->items[$a_index];
		}
		else
		{
			return NULL;
		}
	}

/**
* Removes all gap items
* 
* Removes all gap items
*
* @access public
* @see $items
*/
	function clearItems() 
	{
		$this->items = array();
	}

	/**
	* Sets the shuffle state of the items
	* 
	* Sets the shuffle state of the items
	*
	* @param boolean $a_shuffle Shuffle state
	* @access public
	* @see $shuffle
	*/
	function setShuffle($a_shuffle = TRUE) 
	{
		$this->shuffle = $a_shuffle ? TRUE : FALSE;
	}

	/**
	* Gets the shuffle state of the items
	* 
	* Gets the shuffle state of the items
	*
	* @return boolean Shuffle state
	* @access public
	* @see $shuffle
	*/
	function getShuffle() 
	{
		return $this->shuffle;
	}

	/**
	* Shuffles the values of a given array
	*
	* Shuffles the values of a given array
	*
	* @param array $array An array which should be shuffled
	* @access public
	*/
	function arrayShuffle($array)
	{
		mt_srand((double)microtime()*1000000);
		$i = count($array);
		if ($i > 0)
		{
			while(--$i)
			{
				$j = mt_rand(0, $i);
				if ($i != $j)
				{
					// swap elements
					$tmp = $array[$j];
					$array[$j] = $array[$i];
					$array[$i] = $tmp;
				}
			}
		}
		return $array;
	}

	/**
	* Returns the maximum width of the gap
	*
	* Returns the maximum width of the gap
	*
	* @return integer The maximum width of the gap defined by the longest answer
	* @access public
	*/
	function getMaxWidth()
	{
		$maxwidth = 0;
		foreach ($this->items as $item)
		{
			if (strlen($item->getAnswerText()) > $maxwidth)
			{
				$maxwidth = strlen($item->getAnswerText());
			}
		}
		return $maxwidth;
	}
	
	/**
	* Returns the indexes of the best solutions for the gap
	*
	* Returns the indexes of the best solutions for the gap
	*
	* @return array The indexs of the best solutions
	* @access public
	*/
	function getBestSolutionIndexes()
	{
		$maxpoints = 0;
		foreach ($this->items as $key => $item)
		{
			if ($item->getPoints() > $maxpoints)
			{
				$maxpoints = $item->getPoints();
			}
		}
		$keys = array();
		foreach ($this->items as $key => $item)
		{
			if ($item->getPoints() == $maxpoints)
			{
				array_push($keys, $key);
			}
		}
		return $keys;
	}
}

?>