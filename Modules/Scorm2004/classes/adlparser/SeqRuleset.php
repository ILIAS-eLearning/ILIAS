<?php

	define("RULE_TYPE_ANY",1);
	define("RULE_TYPE_EXIT",2);
	define("RULE_TYPE_POST",3);
	define("RULE_TYPE_SKIPPED",4);
	define("RULE_TYPE_DISABLED",5);
	define("RULE_TYPE_HIDDEN",6);
	define("RULE_TYPE_FORWARDBLOCK",7);
	
	class SeqRuleset{
		
		public $mRules;
		
		public function __construct($iRules) {
			
			$this->mRules=$iRules;
		}
	}
	
?>