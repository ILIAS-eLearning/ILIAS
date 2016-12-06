<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class represents an ordering element for assOrderingQuestion
*
* @author		Björn Heyser <bheyser@databay.de>
* @version		$Id$
* @package		Modules/TestQuestionPool
*/
class ilAssOrderingElement
{
	/**
	 * this identifier is simply the database row id
	 * it should not be used at any place
	 * 
	 * it was never initialised in this object
	 * up to now (compare revision)
	 * 
	 * @var integer
	 */
	public $id = -1;
	
	/**
	 * constant boundaries for random id generations
	 */
	const RANDOM_ID_RANGE_LOWER_BOUND = 1;
	const RANDOM_ID_RANGE_UPPER_BOUND = 100000;

	/**
	 * this identifier is generated randomly
	 * it is recycled for known elements
	 * 
	 * the main purpose is to have a key that does not make the solution
	 * derivable and is therefore useable in the examines working form
	 * 
	 * @var integer
	 */
	protected $randomIdentifier;
	
	/**
	 * this identifier is used to identify elements and is stored
	 * together with the set position and indentation
	 * 
	 * this happens for the examine's submit data as well, an order index
	 * is build and the elements are assigned using this identifier
	 * 
	 * it is an integer sequence starting at 0 that increments
	 * with every added element while obsolete numbers are not recycled
	 * 
	 * @var integer
	 */
	protected $solutionIdentifier = null;
	
	/**
	 * the correct width of indentation for the element
	 * 
	 * @var integer
	 */
	protected $indentation = null;
	
	/**
	 * the correct position in the ordering sequence
	 *
	 * @var integer
	 */
	protected $position = null;
	
	/**
	 * @var string
	 */
	protected $content = null;
	
	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}
	
	/**
	 * @return integer $randomIdentifier
	 */
	public function getRandomIdentifier() 
	{
		return $this->randomIdentifier;
	}
	
	/**
	 * @param $randomIdentifier
	 */
	public function setRandomIdentifier($randomIdentifier) 
	{
		$this->randomIdentifier = $randomIdentifier;
	}
	
	/**
	 * @return int
	 */
	public function getSolutionIdentifier()
	{
		return $this->solutionIdentifier;
	}
	
	/**
	 * @param int $solutionIdentifier
	 */
	public function setSolutionIdentifier($solutionIdentifier)
	{
		$this->solutionIdentifier = $solutionIdentifier;
	}
	
	/**
	 * @param int $indentation
	 */
	public function setIndentation($indentation)
	{
		$this->indentation = $indentation;
	}
	
	/**
	 * @return int
	 */
	public function getIndentation()
	{
		return $this->indentation;
	}
	
	/**
	 * @return int
	 */
	public function getPosition()
	{
		return $this->position;
	}
	
	/**
	 * @param int $position
	 */
	public function setPosition($position)
	{
		$this->position = $position;
	}
	
	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	 * @param string $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	/**
	 * @param array $excludeRandomIds
	 * @return int $generatedRandomId
	 */
	public static function generateRandomId($excludeRandomIds = array())
	{
		do
		{
			$randomId = mt_rand(
				self::RANDOM_ID_RANGE_LOWER_BOUND, self::RANDOM_ID_RANGE_UPPER_BOUND
			);
		}
		while( in_array($randomId, $excludeRandomIds) );
		
		return $randomId;
	}
}
