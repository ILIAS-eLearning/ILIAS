<?php
	define("EVALUATE_UNKNOWN",0);
	define("EVALUATE_TRUE",1);
	define("EVALUATE_FALSE",-1);
	define("COMBINATION_ALL","all");
	define("COMBINATION_ANY","any");
	
	class SeqConditionSet{
		
		public $mCombination = null;
		
		//convert vector to array
		public $mConditions = null;
  		public $mRetry =  false;
 		public $mRollup = false;

		public function __construct() {

		}
		
		public function SeqConditionSet($iRollup)
	   	{
	      	$this->mRollup = $iRollup;
	   	}

		
	}
