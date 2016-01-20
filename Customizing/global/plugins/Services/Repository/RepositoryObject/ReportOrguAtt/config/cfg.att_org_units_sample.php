<?php
	$this->sum_parts = array(
				"sum_employees" => array(
					"regular" => 
						"COUNT(DISTINCT orgu.usr_id) as sum_employees"
					,"sum" => 
						"COUNT(DISTINCT usr_id) as sum_employees"
					)
			);