<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilityControlConceptGUI
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilAccessibilityControlConceptGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;

    protected $user;

    protected $accessibilityEvaluation;

    /**
     * Constructor
     */
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


    /**
     * Execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("showControlConcept");
        if (in_array($cmd, array("showControlConcept"))) {
            $this->$cmd();
        }
    }

    /**
     * @param $tpl
     */
    protected function printToGlobalTemplate($tpl)
    {
        global $DIC;
        $gtpl = $DIC['tpl'];
        $gtpl->setContent($tpl->get());
        $gtpl->printToStdout("DEFAULT", false, true);
    }

    /**
     * @param $a_tmpl
     * @return ilGlobalTemplate
     */
    protected function initTemplate($a_tmpl)
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
    protected function showControlConcept()
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
                ? ilUtil::prepareFormOutput(ilAccessibilitySupportContacts::getMailsToAddress())
                : $this->settings->get("admin_email");
            $tpl->setVariable(
                'ACCESSIBILITY_CONTROL_CONCEPT_CONTENT',
                sprintf(
                    $this->lng->txt('no_accessibility_control_concept_description'),
                    'mailto:' . $mails
                )
            );
        }

        self::printToGlobalTemplate($tpl);
    }

    /**
     * Get footer link
     *
     * @return string footer link
     */
    public static function getFooterLink()
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();

        if (!ilObjAccessibilitySettings::getControlConceptStatus()) {
            return "";
        }

        return $ilCtrl->getLinkTargetByClass("ilaccessibilitycontrolconceptgui");
    }

    /**
     * Get footer text
     *
     * @return string footer text
     */
    public static function getFooterText()
    {
        global $DIC;

        $lng = $DIC->language();
        return $lng->txt("accessibility_control_concept");
    }
}
