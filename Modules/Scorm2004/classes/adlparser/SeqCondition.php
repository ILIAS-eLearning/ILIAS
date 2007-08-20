<?php
	define("SATISFIED","satisfied");
	define("OBJSTATUSKNOWN","objectiveStatusKnown");
	define("OBJMEASUREKNOWN","objectiveMeasureKnown");
	define("OBJMEASUREGRTHAN","objectiveMeasureGreaterThan");
	define("OBJMEASURELSTHAN","objectiveMeasureLessThan");
	define("COMPLETED","completed");
	define("PROGRESSKNOWN","activityProgressKnown");
	define("ATTEMPTED","attempted");
	define("ATTEMPTSEXCEEDED","attemptLimitExceeded");
	define("TIMELIMITEXCEEDED","timeLimitExceeded");
	define("OUTSIDETIME","outsideAvailableTimeRange");
	define("ALWAYS","always");
	define("NEVER","never");
	
	class SeqCondition{
		
	   public $mCondition = null;
	   public $mNot = false;
	   public $mObjID = null;
	   public $mThreshold = 0.0;
	
		public function __construct() {

		}
		
	}


?>
