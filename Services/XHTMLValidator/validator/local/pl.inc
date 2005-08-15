<?php

// Translation: Rzoock (rzoock@wp.pl)
// Revision: $Revision$
// EN-Revision: 1.10
// original charset: CP 1250

$error[0] = 'Nieprawidłowy adres URL: <em>%s</em>!';
$error[1] = 'Nie można połączyć się z <em>%s</em>!';
$error[2] = 'Brak danych wejściowych!';
$error[3] = 'Nie można automatycznie odnaleźć zestawu znaków. Zostanie użyty domyślny zestaw znaków.';
$error[4] = 'Nie ma zasobu tidy. Musisz najpierw wykonać funkcję parse_string() lub parse_url()!';
$error[5] = 'Automatycznie wykryto zestaw znaków (&quot;<em>%s</em>&quot;), ale nie jest on rozpoznawany.';
$error[6] = 'Podana strona jest zbyt duża (maks. rozmiar strony wynosi %s bajtów). Nie cała strona zostanie przeanalizowana!';
$error[7] = 'Upłynął limit czasu połączenia.';
$error[8] = 'Plik jest zbyt duży. Maksymalny rozmiar pliku wynosi %s bajtów';
$error[9] = 'Wysyłanie pliku nie powiodło się.';
$error[10]= 'Nie można uruchomić biblioteki językowej dla języka "%s", więc nie dokonano sprawdzania pisowni.';


/* other messages */
$message[0]  = 'Ustawienia opcji';
$message[1]  = 'Format błędów';
$message[2]  = 'Sposób wyświetlania naprawionego kodu HTML/XML';
$message[3]  = 'Adres url do analizy';
$message[4]  = 'Analizuj';
$message[5]  = 'Dokumentacja';
$message[6]  = 'razem ze źródłem';
$message[7]  = 'tylko błędy';
$message[8]  = 'Pokaż błędy';
$message[9]  = 'Pokaż naprawiony kod HTML/XML';
$message[10] = 'Pełen';
$message[11] = 'Różnice';
$message[12] = 'nie pokazuj';
$message[13] = 'Wystąpił błąd';
$message[14] = 'Wykryta wersja';
$message[15] = 'Nie znaleziono żadnych błędów!';
$message[16] = 'Błędy';
$message[17] = 'Naprawiony HTML/XML';
$message[18] = 'Plik';
$message[19] = 'Wyślij plik';
$message[20] = 'Stosowane kodowanie';
$message[21] = 'Znalezione błędy';
$message[22] = 'Walidator HTML/XML';
$message[23] = 'Wyraz "%s" prawdopodobnie zawiera błąd. Sugerowana zmiana na: %s';
$message[24] = 'Język';
$message[25] = 'nie';
$message[99] = 'Niniejszy walidator kodu HTML, XHTML oraz XML jest oparty na programach <a href="http://tidy.sf.net">Tidy</a> oraz <a href="http://www.php.net">PHP 5</a>. Program umożliwia walidację i automatyczną naprawę kodu HTML, XHTML oraz XML. Za pomocą jednego przycisku można także konwertować dokumenty HTML na XHTML.</p><p>Proszę pamiętać, że zarówno niniejszy program jak i Tidy są obecnie w fazie intensywnego rozwoju. Jeśli znajdziesz błąd lub masz pytania/sugestie, skorzystaj z naszego <a href="http://sourceforge.net/tracker/?group_id=143995">systemu monitoringu</a>.';


/* Name for each option */
$opts['accessibility-check']['name'] = 'Poziom sprawdzania dostępności';
$opts['alt-text']['name']            = 'Tekst opisu';
$opts['charset']['name']             = 'Zestaw znaków';
$opts['hide-comments']['name']       = 'Usuwanie komentarzy';
$opts['indent']['name']              = 'Wcięcia';
$opts['indent-spaces']['name']       = 'Szerokość wcięcia';
$opts['output']['name']              = 'Format wejścia/wyjścia';
$opts['spellcheck']['name']          = 'Sprawdzanie pisowni';
$opts['wrap']['name']                = 'Zawijanie wierszy';


/* description for each option */
$opts['accessibility-check']['explain'] = 'Wybierz priorytet dla Walidatora dostępności lub zaznacz 0, żeby wyłączyć sprawdzanie dostępności. Zobacz też: <a href="http://www.w3.org/WAI/">http://www.w3.org/WAI/</a>';
$opts['alt-text']['explain']            = 'Wprowadź tekst, który ma być wpisywany automatycznie do niewypełnionych atrybutów alt obrazów. Po wykonaniu walidacji należy zmienić opisy obrazów na prawidłowe!';
$opts['charset']['explain']             = 'Wybierz zestaw znaków, który ma być używany do analizy dokumentu lub pozostaw opcję auto, jeśli walidator ma próbować wykryć zestaw znaków automatyczne.';
$opts['hide-comments']['explain']       = 'Zaznacz tę opcję, jeśli chcesz usunąć komentarze z dokumentu.';
$opts['indent']['explain']              = 'Zaznacz tę opcję, jeśli chcesz, żeby walidator automatycznie zrobił wcięcia w tekście.';
$opts['indent-spaces']['explain']       = 'Szerokość wcięć. Aby uaktywnić tę opcję należy zaznaczyć pole wyboru Wcięcia.';
$opts['output']['explain']              = 'Wybierz format pliku wejściowego/wyjściowego. Opcja &quot;XHTML&quot; powoduje automatyczną konwersję dokumentu HTML na XHTML.';
$opts['spellcheck']['explain']          = "Wybierz język, w którym ma być sprawdzana pisownia w dokumencie lub zaznacz 'nie', żeby wyłączyć sprawdzanie pisowni. Uwaga: jeśli ten sam błąd wystąpi w jednej linii więcej niż raz, to zostanie zgłoszone tylko jedno jego wystąpienie.";
$opts['wrap']['explain']                = 'Podaj maksymalną liczbę znaków w jednym wierszu (po wskazanym znaku wiersze będą zawijane) lub wpisz 0, żeby nie zawijać wierszy.';


/* languages names */
$langs['en_CA'] = 'angielski (Kanada)';
$langs['en_GB'] = 'angielski (UK)';
$langs['en_US'] = 'angielski (USA)';
$langs['it']    = 'włoski';
$langs['nb']    = 'norweski (Bokmal)';
$langs['nl']    = 'niderlandzki';
$langs['pl']    = 'polski';
$langs['pt']    = 'portugalski';
$langs['tr']    = 'turecki';
?>
