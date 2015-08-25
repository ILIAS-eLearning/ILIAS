<?php
require_once(__DIR__."/../classes/Dictionary/class.gevDictionary.php");
class GevDictionaryTest extends DictionaryTestBase {
	
	public function setUp() {
		$this->dictionary = new gevDictionary();
	}
}