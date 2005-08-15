<?php
/* 
   +----------------------------------------------------------------------+
   | HTML/XML Validator                                                   |
   +----------------------------------------------------------------------+
   | Copyright (c) 2004-2005 Nuno Lopes                                   |
   +----------------------------------------------------------------------+
   | This program is free software; you can redistribute it and/or        |
   | modify it under the terms of the GNU Lesser General Public           |
   | License as published by the Free Software Foundation; either         |
   | version 2.1 of the License, or (at your option) any later version.   |
   |                                                                      |
   | This program is distributed in the hope that it will be useful,      |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
   | Lesser General Public License for more details.                      |
   |                                                                      |
   | You should have received a copy of the GNU Lesser General Public     |
   | License along with this library; if not, write to the Free Software  |
   | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA             |
   | 02111-1307  USA.                                                     |
   +----------------------------------------------------------------------+
   |                     http://validator.aborla.net/                     |
   +----------------------------------------------------------------------+

vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:

     $Id$
*/

require './include.inc';
require './validator.inc';

// while php 5.1 isn't released we must keep compatibility with php 5.0
if (!defined('UPLOAD_ERR_CANT_WRITE'))
    define('UPLOAD_ERR_CANT_WRITE', 7);


/* check if the client browser sent a language */
function valid_http_lang() {
    global $valid_langs;

    if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        return false;

    $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach ($langs as $lang) {
        list($lang) = explode(';', $lang);
        list($lang) = explode('-', $lang);
        $lang = trim($lang);

        if (isset($valid_langs[$lang]))
            return $lang;
    }

    return false;
}


/*
   Mozilla, Firefox and friends have an error that lock radio buttons.
   We use this to disable some feature for these browsers
   More info at: http://bugzilla.mozilla.org/show_bug.cgi?id=229925
*/
define('MOZILLA', (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko/') !== false));

function mozilla($txt) {
    return MOZILLA ? '' : $txt;
}


/* the default 'variables_order' isn't good for us. As we can't change it
   at runtime, we do this little 'hack' to give _GET precendence */
if (isset($_GET['lang'])) {
    $_REQUEST['lang'] = &$_GET['lang'];
} elseif (isset($_POST['lang'])) {
    $_REQUEST['lang'] = &$_POST['lang'];
}


/* Check for valid language and include the local file */
if (!isset($_GET['langchooser']) &&
    (
     (isset($_REQUEST['lang']) && isset($valid_langs[(string)$_REQUEST['lang']])) ||
     ($lang = valid_http_lang()) ||
     !empty($_REQUEST['url'])
    )
   ) {

    if (empty($lang)) {
        $lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
    } else {
        $redir = true;
    }

    if (empty($_COOKIE['lang']) || $lang != $_COOKIE['lang']) {
        setcookie('lang', $lang, time()+60*60*24*90, '/', 'validator.aborla.net');
    }

    output_add_rewrite_var('lang', $lang);

    require './local/en.inc'; // fallback for not translated messages

    if ($lang != 'en')
        require "./local/$lang.inc"; //load localized messages

    common_header();

    if (isset($redir)) {
        echo '<p id="redir">We have automatically choosen the "' . $valid_langs[$lang] .
             '" language for you. <a href="/?langchooser">Click here to change</a>.</p>';
    }


/*************** LANGUAGE CHOOSER ***************/
} else {
    require "./local/en.inc";
    common_header();

    echo "<p>Choose a language, please:</p>\n" . 
         "<form method=\"get\" action=\"index.php\">\n" .
         "<p><select name=\"lang\">\n";

    foreach($valid_langs as $k => $v) {
        echo "<option value=\"$k\">$v</option>\n";
    }

    echo "</select></p>\n<p><input type=\"submit\" value=\"  Submit  \" /></p>\n</form>\n";

    die(common_footer());
}
/*********** END OF LANGUAGE CHOOSER ************/



/**************** DOCUMENTATION ****************/

if (isset($_GET['docs'])) {
    // docs 
    echo "<h2>$message[5]</h2>\n".
         "<p>$message[99]</p><p>&nbsp;</p>\n";

    // explanation of each option
    echo "<h2>$message[0]</h2>";
    foreach($opts as $v) {
        echo "<p><strong>$v[name]</strong>: $v[explain]</p>\n";
    }

    die(common_footer());
}

/***************** END OF DOCS ****************/



/***************** FIRST PAGE *****************/

if (empty($_REQUEST['url']) && (!isset($_FILES['file']) || $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE)) {

    echo '<form method="post" action="index.php" enctype="multipart/form-data"><p>' . $message[3].
	'<input type="hidden" name="MAX_FILE_SIZE" value="' . validator::FILE_LIMIT . '" />' .
	': <input type="text" name="url" size="50" value="http://" /><br />'.
	$message[19] . ': <input name="file" type="file" /></p>';

    $aditional = "$message[8]:\n" . mozilla("<label title=\"$message[1]\">\n") .
		"<input type=\"radio\" name=\"errors\" checked=\"checked\" value=\"along\" /> $message[6]\n" .
		"<input type=\"radio\" name=\"errors\" value=\"alone\" /> $message[7]\n" .
		mozilla("</label>\n") . 

		"<br />$message[9]:\n" . mozilla("<label title=\"$message[2]\">\n") .
		"<input type=\"radio\" name=\"repair\" checked=\"checked\" value=\"full\" /> $message[10]\n" .
		"<input type=\"radio\" name=\"repair\" value=\"diff\" /> $message[11]\n" .
		"<input type=\"radio\" name=\"repair\" value=\"none\" /> $message[12]\n" .
		mozilla("</label>\n") . "<br />\n";


    echo validator::html_opts($aditional);
    echo <<< HTML

<p><input type="submit" value="  $message[4]  " /></p>
</form>
<p><a href="index.php?docs">$message[5]</a></p>

HTML;

    die(common_footer());
}
/************ END OF FIRST PAGE **************/



/****************** START OF THE VALIDATOR ITSELF *******************/

$validator = new validator($_REQUEST);


/* File upload */
if(isset($_FILES['file']) && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE) {

    switch($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
            $result = $validator->parse_string(file_get_contents($_FILES['file']['tmp_name']));
            break;

        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $result = false;
            $int_error = $validator->_error(8, validator::FILE_LIMIT);
            break;

        case UPLOAD_ERR_PARTIAL:
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
            $result = false;
            $int_error = $validator->_error(9);
            break;
    }

/* URL validator */
} else {
    $result = $validator->parse_url((string)$_REQUEST['url']);
}


/* no fatal errors. continue */
if ($result){

    if ($validator->internal_error()) {
        echo "<p>$message[13]:<br />\n";
        echo nl2br($validator->internal_errors_string()) . "</p>\n";
    }

    echo '<p>';

    /* file upload */
    if(isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        echo isset($_FILES['file']['name']) ? "<strong>$message[18]</strong>: {$_FILES['file']['name']}<br />\n" : '';

    /* URL validator */
    } else {
        echo '<strong>URL</strong>: ' . htmlspecialchars($_REQUEST['url'], ENT_QUOTES, 'UTF-8') . "<br />\n";
    }

    /* If the document contains too many errors, the version may not be found */
    if ($detected_version = $validator->detected_version()) {
        echo "<strong>$message[14]</strong>: $detected_version<br />\n";
    }

    // enconding in use
    echo "<strong>$message[20]</strong>: {$validator->readable_charset()}";

    // language
    if ($validator->lang)
        echo "<br />\n<strong>$message[24]</strong>: {$langs[$validator->lang]}";


    /* Show HTML/XML errors */
    if ($errors = $validator->error()) {

        echo "<br />\n<strong>$message[21]</strong>: $errors</p>\n".
             "<h2>$message[16]</h2>\n";

        if (empty($_REQUEST['errors']) || $_REQUEST['errors'] == 'along') {
            echo '<code>' . $validator->errors_source() . "</code>\n\n";

        } else { //errors alone
            echo '<p>' . $validator->errors_string() . "</p>\n\n";
        }

    /* no errors found in the document */
    } else {
        echo "</p><p>$message[15]</p>\n";
    }


    /* show repaired document */
    if (!isset($_REQUEST['repair']) || $_REQUEST['repair'] == 'full') {
        $html = strtr(nl2br(htmlspecialchars($validator->repaired_source(), ENT_QUOTES, 'UTF-8')), array('  ' => ' &nbsp;'));

        echo "\n<p>&nbsp;</p>\n<h2>$message[17]</h2>\n";
        echo '<div class="code"><code>' . $html . "</code></div>\n";

    /* Diff */
    } elseif ($_REQUEST['repair'] == 'diff' && $diff = $validator->repaired_diff()) {
        $diff = strtr($diff, array('  ' => ' &nbsp;'));

        echo "\n<p>&nbsp;</p>\n<h2>$message[11]</h2>\n";
        echo '<div class="code"><code>' . $diff . "</code></div>\n";
    }


/* there was some error handling the URL/file upload */
} else {
    echo "<p>$message[13]:<br />\n";
    echo nl2br($validator->internal_errors_string()) . "</p>\n";
}


if (isset($_GET['dump_debug'])) {
     echo '<pre>' . htmlspecialchars($validator->debug()) . '</pre>';
}


common_footer();
?>
