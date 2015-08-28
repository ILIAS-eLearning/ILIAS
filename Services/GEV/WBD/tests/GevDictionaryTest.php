<?php
chdir("/Library/WebServer/Documents/dev/4_4_generali2_new_wbd/");
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
class GevDictionaryTest extends DictionaryTestBase {
	
	public function setUp() {
		$this->dictionary = new gevWBDDictionary();
	}
}