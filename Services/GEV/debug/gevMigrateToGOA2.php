<?php
// gevMigrateToGOA2

	/*
	* gevUserUtils::getWBDAgentStatus 
	* returns "Angestellter Außendienst"
	* instead of "1 - Angestellter Außendienst"
	*/

	function killOrderPrefix($i){
		$ret = explode("-", $i);
		return trim($ret[1]);
	}



	$agent_status = array(
		 "0 - aus Rolle"
		,"1 - Angestellter Außendienst"
		,"2 - Ausschließlichkeitsvermittler"
		,"3 - Makler"
		,"4 - Mehrfachagent"
		,"5 - Mitarbeiter eines Vermittlers"
		,"6 - Sonstiges"
		,"7 - keine Zuordnung"
	);
	foreach ($agent_status as $status) {
		$sql = "UPDATE hist_user SET wbd_agent_status="
		. "'" .killOrderPrefix($status) ."'"
		. "WHERE wbd_agent_status='$status'";

		print $sql;
	}


?>
