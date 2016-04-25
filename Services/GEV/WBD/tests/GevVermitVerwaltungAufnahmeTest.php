<?php
require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestVermitVerwaltungAufnahme.php");
class GevVermitVerwaltungAufnahmeTest extends RequestTestBase {
	protected $backupGlobals = FALSE;

	public function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		$data = array("email"=>"shecken@cat06.de"
					  ,"mobile_phone_nr"=>"0162/9800608"
					  ,"birthday"=>"1981-06-19"
					  ,"bwv_id"=>"1212-2323-23-2323"
					  ,"user_id"=>3215
					  ,"row_id"=>35214
					  ,"lastname"=>"Hecken"
					  ,"firstname"=>"Stefan"
					);

		$this->request = gevWBDRequestVermitVerwaltungAufnahme::getInstance($data);
	}

	public function test_isImplmentedRequest() {
		$this->assertInstanceOf("gevWBDRequestVermitVerwaltungAufnahme",$this->request);
	}

	public function xml_response_error() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
							.'<soap:Body>'
								.'<soap:Fault>'
									.'<faultcode>soap:Server</faultcode>'
									.'<faultstring>Der Benutzer wurde von einem anderen TP angelegt: 5702136776</faultstring>'
									.'<detail>'
										.'<ns1:ExterneDoubletteException xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/" />'
									.'</detail>'
								.'</soap:Fault>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				))
			);
	}
}