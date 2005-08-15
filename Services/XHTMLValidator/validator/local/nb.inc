<?php

// Translation: Sebastiaan J.L. van der Velden (sjlvdv@tiscali.no)
// Revision: $Revision$
// EN-Revision: 1.8

$error[0] = 'Ugyldig URL: <em>%s</em>!';
$error[1] = 'Kunne ikke opprette forbindelse med <em>%s</em>!';
$error[2] = 'Ingen input!';
$error[3] = 'Klarte ikke å finne tegnsett automatisk. Bruker standard.';
$error[4] = 'Ingen ryddig ressurs. Bruk parse_string() eller parse_url() først!';
$error[5] = 'Funnet et tegnsett automatisk (&quot;<em>%s</em>&quot;), men klarte ikke å gjenkjenne det.';
$error[6] = 'Angit side er for stor (grensen er %s bytes). Ikke alle sider blir analysert!';
$error[7] = 'Forbindelsen har timed out.';
$error[8] = 'Filen er for stor. grensen er %s bytes';
$error[9] = 'Upload av filen var ikke fullstendig.';
$error[10]= 'Klarte ikke å finne ordboken for angitt språk "%s", derfor ble ingen stavekontroll gjort.';


/* other messages */
$message[0]  = 'Konfigurer Instillinger';
$message[1]  = 'Feilformater';
$message[2]  = 'Hvordan vise reparerte HTML/XML';
$message[3]  = 'Url å godkjenne';
$message[4]  = 'Godkjenne';
$message[5]  = 'Dokumentasjon';
$message[6]  = 'sammen med ressursen';
$message[7]  = 'aleine';
$message[8]  = 'Vis feil';
$message[9]  = 'Vis reparert HTML/XML';
$message[10] = 'Full';
$message[11] = 'Forskjell';
$message[12] = 'Ingen';
$message[13] = 'Oppstod en feil';
$message[14] = 'Oppdaget versjon';
$message[15] = 'Ingen feil ble funnet!';
$message[16] = 'Flere Feil';
$message[17] = 'Reparert HTML/XML';
$message[18] = 'Fil';
$message[19] = 'Fil upload';
$message[20] = 'Kodering i bruk';
$message[21] = 'Fant Feil';
$message[22] = 'HTML/XML Godkjenning';
$message[23] = 'Ordet "%s" er kanskje feil stavet. Mulige forslag er: %s';
$message[24] = 'Språk';
$message[25] = 'nei';
$message[99] = 'Denne HTML XHTML og XML godkjenningsprogramvære er basert på <a href="http://tidy.sf.net">Tidy</a> og <a href="http://www.php.net">PHP 5</a>, og gjør det mulig å godkjenne og reparere HTML, XHTML og XML automatisk. Det gjør det mulig å konvertere HTML dokumenter til XHTML med en enkelt knapp.</p><p>Vær oppmersom at denne programvære og Tidy er under utvikling. Hvis du finner en feil eller har spørsmål/forslag, bruk vår <a href="http://sourceforge.net/tracker/?group_id=143995">tracker</a>.';


/* Name for each option */
$opts['accessibility-check']['name'] = 'Sjekk tilgjengelighetsnivå';
$opts['alt-text']['name']            = 'Alt-Tekst';
$opts['charset']['name']             = 'Tegnsett';
$opts['hide-comments']['name']       = 'Fjern kommentar';
$opts['indent']['name']              = 'Indent';
$opts['indent-spaces']['name']       = 'Indent rom';
$opts['output']['name']              = 'Input/Output Format';
$opts['spellcheck']['name']          = 'Stavekontroll';
$opts['wrap']['name']                = 'Tekstbryting';


/* description for each option */
$opts['accessibility-check']['explain'] = 'Velg prioritetsnivå for Tilgjengelighetsvalidering, eller velg 0 for å deaktivere Tilgjengelighetssjekking. Mer informasjon på: <a href="http://www.w3.org/WAI/">http://www.w3.org/WAI/</a>';
$opts['alt-text']['explain']            = 'Gir teksten for automatisk skriving av attributt tekst som kanskje mangler ved noe bilder. Du må endre denne teksten senere med en riktig beskrivelse av bildet';
$opts['charset']['explain']             = 'Gir tegnsettet som skal brukes ved å analysere dokumentet, eller la den stå på auto for automatisk oppsporing.';
$opts['hide-comments']['explain']       = 'Velg dette når du vil fjerne kommentar i dokumentet.';
$opts['indent']['explain']              = 'Velg dette når du vil bruke automatisk indent.';
$opts['indent-spaces']['explain']       = 'Indent rom. Du må also aktivire Indent mulighet.';
$opts['output']['explain']              = 'Velg input/output format. Du kan velge &quot;XHTML&quot; når du vil konvertere fra HTML til XHTML automatisk.';
$opts['spellcheck']['explain']          = "Velg et språk for stavekontroll, eller velg 'nei' for å deaktivere dette. OBS Flere feil på samme linje blir ikke rapportert.";
$opts['wrap']['explain']                = 'Bryt tekst på det spesifiserte linjelengde, eller gir 0 for å deaktivere tekstbryting.';


/* languages names */
$langs['en_CA'] = 'Engelsk (Canadisk)';
$langs['en_GB'] = 'Engelsk (Britisk)';
$langs['en_US'] = 'English (Amerikansk)';
$langs['nb']    = 'Norsk (Bokmål)';
$langs['nl']    = 'Nederlandsk';
$langs['pt']    = 'Portugisisk';
$langs['tr']    = 'Tyrkisk';
?>
