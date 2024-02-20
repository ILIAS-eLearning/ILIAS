<?php

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

/**
 * Class ilAccessibilitySupportContactsGUI
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilAccessibilitySupportContactsGUI implements ilCtrlBaseClassInterface
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ILIAS\HTTP\Services $http;

    public function __construct()
    {
        global $DIC;

        $ctrl = $DIC->ctrl();
        $lng = $DIC->language();
        $http = $DIC->http();

        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->http = $http;
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd("sendIssueMail");
        if (in_array($cmd, array("sendIssueMail"))) {
            $this->$cmd();
        }
    }

    public function sendIssueMail(): void
    {
        $back_url = $this->http->request()->getServerParams()["HTTP_REFERER"];
        $this->ctrl->redirectToURL(
            ilMailFormCall::getRedirectTarget(
                $back_url,
                "",
                [],
                [
                    "type" => "new",
                    "rcp_to" => $this->getContactLogins(),
                    "sig" => $this->getAccessibilityIssueMailMessage($back_url)
                ]
            )
        );
    }

    private function getAccessibilityIssueMailMessage(string $back_url): string
    {
        $sig = chr(13) . chr(10) . chr(13) . chr(10) . chr(13) . chr(10);
        $sig .= $this->lng->txt("report_accessibility_link");
        $sig .= chr(13) . chr(10);
        $sig .= $back_url;
        $sig = rawurlencode(base64_encode($sig));

        return $sig;
    }

    /**
     * Get accessibility support contacts as comma separated string
     */
    private function getContactLogins(): string
    {
        $logins = [];

        foreach (ilAccessibilitySupportContacts::getValidSupportContactIds() as $contact_id) {
            $logins[] = ilObjUser::_lookupLogin($contact_id);
        }

        return implode(",", $logins);
    }

    public static function getFooterLink(): string
    {
        global $DIC;

        $ctrl = $DIC->ctrl();
        $user = $DIC->user();
        $http = $DIC->http();
        $lng = $DIC->language();
        $rbac_system = $DIC->rbac()->system();

        $contacts = ilAccessibilitySupportContacts::getValidSupportContactIds();
        if (count($contacts) > 0) {
            if ($rbac_system->checkAccess("internal_mail", ilMailGlobalServices::getMailObjectRefId())) {
                return $ctrl->getLinkTargetByClass("ilaccessibilitysupportcontactsgui", "");
            } else {
                $mails = ilLegacyFormElementsUtil::prepareFormOutput(
                    ilAccessibilitySupportContacts::getMailsToAddress()
                );
                $request_scheme =
                    isset($http->request()->getServerParams()["HTTPS"])
                    && $http->request()->getServerParams()["HTTPS"] !== "off"
                        ? "https" : "http";
                $url = $request_scheme . "://"
                    . $http->request()->getServerParams()["HTTP_HOST"]
                    . $http->request()->getServerParams()["REQUEST_URI"];
                return "mailto:" . $mails . "?body=%0D%0A%0D%0A" . $lng->txt("report_accessibility_link_mailto") . "%0A" . rawurlencode($url);
            }
        }
        return "";
    }

    public static function getFooterText(): string
    {
        global $DIC;

        $lng = $DIC->language();
        return $lng->txt("report_accessibility_issue");
    }
}
