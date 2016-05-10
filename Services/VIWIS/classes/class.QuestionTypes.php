<?php

class QuestionTypes {
	protected static $types = array('single','multiple');

	public static function validType($type) {
		if(in_array($type, self::$types)) {
			return true;
		}
		return false;
	}
}