<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/class.assClozeGap.php";

/**
* Class for cloze question select gaps
* 
* assClozeSelectGap is a class for the abstraction of cloze select gaps. It represents a
* select gap.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/

class assClozeSelectGap extends assClozeGap
{
/**
* Indicates if the items should be shuffled in the output
* 
* @var boolean
*/
  var $shuffle;

/**
* assClozeSelectGap constructor
* 
* @param integer $a_type An integer representing the gap type
* @access public
*/
  function assClozeSelectGap($a_type)
  {
    $this->type = $a_type;
		$this->items = array();
		$this->shuffle = TRUE;
  }
  
/**
* Gets the shuffle state of the items
* 
* Gets the shuffle state of the items
*
* @return boolean shuffle state
* @access public
* @see $shuffle
*/
  function getShuffle() 
	{
    return $this->shuffle;
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
  function setType($a_shuffle = TRUE) 
	{
    $this->shuffle = $a_shuffle ? TRUE : FALSE;
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
}

?>