<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/


$messages = array();

$defaultLanguage = "en";

$lang = (isset($_GET['lang'])) ? $_GET['lang'] : $defaultLanguage;

$messages['de'] = "Sie können nun das Fenster schließen.";
$messages['en'] = "You can now close the window.";
$messages['fr'] = "Vous pouvez maintenant fermer la fenêtre de navigation";

$message = (array_key_exists($lang, $messages)) ? $messages[$lang] : $messages[$defaultLanguage];
echo "<pre>{$message}</pre>";
exit;
