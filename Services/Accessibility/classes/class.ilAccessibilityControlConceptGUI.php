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
 * Class ilAccessibilityControlConceptGUI
 * @author Thomas Famula <famula@leifos.de>
 */
class ilAccessibilityControlConceptGUI implements ilCtrlBaseClassInterface
{
    protected ilSetting $settings;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected \ILIAS\HTTP\Services $http;
    protected ilObjUser $user;
    protected ilAccessibilitySequentialDocumentEvaluation $accessibilityEvaluation;

    public function __construct()
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $http = $DIC->http();
        $user = $DIC->user();
        $settings = $DIC->settings();
        $accessibilityEvaluation = $DIC['acc.document.evaluator'];

        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->http = $http;
        $this->user = $user;
        $this->settings = $settings;
        $this->accessibilityEvaluation = $accessibilityEvaluation;

        $this->user->setLanguage($this->lng->getLangKey());
    }


    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("showControlConcept");
        if (in_array($cmd, array("showControlConcept"))) {
            $this->$cmd();
        }
    }

    protected function printToGlobalTemplate(ilGlobalTemplateInterface $tpl)
    {
        global $DIC;
        $gtpl = $DIC['tpl'];
        $gtpl->setContent($tpl->get());
        $gtpl->printToStdout("DEFAULT", false, true);
    }

    protected function initTemplate(string $a_tmpl) : ilGlobalTemplate
    {
        $tpl = new ilGlobalTemplate("tpl.main.html", true, true);
        $template_file = $a_tmpl;
        $template_dir = 'Services/Accessibility';
        $tpl->addBlockFile('CONTENT', 'content', $template_file, $template_dir);
        return $tpl;
    }

    /**
     * Show accessibility control concept
     */
    protected function showControlConcept() : void
    {
        if (!$this->user->getId()) {
            $this->user->setId(ANONYMOUS_USER_ID);
        }

        $this->tpl->loadStandardTemplate();
        $this->tpl->setTitle($this->lng->txt("accessibility_control_concept"));

        $tpl = $this->initTemplate('tpl.view_accessibility_control_concept.html');

        $handleDocument = $this->accessibilityEvaluation->hasDocument();
        if ($handleDocument) {
            $document = $this->accessibilityEvaluation->document();
            $tpl->setVariable('ACCESSIBILITY_CONTROL_CONCEPT_CONTENT', $document->content());
        } else {
            $mails = (ilAccessibilitySupportContacts::getMailsToAddress() != "")
                ? ilLegacyFormElementsUtil::prepareFormOutput(ilAccessibilitySupportContacts::getMailsToAddress())
                : $this->settings->get("admin_email");
            $tpl->setVariable(
                'ACCESSIBILITY_CONTROL_CONCEPT_CONTENT',
                sprintf(
                    $this->lng->txt('no_accessibility_control_concept_description'),
                    'mailto:' . $mails
                )
            );
        }

        $this->printToGlobalTemplate($tpl);
    }

    public static function getFooterLink() : string
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();

        if (!ilObjAccessibilitySettings::getControlConceptStatus()) {
            return "";
        }

        return $ilCtrl->getLinkTargetByClass("ilaccessibilitycontrolconceptgui");
    }

    public static function getFooterText() : string
    {
        global $DIC;

        $lng = $DIC->language();
        return $lng->txt("accessibility_control_concept");
    }
}
