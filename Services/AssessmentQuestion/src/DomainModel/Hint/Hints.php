<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Hint;

/**
 * Class Hints
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Hints {

	/**
	 * @var Hint[]
	 */
	private $hints;

	public function __construct() {
		$this->hints = [];
	}

	public function addHint(?Hint $hint) {
		$this->hints[] = $hint;
	}


    /**
     * @return Hint[]
     */
	public function getHints() : array {
		return $this->hints;
	}

    /**
     * @return Hint
     */
    public function getSpecificHint(int $order_number) : Hint {
        foreach($this->hints as $hint) {
            if((int) $hint->getOrderNumber() === (int) $order_number) {
                return $hint;
            }
        }
       return new Hint($order_number, '', 0);
    }
	
	public static function deserialize(string $json_data) : Hints {
	    $data = json_decode($json_data);
	    $hints = new Hints();
	    
	    foreach($data as $hint) {
	        $a_hint = new Hint($hint->order_number,$hint->content,$hint->point_deduction);
            $a_hint->deserialize($hint);
            $hints->addHint($a_hint);
	    }
	    
	    return $hints;
	}
	
	/**
	 * @param Hints $other
	 *
	 * @return bool
	 */
	public function equals(Hints $other) : bool {
	    return !is_null($other) &&
	           count($this->hints) === count($other->hints) &&
	           $this->hintsAreEqual($other);
	}
	
	public function hintsAreEqual(Hints $other) : bool {
	    /** @var Hint $my_hint */
	    foreach ($this->hints as $my_hint) {
	        $found = false;
	        
	        /** @var Hint $other_hint */
	        foreach ($other->hints as $other_hint) {
	            if ($my_hint->equals($other_hint)) {
	                    $found = true;
	                    break;
	                }
	        }
	        
	        if (!$found) {
	            return false;
	        }
	    }
	    
	    return true;
	}
}
