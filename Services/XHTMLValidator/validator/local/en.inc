<?php

// Revision: $Revision$

$error[0] = 'Invalid URL: <em>%s</em>!';
$error[1] = "Couldn't connect to <em>%s</em>!";
$error[2] = 'No input!';
$error[3] = "Couldn't find the charset automatically. Using the default.";
$error[4] = 'There is no tidy resource. Call parse_string() or parse_url() first!';
$error[5] = "I've detected a charset automatically (&quot;<em>%s</em>&quot;), but I couldn't recognise it.";
$error[6] = 'The page you specify is too big (the limit is %s bytes). Not all page will be parsed!';
$error[7] = 'The connection has timed out.';
$error[8] = 'The file is too big. The limit is %s bytes';
$error[9] = "The file upload didn't complete sucessfully.";
$error[10]= "Couldn't start the aspell library for the '%s' language, so no spell checking was done.";


/* other messages */
$message[0]  = 'Configure Options';
$message[1]  = 'Format of errors';
$message[2]  = 'How to show the repaired HTML/XML';
$message[3]  = 'Url to validate';
$message[4]  = 'Validate';
$message[5]  = 'Documentation';
$message[6]  = 'along with the source';
$message[7]  = 'alone';
$message[8]  = 'Show errors';
$message[9]  = 'Show repaired HTML/XML';
$message[10] = 'Full';
$message[11] = 'Diff';
$message[12] = 'none';
$message[13] = 'Occured an error';
$message[14] = 'Detected version';
$message[15] = 'No errors were found!';
$message[16] = 'Errors';
$message[17] = 'Repaired HTML/XML';
$message[18] = 'File';
$message[19] = 'File upload';
$message[20] = 'Encoding in use';
$message[21] = 'Errors Found';
$message[22] = 'HTML/XML Validator';
$message[23] = 'The word "%s" is probably misspelled. Possible suggestions include: %s';
$message[24] = 'Language';
$message[25] = 'no';
$message[99] = 'This HTML, XHTML and XML validator is based on <a href="http://tidy.sf.net">Tidy</a> and <a href="http://www.php.net">PHP 5</a>, and allows you to validate and automatically repair HTML, XHTML and XML. It also allows you to convert HTML documents to XHTML with a single button.</p><p>Please note that both this program and Tidy are currently under heavy development. If you find a bug or if you have any question/suggestion, please use our <a href="http://sourceforge.net/tracker/?group_id=143995">tracker</a>.';


/* Name for each option */
$opts['accessibility-check']['name'] = 'Accessibility check level';
$opts['alt-text']['name']            = 'Alt-Text';
$opts['charset']['name']             = 'Charset';
$opts['hide-comments']['name']       = 'Strip comments';
$opts['indent']['name']              = 'Indent';
$opts['indent-spaces']['name']       = 'Indent spaces';
$opts['output']['name']              = 'Input/Output Format';
$opts['spellcheck']['name']          = 'Spell Checking';
$opts['wrap']['name']                = 'Wrap';


/* description for each option */
$opts['accessibility-check']['explain'] = 'Select the priority level for the Accessibility validator, or 0 to disable the Accessibility checks. More info at: <a href="http://www.w3.org/WAI/">http://www.w3.org/WAI/</a>';
$opts['alt-text']['explain']            = 'Enter the text to automatically fill the alt attribute text that may be missing for some images. You should change this text later for a real description of the image!';
$opts['charset']['explain']             = 'Enter the charset that will be used to parse the document, or leave auto if you want to try to detect it automatically.';
$opts['hide-comments']['explain']       = 'Check this option if you want to remove the comments in the document.';
$opts['indent']['explain']              = 'Check this if you want to automatically indent your text.';
$opts['indent-spaces']['explain']       = 'Indent spaces. You must also enable the Indent option.';
$opts['output']['explain']              = 'Select input/output format. You may select the option &quot;XHTML&quot; if you want to convert a HTML document to XHTML automatically.';
$opts['spellcheck']['explain']          = "Select a language to run a spell checking on your document, or select 'no' to disable it. Please note that repeated errors on the same line aren't reported.";
$opts['wrap']['explain']                = 'Wrap lines at the specified line length, or enter 0 to disable wraping.';


/* languages names */
$langs['en_CA'] = 'English (Canadian)';
$langs['en_GB'] = 'English (British)';
$langs['en_US'] = 'English (American)';
$langs['ca']    = 'Catalan';
$langs['da']    = 'Danish';
$langs['es']    = 'Spanish';
$langs['eu']    = 'Basque';
$langs['it']    = 'Italian';
$langs['nb']    = 'Norwegian (Bokmal)';
$langs['nl']    = 'Dutch';
$langs['pl']    = 'Polish';
$langs['pt']    = 'Portuguese';
$langs['sv']    = 'Swedish';
$langs['tr']    = 'Turkish';
?>
