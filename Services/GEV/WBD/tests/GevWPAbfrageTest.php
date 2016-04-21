<?php
require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestWPAbfrage.php");
class GevWPAbfrageTest extends RequestTestBase {
	
	protected $backupGlobals = FALSE;

	public function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		$data = array("certification_period"=>"Selektiert alle Weiterbildungsmaßnahmen."
					  ,"bwv_id"=>"2015-12--5-124"
					  ,"user_id" => "45641"
					  ,"row_id" => "442"
					);

		$this->user_id = 10;

		$this->request = gevWBDRequestWPAbfrage::getInstance($data);
	}

	public function test_isImplmentedRequest() {
		$this->assertInstanceOf("gevWBDRequestWPAbfrage",$this->request);
	}

	public function xml_response_success() {
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
				))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
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
				))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
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
				))
			);
	}

	public function xml_response_success_xml_fails() {
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
											.'<VermittlerId2>20150728-100390-74</VermittlerId2>'
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
				))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
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
											.'<VermittlerId2>20150728-100390-74</VermittlerId2>'
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
				))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
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
											.'<VermittlerId2>20150728-100390-74</VermittlerId2>'
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

	//Bool = False needed
	/**
     * @dataProvider xml_response_success_xml_fails
     * @expectedException LogicException
     */
	public function test_parseResponseXMLErrorInXML($xml) {
		$this->request->createWBDSuccess($xml,$this->user_id);
	}

	//Array needed
	/**
     * @dataProvider xml_response_success
     */
	public function test_returnWBDSuccessObject($xml) {
		$this->request->createWBDSuccess($xml,$this->user_id);
		$this->assertInstanceOf("WBDSuccess",$this->request->getWBDSuccess());
	}

	/**
	 * @dataProvider xml_response_success
	 * @expectedException LogicException
	 */
	public function test_returnWBDErrorObjectOnSuccess($xml) {
		$this->request->createWBDSuccess($xml, $this->user_id);
		$this->assertInstanceOf("WBDError",$this->request->getWBDError());
	}
}