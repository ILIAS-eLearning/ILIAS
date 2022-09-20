<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/** @noRector */
require_once("libs/composer/vendor/autoload.php");
ilContext::init(ilContext::CONTEXT_SHIBBOLETH);
ilInitialisation::initILIAS();
global $DIC;

$server = $DIC->http()->request()->getServerParams();

if (
    !isset($server['HTTP_SHIB_APPLICATION_ID'])
    && !isset($server['Shib-Application-ID'])
    && !isset($server['REDIRECT_Shib_Application_ID'])
) {
    $factory = $DIC->ui()->factory();
    $message_box = $factory->messageBox()->failure("The file shib_login.php must be protected by Shibboleth, otherwise you cannot use Shibboleth authentication.")->withButtons([
        $factory->button()->standard('Open Documentation', './Services/AuthShibboleth/README.md')
    ]);

    $DIC->ui()->mainTemplate()->setContent($DIC->ui()->renderer()->render($message_box));
    $DIC->ui()->mainTemplate()->printToStdout();
} else {
    // authentication is done here ->
    $DIC->ctrl()->setCmd('doShibbolethAuthentication');
    $DIC->ctrl()->callBaseClass(ilStartUpGUI::class);
}
