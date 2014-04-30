<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
 * Class for cloze question gaps
 *
 * assClozeGap is a class for the abstraction of cloze gaps. It represents a text gap.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
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
	 * @var int $type
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
	
	private $gap_size = 0;

	/**
	 * assClozeGap constructor
	 *
	 * @param int $a_type
	 *
	 */
	public function assClozeGap($a_type)
	{
		$this->type = $a_type;
		$this->items = array();
		$this->shuffle = true;
	}

	/**
	 * Gets the cloze gap type
	 *
	 * Gets the cloze gap type
	 *
	 * @return integer cloze gap type
	 *
	 * @see $type for mapping.
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Sets the cloze gap type
	 *
	 * @param integer $a_type cloze gap type
	 *
	 * @see $type for mapping.
	 */
	public function setType($a_type = 0)
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
		$order = $a_item->getOrder();
		if (array_key_exists($order, $this->items))
		{
			$newitems = array();
			for ($i = 0; $i < $order; $i++)
			{
				array_push($newitems, $this->items[$i]);
			}
			array_push($newitems, $a_item);
			for ($i = $order; $i < count($this->items); $i++)
			{
				array_push($newitems, $this->items[$i]);
			}
			$i = 0;
			foreach ($newitems as $idx => $item)
			{
				$newitems[$idx]->setOrder($i);
				$i++;
			}
			$this->items = $newitems;
		}
		else
		{
			array_push($this->items, $a_item);
		}
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
	 */
	public function setShuffle($a_shuffle = true)
	{
		$this->shuffle = (bool) $a_shuffle;
	}

	/**
	 * Gets the shuffle state of the items
	 *
	 * @return boolean Shuffle state
	 */
	public function getShuffle()
	{
		return $this->shuffle;
	}

	/**
	 * Shuffles the values of a given array
	 *
	 * Shuffles the values of a given array
	 *
	 * @param array $array An array which should be shuffled
	 * @return array
	 * @access public
	 *
	 * @TODO: Figure out why this method exists. (See note)
	 * MBecker: PHP knows the function shuffle since 4.2 which is out
	 * since April 2002. Still, Helmut built this function in
	 * 2007 with rev. 13281 ... This needs investigation.
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
			if (strlen($item->getAnswertext()) > $maxwidth)
			{
				$maxwidth = strlen($item->getAnswertext());
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
	
	function getBestSolutionOutput()
	{
		global $lng;
		switch ($this->getType())
		{
			case CLOZE_TEXT:
			case CLOZE_SELECT:
				$best_solutions = array();
				foreach ($this->getItems() as $answer)
				{
					if (is_array($best_solutions[$answer->getPoints()]))
					{
						array_push($best_solutions[$answer->getPoints()], $answer->getAnswertext());
					}
					else
					{
						$best_solutions[$answer->getPoints()] = array();
						array_push($best_solutions[$answer->getPoints()], $answer->getAnswertext());
					}
				}
				krsort($best_solutions, SORT_NUMERIC);
				reset($best_solutions);
				$found = current($best_solutions);
				return join(" " . $lng->txt("or") . " ", $found);
				break;
			case CLOZE_NUMERIC:
				$maxpoints = 0;
				$foundvalue = "";
				foreach ($this->getItems() as $answer)
				{
					if ($answer->getPoints() >= $maxpoints)
					{
						$maxpoints = $answer->getPoints();
						$foundvalue = $answer->getAnswertext();
					}
				}
				return $foundvalue;
				break;
			default:
				return "";
		}
	}

	/**
	 * @param integer $gap_size
	 */
	public function setGapSize( $gap_size)
	{
		$this->gap_size = $gap_size;
	}

	/**
	 * @return int
	 */
	public function getGapSize()
	{
		return $this->gap_size;
	}
}