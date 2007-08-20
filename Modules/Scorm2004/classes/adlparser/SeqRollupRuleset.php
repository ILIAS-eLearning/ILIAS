<?php
	
	class SeqRollupRuleset {
		
	 	public $mRollupRules = null;

		public $mIsSatisfied = false;

		public $mIsNotSatisfied = false;

	 	public $mIsCompleted = false;

		public $mIsIncomplete = false;
		
		
   		public function __construct($iRules){
	 		$this->mRollupRules = $iRules;
   		}
     
   
	}
?>
