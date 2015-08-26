<?php
require_once("/Library/WebServer/Documents/dev/4_4_generali2_new_wbd/Services/GEV/WBD/classes/Requests/class.gevWBDRequestVvAenderung.php");
class GevVvAenderungTest extends RequestTestBase {

	public function setUp() {
		$data = array("address_type"=>"geschäftlich"
					  ,"title"=>"m"
					  ,"email"=>"shecken@cat06.de"
					  ,"mobile_phone_nr"=>"0162/9800608"
					  ,"info_via_mail"=>false
					  ,"birthday"=>"1981-06-19"
					  ,"country"=>"D"
					  ,"lastname"=>"Hecken"
					  ,"city"=>"Köln"
					  ,"zipcode"=>"50969"
					  ,"phone_nr"=>"0221/46757600"
					  ,"degree"=>"Dr"
					  ,"wbd_agent_status"=>"Makler"
					  ,"okz"=>"OKZ1"
					  ,"firstname"=>"Stefan"
					  ,"user_id"=>3215
					  ,"street"=>"Vorgebirgstr. 338"
					  ,"row_id"=>35214
					);

		$this->request = gevWBDRequestVvAenderung::getInstance($data);
	}

	public function test_isImplmentedRequest() {
		$this->assertInstanceOf("gevWBDRequestVvAenderung",$this->request);
	}

	public function xml_response_success() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
												.'<soap:Body>'
													.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
														.'<AenderungRueckgabewert>'
															.'<VermittlerId>20150728-100390-74</VermittlerId>'
														.'</AenderungRueckgabewert>'
													.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
													.'<AenderungRueckgabewert>'
														.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'</AenderungRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
													.'<AenderungRueckgabewert>'
														.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'</AenderungRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									))
			);
	}

	public function xml_response_success_xml_fails() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
													.'<AenderungRueckgabewert>'
														.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'</AenderungRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
													.'<AenderungRueckgabewert>'
														.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'</AenderungRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
													.'<AenderungRueckgabewert>'
														.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'</AenderungRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									))
			);
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