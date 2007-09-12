<?php

define("SEQ_ACTION_NOACTION","noaction");
define("SEQ_ACTION_IGNORE","ignore");
define("SEQ_ACTION_SKIP","skip");
define("SEQ_ACTION_DISABLED","disabled");
define("SEQ_ACTION_HIDEFROMCHOICE","hiddenFromChoice");
define("SEQ_ACTION_FORWARDBLOCK","stopForwardTraversal");
define("SEQ_ACTION_EXITPARENT","exitParent");
define("SEQ_ACTION_EXITALL","exitAll");
define("SEQ_ACTION_RETRY","retry");
define("SEQ_ACTION_RETRYALL","retryAll");
define("SEQ_ACTION_CONTINUE","continue");
define("SEQ_ACTION_PREVIOUS","previous");
define("SEQ_ACTION_EXIT","exit");


class SeqRule {
	
	public $mAction=SEQ_ACTION_IGNORE;
	public $mConditions=null;
	
	public function __construct() {
			
	}
	
}

?>