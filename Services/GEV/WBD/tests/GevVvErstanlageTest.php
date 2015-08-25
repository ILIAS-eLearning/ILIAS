<?php
class GevVvErstanlageTest extends RequestTestBase {
	
	public function setUp() {
		$this->request = new gevWBDRequestVvErstanlage();
	}

	public function test_isImplmentedRequest() {
		$this->assertInstanceOf("gevWBDRequestVvErstanlage",$this->request);
	}

	public function xml_response_success() {
		return array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
												.'<ErstanlageRueckgabewert>'
													.'<TpInterneVermittlerId>7665</TpInterneVermittlerId>'
													.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'<AnlageDatum>2015-07-28T00:00:00+02:00</AnlageDatum>'
													.'<BeginnZertifizierungsPeriode>2015-07-28T00:00:00+02:00</BeginnZertifizierungsPeriode>'
												.'</ErstanlageRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
					,simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
												.'<ErstanlageRueckgabewert>'
													.'<TpInterneVermittlerId>7665</TpInterneVermittlerId>'
													.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'<AnlageDatum>2015-07-28T00:00:00+02:00</AnlageDatum>'
													.'<BeginnZertifizierungsPeriode>2015-07-28T00:00:00+02:00</BeginnZertifizierungsPeriode>'
												.'</ErstanlageRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
					,simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
												.'<ErstanlageRueckgabewert>'
													.'<TpInterneVermittlerId>7665</TpInterneVermittlerId>'
													.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'<AnlageDatum>2015-07-28T00:00:00+02:00</AnlageDatum>'
													.'<BeginnZertifizierungsPeriode>2015-07-28T00:00:00+02:00</BeginnZertifizierungsPeriode>'
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
													.'<TpIntersneVermittlerId>7665</TpIntersneVermittlerId>'
													.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'<AnlageDatum>2015-07-28T00:00:00+02:00</AnlageDatum>'
													.'<BeginnZertifizierungsPeriode>2015-07-28T00:00:00+02:00</BeginnZertifizierungsPeriode>'
												.'</ErstanlageRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
					,simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
												.'<ErstanlageRueckgabewert>'
													.'<TpInterneVermittlerId>7665</TpInterneVermittlerId>'
													.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'<AnlagedDatum>2015-07-28T00:00:00+02:00</AnlagedDatum>'
													.'<ZertifizierungsPeriode>2015-07-28T00:00:00+02:00</ZertifizierungsPeriode>'
												.'</ErstanlageRueckgabewert>'
												.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
					,simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
												.'<soap:Body>'
												.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
												.'<ErstanlageRueckgabewert>'
													.'<TpInterneVermittlerId>7665</TpInterneVermittlerId>'
													.'<VermittlerId>20150728-100390-74</VermittlerId>'
													.'<AnlagedsaDatum>2015-07-28T00:00:00+02:00</AnlagedsaDatum>'
													.'<BeginnZertifizierungsPeriode>2015-07-28T00:00:00+02:00</BeginnZertifizierungsPeriode>'
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