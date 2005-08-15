<?php

// Translation: Sebastiaan J.L. van der Velden (sjlvdv@tiscali.no)
// Revision: $Revision$
// EN-Revision: 1.11

$error[0] = 'Ongeldige URL: <em>%s</em>!';
$error[1] = 'Kon geen verbinding maken met <em>%s</em>!';
$error[2] = 'Geen invoer!';
$error[3] = 'Kon geen karakterset vinden. Gebruik standaard karakterset.';
$error[4] = 'Er is geen goede bron. Gebruik parse_string() of parse_url() eerst!';
$error[5] = 'Karakterset automtisch gevonden (&quot;<em>%s</em>&quot;), maar niet herkend.';
$error[6] = 'De gespecificeerde pagina is te groot (de limiet is %s bytes). Niet alle paginas zullen verwerkt worden!';
$error[7] = 'Er is een time out opgetreden.';
$error[8] = 'Het bestand is te groot. De limiet is %s bytes';
$error[9] = 'Er is een fout opgetreden tijdens het uploaden van het bestand.';
$error[10]= 'Kon de woordenlijst for de taal "%s" niet vinden, de spellingscontrole is niet uitgevoerd.';


/* other messages */
$message[0]  = 'Configureer Opties';
$message[1]  = 'Formaat van fouten';
$message[2]  = 'Hoe de gerepareerde HTML/XML laten zien';
$message[3]  = 'Te controleren Url';
$message[4]  = 'Controleren';
$message[5]  = 'Documetatie';
$message[6]  = 'samen met de bron';
$message[7]  = 'alleen';
$message[8]  = 'Laat fouten zien';
$message[9]  = 'Laat de gerepareerde HTML/XML zien';
$message[10] = 'Vol';
$message[11] = 'Verschil';
$message[12] = 'niets';
$message[13] = 'Fout Ontstaan';
$message[14] = 'Gedetecteerde versie';
$message[15] = 'Geen fouten gevonden!';
$message[16] = 'Fouten';
$message[17] = 'Gerepareerde HTML/XML';
$message[18] = 'Bestand';
$message[19] = 'Bestand uploaden';
$message[20] = 'Codering in gebruik';
$message[21] = 'Fouten gevonden';
$message[22] = 'HTML/XML Controle';
$message[23] = '"%s" is waarschijnlijk fout geschreven. Een mogelijke suggestie is: %s';
$message[24] = 'Taal';
$message[25] = 'nee';
$message[99] = 'Deze HTML, XHTML en XML controle is gebaseerd op <a href="http://tidy.sf.net">Tidy</a> en <a href="http://www.php.net">PHP 5</a>, en maakt het mogelijk om automatisch HTML, XHTML en XML te controleren en repareren. </p><p>NB er wordt nog steeds gewerkt aan dit programma en Tidy. Mocht U een fout vinden of U heeft vragen of suggesties, gebruik dan onze <a href="http://sourceforge.net/tracker/?group_id=143995">tracker</a>.';


/* Name for each option */
$opts['accessibility-check']['name'] = 'Niveau toegangkelijkheids-check';
$opts['alt-text']['name']            = 'Alt-Tekst';
$opts['charset']['name']             = 'Karakterset';
$opts['hide-comments']['name']       = 'Verwijder kommentaar';
$opts['indent']['name']              = 'Inspringen';
$opts['indent-spaces']['name']       = 'Inspring ruimtes';
$opts['output']['name']              = 'Input/Output Formaat';
$opts['spellcheck']['name']          = 'Spellingscontrole';
$opts['wrap']['name']                = 'Samenvouwen';


/* description for each option */
$opts['accessibility-check']['explain'] = 'Selecteer het prioriteitsniveau voor de Toegankelijheidscontrole, of 0 om de Toegankelijkheidscontrole te deactiveren. Meer informatie op: <a href="http://www.w3.org/WAI/">http://www.w3.org/WAI/</a>';
$opts['alt-text']['explain']            = 'Geef de tekst om automatisch de atributen in te vullen die misschien niet opgegeven zijn bij sommige afbeeldingen. U moet deze tekst later wijzigen met de echte beschrijving van de afbeelding!';
$opts['charset']['explain']             = 'Geef de karakterset die gebruikt moet worden om het document te verwerken, of laat deze op auto staan voor automatische detectie.';
$opts['hide-comments']['explain']       = 'Vink deze optie aan als U kommentaar in het document wilt verwijderen.';
$opts['indent']['explain']              = 'Vink deze optie aan als U de tekst automatisch wilt laten inspringen.';
$opts['indent-spaces']['explain']       = 'Inspring ruimtes. Werkt alleen wanneer de Inspring optie is geselecteerd.';
$opts['output']['explain']              = 'Selecteer het input/output formaat. Selecteer &quot;XHTML&quot; als u automatisch een HTML document naar XHTML wilt converteren.';
$opts['spellcheck']['explain']          = "Selecteer een taal om een spellingscontrole uit te voeren, of selecteer 'nee' om dit uit te schakelen. NB meerdere fouten in eenzelfde regel worden niet gemeld.";
$opts['wrap']['explain']                = 'Vouw regels samen op de aangegeven regellengte, of geef 0 op in samenvouwen uit te schakelen.';


/* languages names */
$langs['en_CA'] = 'Engels (Canada)';
$langs['en_GB'] = 'Engels (Groot Brittanië)';
$langs['en_US'] = 'Engels (America)';
$langs['it']    = 'Italiaans';
$langs['nb']    = 'Noors (Bokmal)';
$langs['nl']    = 'Nederlands';
$langs['pl']    = 'Pools';
$langs['pt']    = 'Portugees';
$langs['sv']    = 'Zweeds';
$langs['tr']    = 'Turks';
?>
