<?php

// Translation: Sergio Bonfiglio (hiei2@interfree.it)
// Revision: $Revision$
// EN-Revision: 1.13

$error[0] = 'URL non valida: <em>%s</em>!';
$error[1] = 'Impossibile connettersi a <em>%s</em>!';
$error[2] = 'Nessun input!';
$error[3] = 'Impossibile trovare il set di caratteri automaticamente. Uso il predefinito.';
$error[4] = 'Non ci sono risorse corrette. Chiama parse_string() o parse_url() prima!';
$error[5] = 'Ho trovato un set di caratteri automaticamente (&quot;<em>%s</em>&quot;), ma non lo riconosco.';
$error[6] = 'La pagina specificata è troppo grande (il limite è %s byte). Non tutte le pagine saranno analizzate!';
$error[7] = 'La connessione è scaduta.';
$error[8] = 'Il file è troppo grande. Il limite è %s byte';
$error[9] = "L'Upload del file non è stato completato con successo.";
$error[10]= 'Non posso fare partire la libreria ortografica per la lingua "%s": nessun controllo ortografico è stato effettuato.';


/* other messages */
$message[0]  = 'Configura Opzioni';
$message[1]  = 'Formato degli errori';
$message[2]  = "Come mostrare l'HTML/XML riparato";
$message[3]  = 'Url da convalidare';
$message[4]  = 'Convalida';
$message[5]  = 'Documentazione';
$message[6]  = 'con la sorgente';
$message[7]  = 'da soli';
$message[8]  = 'Mostra errori';
$message[9]  = "Mostra l'HTML/XML riparato";
$message[10] = 'tutto';
$message[11] = 'differenza';
$message[12] = 'nulla';
$message[13] = 'Si è verificato un errore';
$message[14] = 'Versione rilevata';
$message[15] = 'Nessun errore trovato!';
$message[16] = 'Errori';
$message[17] = 'HTML/XML riparato';
$message[18] = 'File';
$message[19] = 'Upload del file';
$message[20] = 'Codifica in uso';
$message[21] = 'Errori Trovati';
$message[22] = 'Verificatore HTML/XML';
$message[23] = 'La parola "%s" è probabilmente un errore ortografico. I possibili suggerimenti includono: %s';
$message[24] = 'Lingua';
$message[25] = 'no';
$message[99] = 'Questo convalidatore HTML, XHTML e XML  è basato su <a href="http://tidy.sf.net">Tidy</a> e <a href="http://www.php.net">PHP 5</a>, e ti permette di verificare e riparare automaticamente HTML, XHTML e XML. Inoltre ti permette di convertire documenti HTML in XHTML con un solo pulsante.</p><p>Perfavore tieni conto che sia questo programma che Tidy sono in fase di sviluppo. Se trovi un errore o o se hai qualsiasi domanda o suggerimento, ti preghiamo di usare <a href="http://sourceforge.net/tracker/?group_id=143995">tracker</a>.';


/* Name for each option */
$opts['accessibility-check']['name'] = 'Livello controllo accessibilità';
$opts['alt-text']['name']            = 'Didascalia';
$opts['charset']['name']             = 'Set di caratteri';
$opts['hide-comments']['name']       = 'Nascondi commenti';
$opts['indent']['name']              = 'Indenta';
$opts['indent-spaces']['name']       = 'Indenta spazi';
$opts['output']['name']              = 'Formato Input/Output';
$opts['spellcheck']['name']          = 'Controllo ortografico';
$opts['wrap']['name']                = 'Adatta';


/* description for each option */
$opts['accessibility-check']['explain'] = 'Seleziona il livelli di priorità per la verifica di accessibilità, oppure 0 per disabilitare il controllo sull\'accesibilità. Maggiori informazioni a: <a href="http://www.w3.org/WAI/">http://www.w3.org/WAI/</a>';
$opts['alt-text']['explain']            = "Inserisce il testo riempiendo automaticamente la didascalia che potrebbe mancare per alcune immagini. Dovresti cambiare questo testo in seguito con una descrizione reale dell'immagine!";
$opts['charset']['explain']             = 'Inserisci il set di caratteri che verrà usato per analizzare il documento, o lascia automatico se vuoi provare a rilevarlo automaticamente.';
$opts['hide-comments']['explain']       = 'Seleziona questa opzione se vuoi rimuovere i commenti nel documento.';
$opts['indent']['explain']              = 'Seleziona questa opzione se vuoi che il tuo testo venga indentato automaticamente.';
$opts['indent-spaces']['explain']       = "Indenta gli spazi. Devi anche attivare l'opzione Indenta.";
$opts['output']['explain']              = "Seleziona il formato input/output. Puoi selezionare l'opzione &quot;XHTML&quot; se vuoi convertire un documento HTML in XHTML automaticamente.";
$opts['spellcheck']['explain']          = "Seleziona una lingua per eseguire il controllo ortografico sul tuo documento, o seleziona 'no' per disabilitarlo. Errori ripetuti nella stessa linea non vengono riportati.";
$opts['wrap']['explain']                = "Adatta le linee alla lunghezza specificata, o inserisci 0 per disabilitare l'adattamento.";


/* languages names */
$langs['en_CA'] = 'Inglese (Canada)';
$langs['en_GB'] = 'Inglese (Inghilterra)';
$langs['en_US'] = 'Inglese (America)';
$langs['ca']    = 'Catalano';
$langs['da']    = 'Danese';
$langs['es']    = 'Spagnolo';
$langs['eu']    = 'Basco';
$langs['it']    = 'Italiano';
$langs['nb']    = 'Norvegese';
$langs['nl']    = 'Olandese';
$langs['pl']    = 'Smalto';
$langs['pt']    = 'Portoghese';
$langs['sv']    = 'Svedese';
$langs['tr']    = 'Turcho';
?>
