<?php
	define("ROLLUP_ACTION_NOCHANGE",0);
	define("ROLLUP_ACTION_SATISFIED",1);
	define("ROLLUP_ACTION_NOTSATISFIED",2);
	define("ROLLUP_ACTION_COMPLETED",3);
	define("ROLLUP_ACTION_INCOMPLETE",4);
	
	define("ROLLUP_CONSIDER_ALWAYS","always");
	define("ROLLUP_CONSIDER_ATTEMPTED","ifAttempted");
	define("ROLLUP_CONSIDER_NOTSKIPPED","ifNotSkipped");
	define("ROLLUP_CONSIDER_NOTSUSPENDED","ifNotSuspended");

	define("ROLLUP_SET_ALL","all");
	define("ROLLUP_SET_ANY","any");
	define("ROLLUP_SET_NONE","none");
	define("ROLLUP_SET_ATLEASTCOUNT","atLeastCount");
	define("ROLLUP_SET_ATLEASTPERCENT","atLeastPercent");
	
	class SeqRollupRule {
		
		public $mAction = ROLLUP_ACTION_SATISFIED;
		
		public $mChildActivitySet = ROLLUP_SET_ALL;
   
		public $mMinCount = 0;
			
		public $mMinPercent = 0.0;
   		
		public $mConditions = null;
		
		public function __construct() {
				//$this->mRules=$iRules;
		}
		
		public function setRollupAction($iAction)  {
      	  if ( $iAction == "satisfied") 
	      {
	         $this->mAction = ROLLUP_ACTION_SATISFIED;
	      }
	      else if ( $iAction=="notSatisfied" )
	      {
	         $this->mAction = ROLLUP_ACTION_NOTSATISFIED;
	      }
	      else if ( $iAction=="completed" )
	      {
	         $this->mAction = ROLLUP_ACTION_COMPLETED;
	      }
	      else if ( $iAction=="incomplete") 
	      {
	         $this->mAction = ROLLUP_ACTION_INCOMPLETE;
	      }
	   }
		
	}
	
?>
