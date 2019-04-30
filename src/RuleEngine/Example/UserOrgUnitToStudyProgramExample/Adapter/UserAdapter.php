<?php

class UserQueryAdapter /*extends Target*/
{

	public function getQuery(): string {
		$query = "SELECT * from usr_data";
	}
}

