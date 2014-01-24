<?php

// Translation: AlleyKat (translations@phpbb2.dk) (http://phpbb2.dk)
// Revision: $Revision$
// EN-Revision: 1.13

$error[0] = 'Ugyldig URL: <em>%s</em>!';
$error[1] = 'Kunne ikke forbinde til <em>%s</em>!';
$error[2] = 'Intet input!';
$error[3] = 'Kunne ikke finde tegnsæt automatisk. Bruger standard.';
$error[4] = 'Der er ingen tidy ressource. Kald parse_string() eller parse_url() først!';
$error[5] = 'Jeg har automatisk sporet et tegnsæt (&quot;<em>%s</em>&quot;), men kan ikke genkende det.';
$error[6] = 'Siden du angav er for stor (grænsen er %s bytes). Ikke alle sider vil blive parset!';
$error[7] = 'Forbindelsen timede ud.';
$error[8] = 'Filen er for stor. Grænsen er %s bytes';
$error[9] = 'Filen blev ikke uploadet korrekt.';
$error[10]= 'Kunne ikke starte aspell biblioteket for "%s" sprog, så der blev ikke stavekontrolleret.';


/* other messages */
$message[0]  = 'Konfigurer indstillinger';
$message[1]  = 'Format for fejl';
$message[2]  = 'Hvordan den reparerede HTML/XML vises';
$message[3]  = 'Url der skal valideres';
$message[4]  = 'Valider';
$message[5]  = 'Dokumentation';
$message[6]  = 'sammen med kildekoden';
$message[7]  = 'alene';
$message[8]  = 'Vis fejl';
$message[9]  = 'Vis repareret HTML/XML';
$message[10] = 'Fuld';
$message[11] = 'Diff';
$message[12] = 'ingen';
$message[13] = 'Der skete en fejl';
$message[14] = 'Sporet version';
$message[15] = 'Der var ingen fejl!';
$message[16] = 'Fejl';
$message[17] = 'Repareret HTML/XML';
$message[18] = 'Fil';
$message[19] = 'Filupload';
$message[20] = 'Brugt encoding';
$message[21] = 'Fundne fejl';
$message[22] = 'HTML/XML Validator';
$message[23] = 'Ordet "%s" er muligvis stavet forkert. Mulige forslag inkluderer: %s';
$message[24] = 'Sprog';
$message[25] = 'nej';
$message[99] = 'Denne HTML, XHTML og XML validator er baseret på <a href="http://tidy.sf.net">Tidy</a> og <a href="http://www.php.net">PHP 5</a>, og lader dig validere og automatisk reparere HTML, XHTML og XML. Det lader dig også konvertere HTML dokumenter til XHTML med et tryk på en knap.</p><p>Bemærk venligst at både dette program og Tidy i øjeblikket er under hastig udvikling. Hvis du finder en fejl eller har spørgsmål/forslag, brug vores <a href="http://sourceforge.net/tracker/?group_id=143995">tracker</a>.';


/* Name for each option */
$opts['accessibility-check']['name'] = 'Accessibility check niveau';
$opts['alt-text']['name']            = 'Alt-Text';
$opts['charset']['name']             = 'Tegnsætt';
$opts['hide-comments']['name']       = 'Strip kommentarer';
$opts['indent']['name']              = 'Indryk';
$opts['indent-spaces']['name']       = 'Indryk mellemrum';
$opts['output']['name']              = 'Input/Output Format';
$opts['spellcheck']['name']          = 'Stavekontrol';
$opts['wrap']['name']                = 'Ombryd';


/* description for each option */
$opts['accessibility-check']['explain'] = 'Vælg prioritetsniveau for Accessibility validator, eller 0 for at slå Accessibility checks fra. Mere info her: <a href="http://www.w3.org/WAI/">http://www.w3.org/WAI/</a>';
$opts['alt-text']['explain']            = 'Indtast tekst der skal bruges til automatisk at udfylde alt attributtekst på billeder hvor det mangler. Du bør ændre denne tekst efterfølgende og indsætte en rigtig beskrivelse af billedet!';
$opts['charset']['explain']             = 'Indtast tegnsæt der skal bruges til at parse dokumentet, eller lad den stå på auto hvis du vil lade den prøve at afgøre det automatisk.';
$opts['hide-comments']['explain']       = 'Sæt mærke i denne indstilling hvis du vil have fjernet alle kommentarer i dokumentet.';
$opts['indent']['explain']              = 'Sæt mærke i denne hvis du vil have automatisk indrykning af din tekst.';
$opts['indent-spaces']['explain']       = 'Indryk mellemrum. Automatisk indrykning skal være slået til.';
$opts['output']['explain']              = 'Vælg input/output format. You may select the option &quot;XHTML&quot; if you want to convert a HTML document to XHTML automatically.';
$opts['spellcheck']['explain']          = "Vælg et sprog for at køre stavekontrol på dit dokument, eller vælg 'nej' for at slå det fra. Bemærk venligst at gentagne fejl på samme linie ikke behandles/rapporteres.";
$opts['wrap']['explain']                = 'Ombryd linier ved den angivne linielængde, eller indtast 0 for at slå ombrydning fra.';


/* languages names */
$langs['en_CA'] = 'Engelsk (Kanadisk)';
$langs['en_GB'] = 'Engelsk (Britisk)';
$langs['en_US'] = 'Engelsk (Amerikansk)';
$langs['ca']    = 'Katalansk';
$langs['da']    = 'Dansk';
$langs['es']    = 'Spansk';
$langs['eu']    = 'Baskisk';
$langs['it']    = 'Italiensk';
$langs['nb']    = 'Norsk (Bokmål)';
$langs['nl']    = 'Hollandsk';
$langs['pl']    = 'Polsk';
$langs['pt']    = 'Portugisisk';
$langs['sv']    = 'Svensk';
$langs['tr']    = 'Tyrkisk';
?>
