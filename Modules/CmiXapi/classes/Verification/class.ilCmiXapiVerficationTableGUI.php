<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * List all completed course for current user
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiVerificationTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     *
     * @param ilObject $a_parent_obj
     * @param string $a_parent_cmd
     */
    public function __construct($a_parent_obj, $a_parent_cmd = "")
    {
        global $ilCtrl;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("passed"), "passed");
        $this->addColumn($this->lng->txt("action"), "");
        
        $this->setTitle($this->lng->txt("cmxv_create"));
        $this->setDescription($this->lng->txt("cmxv_create_info"));
        
        $this->setRowTemplate("tpl.cmix_verification_row.html", "Modules/CmiXapi");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        
        $this->getItems();
    }
    
    /**
     * Get all completed tests
     */
    protected function getItems()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $userCertificateRepository = new ilUserCertificateRepository(
            $DIC->database(),
            $DIC->logger()->root()
        );

        $certificateArray = $userCertificateRepository->fetchActiveCertificatesByTypeForPresentation(
            $DIC->user()->getId(),
            'cmix'
        );

        $data = array();

        foreach ($certificateArray as $certificate) {
            $data[] = array(
                'id' => $certificate->getUserCertificate()->getObjId(),
                'title' => $certificate->getObjectTitle(),
                'passed' => true
            );
        }

        $this->setData($data);
    }
    
    /**
     * Fill template row
     *
     * @param array $a_set
     */
    protected function fillRow($a_set)
    {
        global $ilCtrl;
        
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("PASSED", ($a_set["passed"]) ? $this->lng->txt("yes") :
            $this->lng->txt("no"));
        
        if ($a_set["passed"]) {
            $ilCtrl->setParameter($this->parent_obj, "cmix_id", $a_set["id"]);
            $action = $ilCtrl->getLinkTarget($this->parent_obj, "save");
            $this->tpl->setVariable("URL_SELECT", $action);
            $this->tpl->setVariable("TXT_SELECT", $this->lng->txt("select"));
        }
    }
}
