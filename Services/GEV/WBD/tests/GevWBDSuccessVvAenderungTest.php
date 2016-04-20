<?php
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessVvAenderung.php");
class GevWBDSuccessVvAenderungTest extends SuccessTestBase {

	public function setUp() {
		$this->row_id = 25;
		$this->success = new gevWBDSuccessVvAenderung(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
												.'<soap:Body>'
													.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
														.'<ErstanlageRueckgabewert>'
															.'<VermittlerId>20150728-100390-74</VermittlerId>'
														.'</ErstanlageRueckgabewert>'
													.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									),$this->row_id);
	}

	public function success_xml_error() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
												.'<soap:Body>'
													.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
														.'<ErstanlageRueckgabewert>'
															.'<VermittlersId>20150728-100390-74</VermittlersId>'
														.'</ErstanlageRueckgabewert>'
													.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
						)
				);
	}

	public function test_isWBDSuccessVvAenderung() {
		$this->assertInstanceOf("gevWBDSuccessVvAenderung",$this->success);
	}

	/**
	* @dataProvider success_xml_error
	* @expectedException LogicException
	*/
	public function test_cantCreateSuccessObject($xml) {
		$success = new gevWBDSuccessVvAenderung($xml, $this->row_id);
		$this->assertNotInstanceOf("gevWBDSuccessVvAenderung",$success);
	}

	public function test_agentId() {
		$this->assertInternalType("string", $this->success->agentId());
	}
}