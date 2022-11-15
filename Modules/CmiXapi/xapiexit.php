<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


$messages = array();

$defaultLanguage = "en";
$lang = (isset($_GET['lang'])) ? $_GET['lang'] : $defaultLanguage;

$messages['de'] = "Sie können nun das Fenster schließen.";
$messages['en'] = "You can now close the window.";
$messages['fr'] = "Vous pouvez maintenant fermer la fenêtre de navigation";

$message = (array_key_exists($lang, $messages)) ? $messages[$lang] : $messages[$defaultLanguage];
echo "<pre>{$message}</pre>";
exit;
