<?php
require_once(__DIR__."/../classes/Dictionary/class.gevWBDDictionary.php");
class GevDictionaryTest extends DictionaryTestBase {
	
	public function setUp() {
		$this->dictionary = new gevWBDDictionary();
	}
}