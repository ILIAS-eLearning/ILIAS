<?php
class GevVvAenderungTest extends RequestTestBase {
	
	public function setUp() {
		$this->request = new gevWBDRequestVvAenderung();
	}

	public function test_isImplmentedRequest() {
		$this->assertInstanceOf("gevWBDRequestVvAenderung",$this->request);
	}

	public function xml_response_success() {
		return array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
													.'<ErstanlageRueckgabewert>'
														.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'</ErstanlageRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
					,simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
													.'<ErstanlageRueckgabewert>'
														.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'</ErstanlageRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
					,simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
													.'<ErstanlageRueckgabewert>'
														.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'</ErstanlageRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
			);
	}

	public function xml_response_success_xml_fails() {
		return array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
													.'<ErstanlageRueckgabewert>'
														.'<VermittlefrId>20150728-100390-74</VermittlefrId>'
													.'</ErstanlageRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
					,simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
													.'<ErstanlageRueckgabewert>'
														.'<VermsittlerId>20150728-100390-74</VermsittlerId>'
													.'</ErstanlageRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
					,simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
													.'<ErstanlageRueckgabewert>'
														.'<VermilerId>20150728-100390-74</VermilerId>'
													.'</ErstanlageRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
			);
	}

	public function xml_response_error() {
		return array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"">'
												.'<soap:Body>'
													.'<soap:Fault>'
														.'<faultcode>soap:Server</faultcode>'
														.'<faultstring>Der Vermittler ist deaktiviert.</faultstring>'
														.'<detail>'
															.'<ns1:VermittlerNichtAktivException xmlns:ns1="http://meldung.wp.external.service.wbd.gdv.de/"/>'
														.'</detail>'
													.'</soap:Fault>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
					,simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"">'
												.'<soap:Body>'
													.'<soap:Fault>'
														.'<faultcode>soap:Server</faultcode>'
														.'<faultstring>Der Vermittler ist deaktiviert.</faultstring>'
														.'<detail>'
															.'<ns1:VermittlerNichtAktivException xmlns:ns1="http://meldung.wp.external.service.wbd.gdv.de/"/>'
														.'</detail>'
													.'</soap:Fault>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
					,simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"">'
												.'<soap:Body>'
													.'<soap:Fault>'
														.'<faultcode>soap:Server</faultcode>'
														.'<faultstring>Der Vermittler ist deaktiviert.</faultstring>'
														.'<detail>'
															.'<ns1:VermittlerNichtAktivException xmlns:ns1="http://meldung.wp.external.service.wbd.gdv.de/"/>'
														.'</detail>'
													.'</soap:Fault>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
			);
	}
}