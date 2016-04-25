<?php
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessWPStorno.php");
class GevWBDSuccessWPStornoTest extends SuccessTestBase {

	public function setUp() {
		$this->row_id = 25;
		$this->success = new gevWBDSuccessWPStorno(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
												.'<soap:Body>'
													.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
														.'<WPStornoRueckgabewert>'
															.'<WeiterbildungsPunkteBuchungsId>2015-145-1654</WeiterbildungsPunkteBuchungsId>'
															.'<VermittlerId>20150728-100390-74</VermittlerId>'
															.'<InterneVermittlerId>21352</InterneVermittlerId>'
															.'<BeginnErstePeriode>2015-07-28T00:00:00+02:00</BeginnErstePeriode>'
														.'</WPStornoRueckgabewert>'
													.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									),$this->row_id);
	}

	public function success_xml_error() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
								.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
									.'<WPStornoRueckgabewert>'
										.'<WeiterbildungsPunkteBuchungsId>2015-145-1654</WeiterbildungsPunkteBuchungsId>'
										.'<VermitttlerId>20150728-100390-74</VermitttlerId>'
										.'<InterneVermittlerId>21352</InterneVermittlerId>'
										.'<BeginnErstePeriode>2015-07-28T00:00:00+02:00</BeginnErstePeriode>'
									.'</WPStornoRueckgabewert>'
								.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
						)
					)
				);
	}

	public function test_isWBDSuccessVvAenderung() {
		$this->assertInstanceOf("gevWBDSuccessWPStorno",$this->success);
	}

	/**
	* @dataProvider success_xml_error
	* @expectedException LogicException
	*/
	public function test_cantCreateSuccessObject($xml) {
		$success = new gevWBDSuccessWPStorno($xml,$this->row_id);
		$this->assertNotInstanceOf("gevWBDSuccessWPStorno",$success);
	}

	public function test_agentId() {
		$this->assertInternalType("string", $this->success->agentId());
	}
}