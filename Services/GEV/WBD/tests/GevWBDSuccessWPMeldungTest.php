<?php
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessWPMeldung.php");
class GevWBDSuccessWPMeldungTest extends SuccessTestBase {

	public function setUp() {
		$this->success = new gevWBDSuccessWPMeldung(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
												.'<soap:Body>'
													.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
														.'<WPMeldungRueckgabewert>'
															.'<WeiterbildungsPunkteBuchungsId>2015-145-1654</WeiterbildungsPunkteBuchungsId>'
															.'<InterneVermittlerId>7665</InterneVermittlerId>'
															.'<VermittlerId>20150728-100390-74</VermittlerId>'
															.'<InterneBuchungsId>21352</InterneBuchungsId>'
															.'<BeginnErstePeriode>2015-07-28T00:00:00+02:00</BeginnErstePeriode>'
														.'</WPMeldungRueckgabewert>'
													.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									),'201-06-19', 6);
	}

	public function success_xml_error() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
												.'<soap:Body>'
													.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
														.'<WPMeldungRueckgabewert>'
															.'<WeiterbildungsPunkteBuchungsId>2015-145-1654</WeiterbildungsPunkteBuchungsId>'
															.'<InterneVdermittlerId>7665</InterneVdermittlerId>'
															.'<VermittlerId>20150728-100390-74</VermittlerId>'
															.'<InterneBuchungsId>21352</InterneBuchungsId>'
															.'<BeginnErstePeriode>2015-07-28T00:00:00+02:00</BeginnErstePeriode>'
														.'</WPMeldungRueckgabewert>'
													.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
						)
				);
	}

	public function test_isWBDSuccessWPMeldung() {
		$this->assertInstanceOf("gevWBDSuccessWPMeldung",$this->success);
	}

	/**
	* @dataProvider success_xml_error
	* @expectedException LogicException
	*/
	public function test_cantCreateSuccessObject($xml) {
		$success = new gevWBDSuccessWPMeldung($xml,'201-06-19', 6);
		$this->assertNotInstanceOf("gevWBDSuccessWPMeldung",$success);
	}

	public function test_agentId() {
		$this->assertInternalType("string", $this->success->agentId());
	}
}