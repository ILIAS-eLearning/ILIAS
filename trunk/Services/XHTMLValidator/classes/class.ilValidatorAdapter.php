<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/XHTMLValidator/validator/config.inc';
$GLOBALS["opts"] = $opts;

// include bundled Text_Diff PEAR package
require_once './Services/XHTMLValidator/validator/Text_Diff/Diff.php';
require_once './Services/XHTMLValidator/validator/Text_Diff/Renderer.php';
require_once './Services/XHTMLValidator/validator/Text_Diff/unified.php';

require_once './Services/XHTMLValidator/validator/include.inc';
require_once './Services/XHTMLValidator/validator/validator.inc';


/**
* Adapter Class for XHTML Validator
*
* This service is used in developer mode to validate the current screen against
* XHTML and Accessibility standards. In developer mode a corresponding link
* will appear in the footer of the page.
*
* @ingroup Services/XHTMLValidator
*/
class ilValidatorAdapter
{
	
	function validate($a_html, $a_mode)
	{
		// while php 5.1 isn't released we must keep compatibility with php 5.0
		if (!defined('UPLOAD_ERR_CANT_WRITE'))
			define('UPLOAD_ERR_CANT_WRITE', 7);
		
		/*
		   Mozilla, Firefox and friends have an error that lock radio buttons.
		   We use this to disable some feature for these browsers
		   More info at: http://bugzilla.mozilla.org/show_bug.cgi?id=229925
		*/
		define('MOZILLA', (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko/') !== false));
		
		//function mozilla($txt) {
		//	return MOZILLA ? '' : $txt;
		//}
		
		
		if (empty($lang)) {
			$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		} else {
			$redir = true;
		}

		/*
		if (empty($_COOKIE['lang']) || $lang != $_COOKIE['lang']) {
			setcookie('lang', $lang, time()+60*60*24*90, '/', 'validator.aborla.net');
		}*/

		//output_add_rewrite_var('lang', $lang);
		
		require './Services/XHTMLValidator/validator/local/en.inc'; // fallback for not translated messages

		//if ($lang != 'en')
		//	require "./local/$lang.inc"; //load localized messages
		
		//common_header();
				
		
		/****************** START OF THE VALIDATOR ITSELF *******************/
		
		// output: html/xhtml/xml, accessibility-check: 0-3
		$options = array();
		$options["charset"] = "utf8";
		if ($a_mode == "accessibility")
		{
			$options["accessibility-check"] = 3;
		}

		$validator = new validator($options);
		
		//$result = $validator->parse_url((string)$_REQUEST['url']);
		$result = $validator->parse_string($a_html);
		
		/* no fatal errors. continue */
		if ($result){

			if ($validator->internal_error()) {
				$answer.= "<p>$message[13]:<br />\n";
				$answer.= nl2br($validator->internal_errors_string()) . "</p>\n";
			}
		
			$answer.= '<p>';
		
		
			/* If the document contains too many errors, the version may not be found */
			if ($detected_version = $validator->detected_version()) {
				$answer.= "<strong>$message[14]</strong>: $detected_version<br />\n";
			}
		
			// enconding in use
			$answer.= "<strong>$message[20]</strong>: {$validator->readable_charset()}";
		
			// language
			if ($validator->lang)
				$answer.= "<br />\n<strong>$message[24]</strong>: {$langs[$validator->lang]}";
		
		
			/* Show HTML/XML errors */
			if ($errors = $validator->error()) {
		
				$answer.= "<br />\n<strong>$message[21]</strong>: $errors</p>\n".
					 "<h2>$message[16]</h2>\n";
		
				if (empty($_REQUEST['errors']) || $_REQUEST['errors'] == 'along') {
					$answer.= '<code>' . $validator->errors_source() . "</code>\n\n";
		
				} else { //errors alone
					$answer.= '<p>' . $validator->errors_string() . "</p>\n\n";
				}
		
			/* no errors found in the document */
			} else {
				$answer.= "</p><p>$message[15]</p>\n";
			}
		
		
			/* show repaired document */
			if (!isset($_REQUEST['repair']) || $_REQUEST['repair'] == 'full') {
				$html = strtr(nl2br(htmlspecialchars($validator->repaired_source(), ENT_QUOTES, 'UTF-8')), array('  ' => ' &nbsp;'));
		
				$answer.= "\n<p>&nbsp;</p>\n<h2>$message[17]</h2>\n";
				$answer.= '<div class="code"><code>' . $html . "</code></div>\n";
			}
			/* Diff */
		/*
			} elseif ($_REQUEST['repair'] == 'diff' && $diff = $validator->repaired_diff()) {
				$diff = strtr($diff, array('  ' => ' &nbsp;'));
		
				echo "\n<p>&nbsp;</p>\n<h2>$message[11]</h2>\n";
				echo '<div class="code"><code>' . $diff . "</code></div>\n";
			}
		*/
		
		
		/* there was some error handling the URL/file upload */
		} else {
			$answer.= "<p>$message[13]:<br />\n";
			$answer.= nl2br($validator->internal_errors_string()) . "</p>\n";
		}

		return $answer;
	}
}
?>
