<?php

// Translation: Nuno Lopes
// Revision: $Revision$
// EN-Revision: 1.13

$error[0] = 'URL Inválido: <em>%s</em>!';
$error[1] = 'Erro ao ligar a <em>%s</em>!';
$error[2] = 'Tem que especificar o $html!';
$error[3] = 'Não consegui encontrar o charset automaticamente. Vou usar o charset padrão.';
$error[4] = 'Não existe nenhum recurso tidy. Use as funções parse_string() ou parse_url() primeiro!';
$error[5] = 'Detectei um charset automaticamente, &quot;<em>%s</em>&quot;, mas não o consegui reconhecer!';
$error[6] = 'A página que indicou é demasiado grande! A página não vai ser toda analizada';
$error[7] = 'A ligação excedeu o tempo limite.';
$error[8] = 'O ficheiro é demasiado grande. O limite são %s bytes';
$error[9] = 'O upload do ficheiro não foi concluido.';
$error[10]= 'Não foi possível iniciar o programa aspell para a língua "%s". Como resultado, a verificação ortográfica não será realizada.';


/* outras mensagens */
$message[0]  = 'Opções';
$message[1]  = 'Formato dos erros';
$message[2]  = 'Formato do HTML/XML reparado';
$message[3]  = 'Url para validar';
$message[4]  = 'Validar';
$message[5]  = 'Documentação';
$message[6]  = 'com o código original';
$message[7]  = 'em separado';
$message[8]  = 'Mostrar erros';
$message[9]  = 'Mostrar HTML/XML reparado';
$message[10] = 'completo';
$message[11] = 'differenças';
$message[12] = 'não';
$message[13] = 'Ocurreu um erro';
$message[14] = 'Versão detectada';
$message[15] = 'Não foram detectados quaisquer erros!';
$message[16] = 'Erros';
$message[17] = 'HTML/XML reparado';
$message[18] = 'Ficheiro';
$message[19] = 'Upload de ficheiro';
$message[20] = 'Charset em uso';
$message[21] = 'Erros encontrados';
$message[22] = 'Validador de HTML/XML';
$message[23] = 'A palavra "%s" está provavelmente mal escrita. Possíveis correcções incluem: %s';
$message[24] = 'Língua';
$message[25] = 'não';
$message[99] = 'Este validador de HTML, XHTML e XML é baseado no <a href="http://tidy.sf.net">Tidy</a> e no <a href="http://www.php.net">PHP 5</a>, e possibilita a validação e reparação automática de documentos HTML, XHTML and XML. Ele também permite converter documentos HTML para XHTML com um simples clique.</p><p>Note que este programa e o Tidy ainda estão em desenvolvimento. Se encontrar algum erro ou tiver alguma questão/sugestão, use o nosso <a href="http://sourceforge.net/tracker/?group_id=143995">tracker</a>.';


/* Nome de cada opção */
$opts['accessibility-check']['name'] = 'Nível de acessibilidade';
$opts['alt-text']['name']            = 'Texto &quot;alt&quot;';
$opts['charset']['name']             = 'Charset';
$opts['hide-comments']['name']       = 'Eliminar comentários';
$opts['indent']['name']              = 'Alinhamento';
$opts['indent-spaces']['name']       = 'Espaços para alinhamento';
$opts['output']['name']              = 'Formato';
$opts['spellcheck']['name']          = 'Verificação Ortográfica';
$opts['wrap']['name']                = 'Tamanho de linha';


/* descrição de cada opção */
$opts['accessibility-check']['explain'] = 'Seleccione o nível para validação de acessibilidade, ou introduza 0 para desactivar. Mais informações em: <a href="http://www.w3.org/WAI/">http://www.w3.org/WAI/</a>.';
$opts['alt-text']['explain']            = 'Introduza o texto para preencher automaticamente o texto &quot;alt&quot;. Mais tarde deve personalizar este texto para cada imagem.';
$opts['charset']['explain']             = 'Introduza o charset que vai ser usado para analisar o documento, ou seleccione &quot;auto&quot; para detectá-lo automaticamente.';
$opts['hide-comments']['explain']       = 'Seleccione esta opção para remover os comentários no documento.';
$opts['indent']['explain']              = 'Seleccione esta opção para alinhar o documento automaticamente.';
$opts['indent-spaces']['explain']       = 'Espaços para alinhar. É necessário que a opção &quot;Alinhamento&quot; esteja activa.';
$opts['output']['explain']              = 'Seleccione o formato do documento. Pode seleccionar a opção &quot;XHTML&quot; se desejar converter automaticamente um documento HTML para XHTML.';
$opts['spellcheck']['explain']          = "Seleccione uma língua para realizar uma verificação ortográfica, ou seleccione 'não' para desactivar esta opção. Observe porém que em caso de haver erros repetidos na mesma linha, apenas um é reportado.";
$opts['wrap']['explain']                = 'Limita o tamanho da linha a x caracteres. Para desactivar insira 0.';


/* línguas */
$langs['en_CA'] = 'Inglês (Canadiano)';
$langs['en_GB'] = 'Inglês (Britânico)';
$langs['en_US'] = 'Inglês (Americano)';
$langs['ca']    = 'Catalão';
$langs['da']    = 'Dinamarquês';
$langs['es']    = 'Espanhol';
$langs['eu']    = 'Basco';
$langs['it']    = 'Italiano';
$langs['nb']    = 'Norueguês (Bokmal)';
$langs['nl']    = 'Holandês';
$langs['pl']    = 'Polaco';
$langs['pt']    = 'Português';
$langs['sv']    = 'Sueco';
$langs['tr']    = 'Turco';

?>
