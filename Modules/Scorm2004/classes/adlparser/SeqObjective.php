<?php
	class SeqObjective{
		
		public $mObjID = "_primary_";
		
		public $mSatisfiedByMeasure = false;
		
		public $mActiveMeasure = true;
		
		public $mMinMeasure = 1.0;
   		
		public $mContributesToRollup = false;
   		
		public $mMaps = null;	
		
		public function __construct() {

		}
	}
?>
