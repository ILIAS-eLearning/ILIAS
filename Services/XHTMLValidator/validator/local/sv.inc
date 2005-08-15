<?php

// Translation: Thomas Jansson (thomasjansson@users.sourceforge.net)
// Revision: $Revision$
// EN-Revision: 1.11

$error[0] = 'Felaktig URL: <em>%s</em>!';
$error[1] = 'Kunde inte ansluta till <em>%s</em>!';
$error[2] = 'Ingen indata!';
$error[3] = 'Kunde inte finna teckenuppsättningen automatiskt. Använder den förinställda.';
$error[4] = 'Det finns ingen tidy-resurs. Anropa parse_string() eller parse_url() först!';
$error[5] = 'Detekterade en teckenuppsättning automatiskt (&quot;<em>%s</em>&quot;), men kunde inte förstå den.';
$error[6] = 'Sidan du anger är för stor (gränsen är %s bytes). Hela sidan kommer ej att tolkas!';
$error[7] = 'Anslutningen dog ut (timeout).';
$error[8] = 'Filen är för stor. Gränsen är %s bytes';
$error[9] = 'Uppladdningen av filen slutfördes inte lyckat.';
$error[10]= 'Kunde inte starta aspell-biblioteket för språket "%s", så ingen stavningskontroll har utförts.';


/* other messages */
$message[0]  = 'Konfigurera alternativ';
$message[1]  = 'Felformat';
$message[2]  = 'Hur den reparerade HTML/XML-koden skall visas';
$message[3]  = 'Url att validera';
$message[4]  = 'Validera';
$message[5]  = 'Dokumentation';
$message[6]  = 'tillsammans med källkoden';
$message[7]  = 'enbart';
$message[8]  = 'Visa fel';
$message[9]  = 'Visa reparerad HTML/XML';
$message[10] = 'Full';
$message[11] = 'Skillnad';
$message[12] = 'ingen';
$message[13] = 'Ett fel uppstod';
$message[14] = 'Detekterade version';
$message[15] = 'Inga fel hittades!';
$message[16] = 'Fel';
$message[17] = 'Reparerad HTML/XML';
$message[18] = 'Fil';
$message[19] = 'Filuppladdning';
$message[20] = 'Använd kodning';
$message[21] = 'Fel funna';
$message[22] = 'HTML/XML-validerare';
$message[23] = 'Ordet "%s" är antagligen felstavat. Möjliga förslag är: %s';
$message[24] = 'Språk';
$message[25] = 'nej';
$message[99] = 'Denna HTML-, XHTML- och XML-validerare är baserad på <a href="http://tidy.sf.net">Tidy</a> och <a href="http://www.php.net">PHP 5</a>, och låter dig validera och automatiskt reparera HTML, XHTML and XML. Den låter dig också omvandla HTML-dokument till XHTML med en enda knapp.</p><p>Var god att notera att både detta program liksom Tidy för närvarande är under utveckling. Om du hittar en bugg eller om du harnågra frågor eller synpunkter, använd vår <a href="http://sourceforge.net/tracker/?group_id=143995">tracker</a>.';


/* Name for each option */
$opts['accessibility-check']['name'] = 'Tillgänglighetskontrollnivå';
$opts['alt-text']['name']            = 'Alternativ text';
$opts['charset']['name']             = 'Teckenuppsättning';
$opts['hide-comments']['name']       = 'Ta bort kommentarer';
$opts['indent']['name']              = 'Indentera';
$opts['indent-spaces']['name']       = 'Indentera mellanslag';
$opts['output']['name']              = 'In/ut-format';
$opts['spellcheck']['name']          = 'Stavningskontroll';
$opts['wrap']['name']                = 'Automatiskt radbyte';


/* description for each option */
$opts['accessibility-check']['explain'] = 'Ange prioritetsnivån för tillgänglighetsvalideraren, eller 0 för att inaktivera tillgänglighetskontroller. Mer information på: <a href="http://www.w3.org/WAI/">http://www.w3.org/WAI/</a>';
$opts['alt-text']['explain']            = 'Ange texten för att automatiskt fylla i alt-attributet som kan saknas för vissa bilder. Du bör ändra denna text senare till en riktig beskrivning av bilden!';
$opts['charset']['explain']             = 'Ange teckenuppsättningen som skall användas för att tolka dokumentet, eller lämna som auto om du vill försöka detektera den automatiskt.';
$opts['hide-comments']['explain']       = 'Kryssa i detta alternativ om du vill ta bort kommentarerna i dokumentet.';
$opts['indent']['explain']              = 'Kryssa i detta alternativ om du vill indentera din text automatiskt.';
$opts['indent-spaces']['explain']       = 'Indentera mellanslag. Du måste även aktivera Indentera-alternativet.';
$opts['output']['explain']              = 'Välj in/ut-format. Du kan ange alternativet &quot;XHTML&quot; om du vill konvertera ett HTML-dokument till XHTML automatiskt.';
$opts['spellcheck']['explain']          = "Välj ett språk för att köra stavningskontroll på ditt dokument, eller välj 'nej' för att stänga av den. Notera att upprepade fel på samma rad inte rapporteras.";
$opts['wrap']['explain']                = 'Gör radbyte vid den angivna radlängden, eller skriv 0 för att ej använda automatiskt radbyte.';


/* languages names */
$langs['en_CA'] = 'Engelska (Kanadensisk)';
$langs['en_GB'] = 'Engelska (Brittisk)';
$langs['en_US'] = 'Engelska (Amerikansk)';
$langs['it']    = 'Italienska';
$langs['nb']    = 'Norska (Bokmal)';
$langs['nl']    = 'Holländska';
$langs['pl']    = 'Polska';
$langs['pt']    = 'Portugisiska';
$langs['sv']    = 'Svenska';
$langs['tr']    = 'Turkiska';
?>
