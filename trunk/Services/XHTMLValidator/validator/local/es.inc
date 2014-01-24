<?php

// Translation: Aitor Gomez & Íñigo Goiri (twolf@telefonica.net)
// Revision: $Revision$
// EN-Revision: 1.13

$error[0] = '¡URL invalida: <em>%s</em>!';
$error[1] = '¡No se pude conectar con <em>%s</em>!';
$error[2] = '¡No hay entrada de datos!';
$error[3] = 'Fue imposible encontrar el juego de carácteres automáticamente. Se usara el por defecto.';
$error[4] = 'No hay un recurso tidy. ¡Debe llamar a parse_string() o a parse_url() antes!';
$error[5] = 'Se ha detectado un juego de carácteres automáticamente (&quot;<em>%s</em>&quot;), pero no se reconoce.';
$error[6] = 'La pagina especificada es demasiado grande (el limite es de %s bytes). ¡No puede ser analizada toda la página!';
$error[7] = 'El tiempo de conexión ha sido excedido.';
$error[8] = 'El archivo es demasiado grande. El limite es de %s bytes';
$error[9] = 'La subida del archivo no se ha completado correctamente.';
$error[10]= 'No se ha podido iniciar la libreria aspell para el idioma "%s", no se ha comprobado la ortografia.';


/* other messages */
$message[0]  = 'Configurar opciones';
$message[1]  = 'Formato de los errores';
$message[2]  = 'Como mostrar el HTML/XML reparado';
$message[3]  = 'Url a validar';
$message[4]  = 'Validar';
$message[5]  = 'Documentación';
$message[6]  = 'a lo largo de la fuente';
$message[7]  = 'solo';
$message[8]  = 'Mostrar errores';
$message[9]  = 'Mostrar HTML/XML reparado';
$message[10] = 'Lleno';
$message[11] = 'Diferencia';
$message[12] = 'ninguno';
$message[13] = 'Ocurrio un error';
$message[14] = 'Versión detectada';
$message[15] = '¡No se encontraron errores!';
$message[16] = 'Errores';
$message[17] = 'HTML/XML reparado';
$message[18] = 'Archivo';
$message[19] = 'Archivo subido';
$message[20] = 'Codificación en curso';
$message[21] = 'Errores encontrados';
$message[22] = 'Validador HTML/XML';
$message[23] = 'Probablemente la palabra "%s" haya sido mal escrita. Posibles recomendaciones: %s';
$message[24] = 'Idioma';
$message[25] = 'no';
$message[99] = 'Este validador HTML, XHTML y XML esta basado en <a href="http://tidy.sf.net">Tidy</a> y <a href="http://www.php.net">PHP 5</a>, y le permite validar y reparar automaticamente HTML, XHTML y XML. Tambien le permite convertir documentos HTML a XHTML con un simple click.</p><p>Por favor, hagase notar que ambos programas estan en desarrollo. Si encuentra algun error o si tienes alguna pregunta/sugerencia, por favor haga uso de nuestro <a href="http://sourceforge.net/tracker/?group_id=143995">tracker</a>.';


/* Name for each option */
$opts['accessibility-check']['name'] = 'Nivel de accesibilidad';
$opts['alt-text']['name']            = 'Texto &quot;alt&quot;';
$opts['charset']['name']             = 'Juego de carácteres';
$opts['hide-comments']['name']       = 'Eliminar comentarios';
$opts['indent']['name']              = 'Alineamiento';
$opts['indent-spaces']['name']       = 'Espacios para el alineamiento';
$opts['output']['name']              = 'Formato entrada/salida';
$opts['spellcheck']['name']          = 'Comprobación ortográfica';
$opts['wrap']['name']                = 'Tamaño de linea';


/* description for each option */
$opts['accessibility-check']['explain'] = 'Seleccione el nivel de prioridad para el validador de accesibilidad, o 0 para deshabilitar la opción. Más información en: <a href="http://www.w3.org/WAI/">http://www.w3.org/WAI/</a>';
$opts['alt-text']['explain']            = 'Introduzca el texto para que automáticamente se complete el atributo alt que puede no encontrarse en algunas imagenes. ¡Debería cambiar ese texto posteriormente con una descripción real de la imagen!';
$opts['charset']['explain']             = 'Introduzca el juego de carácteres que debera ser usado para analizar el documento o deje auto si desea tratar de detectarlo automáticamente.';
$opts['hide-comments']['explain']       = 'Seleccione esta opción si desea eliminar los comentarios del documento.';
$opts['indent']['explain']              = 'Seleccione esta opción para alinear automaticamente el texto.';
$opts['indent-spaces']['explain']       = 'Espacios alineados. Debe usted habilitar la opción de alineamiento.';
$opts['output']['explain']              = 'Seleccione el formato de entrada/salida. Quizás quiera usted escoger la opción &quot;XHTML&quot; si quiere convertir el documento HTML a XHTML automáticamente.';
$opts['spellcheck']['explain']          = "Seleccione el idioma para realizar una correción ortográfica o seleccione 'no' para deshabilitarlo. Notese que los errores repetidos en la misma linea no son incluidos en el informe.";
$opts['wrap']['explain']                = 'Limita el tamaño de linea a x carácteres. Para desactivar introduce 0.';


/* languages names */
$langs['en_CA'] = 'Inglés (Canadiense)';
$langs['en_GB'] = 'Inglés (Británico)';
$langs['en_US'] = 'Inglés (Americano)';
$langs['ca']    = 'Catalán';
$langs['da']    = 'Danés';
$langs['es']    = 'Español';
$langs['eu']    = 'Euskara';
$langs['it']    = 'Italiano';
$langs['nb']    = 'Noruego (Bokmal)';
$langs['nl']    = 'Holandés';
$langs['pl']    = 'Polaco';
$langs['pt']    = 'Portugues';
$langs['sv']    = 'Sueco';
$langs['tr']    = 'Turco';
?>
