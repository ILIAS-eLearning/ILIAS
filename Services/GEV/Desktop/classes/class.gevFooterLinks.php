<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* GUI to show offers to agents of the generali.
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*
*/

class gevFooterLinks {
	protected static function footerLinks($user_id) {
		$uutils = null;
		if($user_id) {
			require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
			$uutils = gevUserUtils::getInstance($user_id);
		}

		return array(
		/*BiProgramm Agent*/array
			( "link" => "./Customizing/global/skin/genv/static/documents/Weiterbildungsprogramme_2015_Vermittler.pdf"
			, "desc" => "gev_footer_bipro_agent"
			, "display" => function() use ($uutils) {
					if($uutils) {
						if($uutils->seeBiproAgent()) {
							$today = date("Y-m-d");
							if($today < "2016-01-01") {
								return true;
							} else {
								return false;
							}
						}
					return false;
					}
				}
			),
		/*BiProgramm Superior*/array
			( "link" => "./Customizing/global/skin/genv/static/documents/Weiterbildungsprogramme_2015_Fuehrungskraefte.pdf"
			, "desc" => "gev_footer_bipro_superior"
			, "display" => function() use ($uutils) {
					if($uutils) {
						if($uutils->seeBiproSuperior()) {
							$today = date("Y-m-d");
							if($today < "2016-01-01") {
								return true;
							} else {
								return false;
							}
						}
						return false;
					}
				}
			),
		/*Infos Decentral*/array
			( "link" => "./static_pages.php?tpl=infpraesenz"
			, "desc" => "gev_footer_infos_decentral"
			, "display" => ($uutils instanceof gevUserUtils)
			),
		/*Infos Webinar*/array
			( "link" => "./static_pages.php?tpl=infwebinar"
			, "desc" => "gev_footer_infos_webinar"
			, "display" => ($uutils instanceof gevUserUtils)
			),
		/*AGB Agent*/array
			( "link" => "./Customizing/global/skin/genv/static/documents/GEV_Makler_Finale_Nutzungsbedingungen.pdf"
			, "desc" => "gev_footer_terms_of_use"
			, "display" => function() use ($uutils) {
					if($uutils) {
						if ($uutils->hasRoleIn(array("VP"))) {
							return true;
						}
						return false;
					}
				}
			),
		 /*AGB*/array
		 	( "link" => "./static_pages.php?tpl=agb"
			 ,"desc" => "gev_footer_terms_of_use"
			 ,"display" => function() use ($uutils) {
					if($uutils) {
						if (!$uutils->hasRoleIn(array("VP"))) {
							return true;
						}
						return false;
					}
				}
			),
		/*Infos WBD registration*/array
			( "link" => "./static_pages.php?tpl=wbd_docs"
			, "desc" => "gev_footer_infos_wbd_registration"
			, "display" => ($uutils instanceof gevUserUtils)
			),
		/*Contact*/array
			( "link" => "./static_pages.php?tpl=kontakt"
			, "desc" => "gev_footer_contact"
			, "display" => true
			),
		/*Imprint*/array
			( "link" => "./static_pages.php?tpl=impressum"
			, "desc" => "gev_footer_imprint"
			, "display" => true
			),
		/*Academy Benried*/array
			( "link" => "./static_pages.php?tpl=gszbernried"
			, "desc" => "gev_footer_academy_benried"
			, "display" => ($uutils instanceof gevUserUtils)
			)
		);
	}

	public function getFooterLinksFor($user_id = null) {
		$links = self::footerLinks($user_id);
		$ret = array();

		foreach ($links as $key => $link) {
			if(is_callable($link["display"])){
				if($link["display"]()) {
					$ret[] = array("link" => $link["link"]
							   ,"desc" => $link["desc"]
							  );
				}
			} else {
				if($link["display"]) {
					$ret[] = array("link" => $link["link"]
							   ,"desc" => $link["desc"]
							  );
				}
			}
		}

		return $ret;
	}
}