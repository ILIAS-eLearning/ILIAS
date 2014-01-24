<?php

// Translation: Aitor Gomez & Íñigo Goiri (twolf@telefonica.net)
// Revision: $Revision$
// EN-Revision: 1.5

$error[0] = 'URL invàlida: <em>%s</em>!';
$error[1] = 'No es pot conectar amb <em>%s</em>!';
$error[2] = 'No hi ha entrada de dades!';
$error[3] = "Va ser impossible trobar el joc de caràcters automàticament. S'utilitzarà el per defecto.";
$error[4] = 'No hi ha un recurs tidy. ¡Ha de cridar a parse_string() o a parse_url() abans!';
$error[5] = "S'ha detectat un joc de caràcters automàticament (&quot;<em>%s</em>&quot;), però no es reconeix.";
$error[6] = 'La pàgina especificada es massa gran (el límit es de %s bytes). No pot ser analitzat tota la pàgina!';
$error[7] = 'El temps de conexió ha sigut sobrepassat.';
$error[8] = 'El arxiu é massa gran. El límit és de %s bytes';
$error[9] = "La pujada del arxiu no s'ha completat correctament.";
$error[10]= "No s'ha pogut iniciar la llibrería aspell pel idioma '%s', no s'ha comprobat l'ortografia.";


/* other messages */
$message[0]  = 'Configurar opcions';
$message[1]  = 'Format dels errors';
$message[2]  = 'Com mostrar el HTML/XML reparat';
$message[3]  = 'Url a validar';
$message[4]  = 'Validar';
$message[5]  = 'Documentació';
$message[6]  = 'al llarg de la font';
$message[7]  = 'sol';
$message[8]  = 'Mostrar errors';
$message[9]  = 'Mostrar HTML/XML reparat';
$message[10] = 'Ple';
$message[11] = 'Diferència';
$message[12] = 'cap';
$message[13] = 'Va ocorrer un error';
$message[14] = 'Versió detectada';
$message[15] = "No s'han trobat errors";
$message[16] = 'Errors';
$message[17] = 'HTML/XML reparat';
$message[18] = 'Arxiu';
$message[19] = 'Arxiu pujat';
$message[20] = 'Codificació en curs';
$message[21] = 'Errors trobats';
$message[22] = 'Validador HTML/XML';
$message[23] = 'Probablement la paraula "%s" ha sigut mal escrita. Possibles recomanacions: %s';
$message[24] = 'Idioma';
$message[25] = 'no';
$message[99] = 'Aquest validador HTML, XHTML i XML està basat en <a href="http://tidy.sf.net">Tidy</a> i <a href="http://www.php.net">PHP 5</a>, i li permet validar i reparar automàticament HTML, XHTML i XML. També li permet convertir documents HTML a XHTML amb un simple click.</p><p>Sisplau, noti que ambdos programes estan en desenvolupament. Si troba cap error o si té cap pregunta/suggerència, sisplau falli ús del nostre <a href="http://sourceforge.net/tracker/?group_id=143995">tracker</a>.';


/* Name for each option */
$opts['accessibility-check']['name'] = "Nivell d'accessibilitat";
$opts['alt-text']['name']            = 'Text &quot;alt&quot;';
$opts['charset']['name']             = 'Joc de caràcters';
$opts['hide-comments']['name']       = 'Elimina comentaris';
$opts['indent']['name']              = 'Alineament';
$opts['indent-spaces']['name']       = 'Espais pel alineament';
$opts['output']['name']              = "Format d'entrada/sortida";
$opts['spellcheck']['name']          = 'Comprobació ortogràfica';
$opts['wrap']['name']                = 'Tamany de linea';


/* description for each option */
$opts['accessibility-check']['explain'] = 'Seleccioni el nivell de prioritat pel validador d`accesibilidad, o 0 per deshabilitar l`opció. Més informació a: <a href="http://www.w3.org/WAI/">http://www.w3.org/WAI/</a>';
$opts['alt-text']['explain']            = 'Introdueixi el text per que automàticament es completi l`atribut alt que pot no trobar-se en algunes imàtges. Hauria de cambiar aquest text posteriorment amb una descripció real de la imatge!';
$opts['charset']['explain']             = 'Introdueixi el joc de caràcters que haurà de ser utilitzat per analitzar el document o deixi auto si desitja travtar de detectar-ho automàticament.';
$opts['hide-comments']['explain']       = 'Seleccioni aquesta opció si desitja eliminar els comentarios del document.';
$opts['indent']['explain']              = 'Seleccioni aquesta opció per alinear automàticament el text.';
$opts['indent-spaces']['explain']       = 'Espais alineats. Ha d`habilitar l`opció d`alineament.';
$opts['output']['explain']              = 'Seleccioni el format d`entrada/sortida. A lo millor vostè voldria escollir la opció &quot;XHTML&quot; si vol convertir el document HTML a XHTML automàticament.';
$opts['spellcheck']['explain']          = "Seleccioni l'idioma per realitzar una correcció ortogràfica o seleccioni 'no' per deshabilitar-ho. Noti que els errors repetits a la mateixa linea no són inclosos a l`informe.";
$opts['wrap']['explain']                = 'Limiti el tamany de linea a x caràcters. Per desactivar introdueixi 0.';


/* languages names */
$langs['en_CA'] = 'Anglès (Canadenc)';
$langs['en_GB'] = 'Anglès (Britànic)';
$langs['en_US'] = 'Anglès (Americà)';
$langs['ca']    = 'Català';
$langs['es']    = 'Castellà';
$langs['eu']    = 'Eusquera';
$langs['pt']    = 'Portugués';
$langs['tr']    = 'Turc';
?>
