<?php

// Translation: Aitor Gomez & Íñigo Goiri (twolf@telefonica.net)
// Revision: $Revision$
// EN-Revision: 1.5

$error[0] = 'Ez baliodun URLa: <em>%s</em>!';
$error[1] = 'Ezin da <em>%s</em>rekin konektatu!';
$error[2] = 'Ez dago datu-sarrerarik!';
$error[3] = 'Ezin izan da karaktere-jokoa automatikoki detektatu. Lehenetsia erabiliko da.';
$error[4] = 'Ez dago tidy baliabiderik. parse_string() edo parse_url()-i deitu behar dituzu aurretik!';
$error[5] = 'Karaktere-joko bat detektatu egin da automatikoki (&quot;<em>%s</em>&quot;), baina ezin da ezagutu reconoce.';
$error[6] = 'Orria handiegia da (muga %s bytetan dago). Ezin izan da orri osoa analizatu!';
$error[7] = 'Konexio denbora agortu egin da.';
$error[8] = 'Fitxategia handiegia da. Muga %s bytetan dago';
$error[9] = 'Fitxategiaren karga ez da ongi burutu.';
$error[10]= 'Ezin izan da aspell liburutegia "%s" hizkuntzarako hasi, ez da ortografia egiaztatu.';


/* other messages */
$message[0]  = 'Konfiguratu aukerak';
$message[1]  = 'Errore-formatua';
$message[2]  = 'Erakutsi konpondutako HTML/XML honela';
$message[3]  = 'Balidatu url hau';
$message[4]  = 'Balidatu';
$message[5]  = 'Dokumentazioa';
$message[6]  = 'iturrian zehar';
$message[7]  = 'bakarra';
$message[8]  = 'Erakutsi erroreak';
$message[9]  = 'Erakutsi konpondutako HTML/XML';
$message[10] = 'Osoa';
$message[11] = 'Diferentzia';
$message[12] = 'Bat ere ez';
$message[13] = 'Errore bat gertatu da';
$message[14] = 'Bertsio detektatua';
$message[15] = 'Ez da errorerik aurkitu!';
$message[16] = 'Erroreak';
$message[17] = 'HTML/XML konpondua';
$message[18] = 'Fitxategia';
$message[19] = 'Fitxategi kargatua';
$message[20] = 'Kodeketa erabiltzen ari da';
$message[21] = 'errore aurkitu dira';
$message[22] = 'HTML/XML balidatzailea';
$message[23] = 'Beharbada "%s" hitza txarto idatzi da. Gomendioak: %s';
$message[24] = 'Hizkuntza';
$message[25] = 'ez';
$message[99] = 'HTML, XHTML eta XML balidatzaile hau <a href="http://tidy.sf.net">Tidy</a>n eta <a href="http://www.php.net">PHP 5</a>n oinarrituta dago, eta HTMLa, XHTMLa eta XMLa automatikoki balidatzeko eta konpontzeko balio du. Gainera, klik bakar batekin HTML dokumentuak XHTML dokumentuetan bihurtzen ditu.</p><p>Mesedez, bi programek garapen fasean daudela kontutan hartu. Erroreren bat aurkitzen baldin baduzu edo galdera/gomendioren bat bidaltzeko gure <a href="http://sourceforge.net/tracker/?group_id=143995">trackerra</a> erabili.';


/* Name for each option */
$opts['accessibility-check']['name'] = 'Erabilerraztasun maila';
$opts['alt-text']['name']            = 'Alt-testua';
$opts['charset']['name']             = 'Karaktere-jokoa';
$opts['hide-comments']['name']       = 'Ezabatu iruzkinak';
$opts['indent']['name']              = 'Lerrokapena';
$opts['indent-spaces']['name']       = 'Lerrokapenerako hutsuneak';
$opts['output']['name']              = 'Sarrera/irteera formatua';
$opts['spellcheck']['name']          = 'Ortografia egiaztapena';
$opts['wrap']['name']                = 'Lerro tamaina';


/* description for each option */
$opts['accessibility-check']['explain'] = 'Hautatu erabilerraztasun balidatzailearentzako prioritate maila edo 0 aukera desgaitzeko. Informazio gehiagorako: <a href="http://www.w3.org/WAI/">http://www.w3.org/WAI/</a>';
$opts['alt-text']['explain']            = 'Sartu alt atributua ez duten irudiak automatikoki betetzeko testua. Geroago testu hori irudiaren benetako deskripzio batekin aldatu beharko zenuke!';
$opts['charset']['explain']             = 'Sartu dokumentoa analizatzeko erabiliko den karaktere-jokoa edo auto utzi automatikoki detektatzen saiatzeko.';
$opts['hide-comments']['explain']       = 'Hautatu aukera hau dokumentuaren iruzkinak ezabatzeko.';
$opts['indent']['explain']              = 'Hautatu aukera hau testua automatikoki lerrokatzeko.';
$opts['indent-spaces']['explain']       = 'Hutsune lerrokatuak. Lerrokapen aukera gaitu behar duzu.';
$opts['output']['explain']              = 'Hautatu sarrera/irteera formatua. Beharbada &quot;XHTML&quot; aukera hautatu nahiko duzu automatikoki HTML dokumentua XHTML dokumentuan bihurtzeko.';
$opts['spellcheck']['explain']          = "Hautatu hizkuntzaren bat ortografia zuzentzeko edo 'ez' hautatu zuzenketa desgaitzeko. Ohartu beharra dago lerro berean aurkitzen diren erroreak ez direla txostenean birritan sartuko.";
$opts['wrap']['explain']                = 'Mugatu lerroaren tamaina x karakteretara. Sartu 0 desgaitzeko.';


/* languages names */
$langs['en_CA'] = 'Ingelera (Kanadiarra)';
$langs['en_GB'] = 'Ingelera (Britainiarra)';
$langs['en_US'] = 'Ingelera (Americarra)';
$langs['ca']    = 'Katalaniera';
$langs['es']    = 'Gaztelania';
$langs['eu']    = 'Euskara';
$langs['pt']    = 'Portugalera';
$langs['tr']    = 'Turkiera';
?>
