<?php
	class gevWBDDataCollectorTest extends PHPUnit_Framework_TestCase {
		public function setUp() {
			$this->data_collector = new gevWBDDataCollector;
		}

		/*public function error_with_crs_id() {
			return array(
						 array(array('mandatory field missing: study_type_selection','new_user',1,0,10), new gevWBDError('mandatory field missing: study_type_selection','new_user',1,0,10))
						,array(array('mandatory field missing: study_type_selection','new_user',1,2,20), new gevWBDError('mandatory field missing: study_type_selection','new_user',1,2,20))
						,array(array('mandatory field missing: study_type_selection','new_user',4,2,30), new gevWBDError('mandatory field missing: study_type_selection','new_user',4,2,30))
						,array(array('mandatory field missing: study_type_selection','new_user',1,2,0), new gevWBDError('mandatory field missing: study_type_selection','new_user',1,2,0))
						);
		}	*/
		
		/**
	    * @dataProvider error_with_crs_id_zero
	    */
	    /*
		public function test_error_1($error_data,$error) {
			$this->assertTrue($this->data_collector->error($error) > 0);
		}*/

		public function test_create_new_user_list() {
			$this->data_collector->createNewUserList();
			$i = 0;
			while($this->data_collector->getNextRecord()) {
				$i++;
			}		
			$this->assertEqual($i,97);
		}
		
		public function test_create_update_user_list_after_change() {
			$this->data_collector->createUpdatedUserList();
			$i = 0;
			while($this->data_collector->getNextRecord()) {
				$i++;
			}		
			$this->assertEqual($i,0);
		}

		public function test_create_update_user_list_after_change() {

			$users_to_change = array(6356,6426,6482);

			$this->data_collector->createUpdatedUserList();
			$i = 0;
			while($this->data_collector->getNextRecord()) {
				$i++;
			}		
			$this->assertEqual($i,3);
		}

	}
?>