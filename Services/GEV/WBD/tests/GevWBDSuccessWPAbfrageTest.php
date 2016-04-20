<?php
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessWPAbfrage.php");
class GevWBDSuccessWPAbfrageTest extends SuccessTestBase {

	public function setUp() {
		$this->success = new gevWBDSuccessWPAbfrage(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
															.'<soap:Body>'
																.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
																	.'<WPAbfrageRueckgabewert>'
																		.'<VermittlerId>20150728-100390-74</VermittlerId>'
																		.'<InterneVermittlerId>21352</InterneVermittlerId>'
																		.'<ZertifizierungsPeriode>003</ZertifizierungsPeriode>'
																		.'<BeginnErstePeriode>2015-07-28T00:00:00+02:00</BeginnErstePeriode>'
																		.'<WeiterbildungsPunkteBuchungListe>'
																			.'<Id>12</Id>'
																			.'<WeiterbildungsPunkteBuchungsId>2015-07-28</WeiterbildungsPunkteBuchungsId>'
																			.'<BuchendeOrganisationsId>123456785</BuchendeOrganisationsId>'
																			.'<AnlegerNutzerId>321654</AnlegerNutzerId>'
																			.'<Weiterbildung>Aus doof mach Schlau</Weiterbildung>'
																			.'<WeiterbildungsPunkte>4</WeiterbildungsPunkte>'
																			.'<BuchungsDatum>2015-07-28T00:00:00+02:00</BuchungsDatum>'
																			.'<SeminarDatumVon>2015-07-28T00:00:00+02:00</SeminarDatumVon>'
																			.'<SeminarDatumBis>2015-07-28T00:00:00+02:00</SeminarDatumBis>'
																			.'<LernArt>004</LernArt>'
																			.'<LernInhalt>005</LernInhalt>'
																			.'<InterneBuchungsId>21501</InterneBuchungsId>'
																			.'<Storniert>false</Storniert>'
																			.'<StornoOrganisationsId></StornoOrganisationsId>'
																			.'<StornoNutzerId></StornoNutzerId>'
																			.'<StornoDatum></StornoDatum>'
																			.'<Korrekturbuchung>false</Korrekturbuchung>'
																			.'<VermittlerId>20150728-100390-74</VermittlerId>'
																			.'<BasiertAufBuchungsId>2015-07-28</BasiertAufBuchungsId>'
																			.'<Hinweis>Nix zu weisen</Hinweis>'
																			.'<AnsprechpartnerDatenId>225</AnsprechpartnerDatenId>'
																		.'</WeiterbildungsPunkteBuchungListe>'
																		.'<OKZ>OKZ1</OKZ>'
																		.'<GesamtepunktePeriode>20</GesamtepunktePeriode>'
																	.'</WPAbfrageRueckgabewert>'
																.'</ns1:putResponse>'
															.'</soap:Body>'
														.'</soap:Envelope>'
												),10);
	}

	public function success_xml_error() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
												.'<soap:Body>'
													.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
														.'<WPAbfrageRueckgabewert>'
															.'<VermittlerId>20150728-100390-74</VermittlerId>'
															.'<InterneVermittlerId>21352</InterneVermittlerId>'
															.'<ZertifizierungsPeriode>003</ZertifizierungsPeriode>'
															.'<BeginnErstePeriode>2015-07-28T00:00:00+02:00</BeginnErstePeriode>'
															.'<WeiterbildungsPunkteBuchungListe>'
																.'<Id>12</Id>'
																.'<WeiterbildungsPunkteBuchungsId>2015-07-28</WeiterbildungsPunkteBuchungsId>'
																.'<BuchendeOrganisationsId>123456785</BuchendeOrganisationsId>'
																.'<AnlegerNutzerId>321654</AnlegerNutzerId>'
																.'<Weiterbildung>Aus doof mach Schlau</Weiterbildung>'
																.'<WeiterbildungsPunkte>4</WeiterbildungsPunkte>'
																.'<BuchungsDatum>2015-07-28T00:00:00+02:00</BuchungsDatum>'
																.'<SeminarsDatumVon>2015-07-28T00:00:00+02:00</SeminarsDatumVon>'
																.'<SeminarDatumBis>2015-07-28T00:00:00+02:00</SeminarDatumBis>'
																.'<LernArt>004</LernArt>'
																.'<LernInhalt>005</LernInhalt>'
																.'<InterneBuchungsId>21501</InterneBuchungsId>'
																.'<Stornierdt>false</Stornierdt>'
																.'<StornoOrganisationsId></StornoOrganisationsId>'
																.'<StornoNutzerId></StornoNutzerId>'
																.'<StornoDatum></StornoDatum>'
																.'<Korrekturbuchung>false</Korrekturbuchung>'
																.'<VermittlerId>20150728-100390-74</VermittlerId>'
																.'<BasiertAufBuchungsId>2015-07-28</BasiertAufBuchungsId>'
																.'<Hinweis>Nix zu weisen</Hinweis>'
																.'<AnsprechpartnerDatenId>225</AnsprechpartnerDatenId>'
															.'</WeiterbildungsPunkteBuchungListe>'
															.'<OKZ>OKZ1</OKZ>'
															.'<GesamtepunktePeriode>20</GesamtepunktePeriode>'
														.'</WPAbfrageRueckgabewert>'
													.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									)
						)
				);
	}

	public function test_isWPAbfrage() {
		$this->assertInstanceOf("gevWBDSuccessWPAbfrage",$this->success);
	}

	/**
	* @dataProvider success_xml_error
	* @expectedException LogicException
	*/
	public function test_cantCreateSuccessObject($xml) {
		$success = new gevWBDSuccessWPAbfrage($xml,10);
		$this->assertNotInstanceOf("gevWBDSuccessWPAbfrage",$success);
	}

	public function test_agentId() {
		$this->assertInternalType("string", $this->success->agentId());
	}
}