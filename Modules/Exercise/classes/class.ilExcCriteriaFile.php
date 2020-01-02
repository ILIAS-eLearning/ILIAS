<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExcCriteria.php";

/**
 * Class ilExcCriteriaFile
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExcCriteriaFile extends ilExcCriteria
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
    }

    public function getType()
    {
        return "file";
    }
        
    protected function initStorage()
    {
        include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
        $storage = new ilFSStorageExercise($this->ass->getExerciseId(), $this->ass->getId());
        return $storage->getPeerReviewUploadPath($this->peer_id, $this->giver_id, $this->getId());
    }
    
    public function getFiles()
    {
        $path = $this->initStorage();
        return (array) glob($path . "*.*");
    }
        
    public function resetReview()
    {
        include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
        $storage = new ilFSStorageExercise($this->ass->getExerciseId(), $this->ass->getId());
        $storage->deleteDirectory($storage->getPeerReviewUploadPath($this->peer_id, $this->giver_id, $this->getId()));
    }
    
    
    // PEER REVIEW
    
    public function addToPeerReviewForm($a_value = null)
    {
        $existing = array();
        foreach ($this->getFiles() as $file) {
            $existing[] = basename($file);
        }
        
        $files = new ilFileInputGUI($this->getTitle(), "prccc_file_" . $this->getId());
        $files->setInfo($this->getDescription());
        $files->setRequired($this->isRequired());
        $files->setValue(implode("<br />", $existing));
        $files->setALlowDeletion(true);
        $this->form->addItem($files);
    }
    
    public function importFromPeerReviewForm()
    {
        $path = $this->initStorage();
        
        if ($this->form->getItemByPostVar("prccc_file_" . $this->getId())->getDeletionFlag()) {
            ilUtil::delDir($path);
            $this->form->getItemByPostVar("prccc_file_" . $this->getId())->setValue(null);
        }
        
        $incoming = $_FILES["prccc_file_" . $this->getId()];
        if ($incoming["tmp_name"]) {
            $org_name = basename($incoming["name"]);
            
            ilUtil::moveUploadedFile(
                $incoming["tmp_name"],
                $org_name,
                $path . $org_name,
                false
            );
        }
    }
    
    public function hasValue($a_value)
    {
        return (bool) sizeof($this->getFiles());
    }
        
    public function validate($a_value)
    {
        $lng = $this->lng;
        
        // because of deletion flag we have to also check ourselves
        if ($this->isRequired()) {
            if (!$this->hasValue($a_value)) {
                if ($this->form) {
                    $this->form->getItemByPostVar("prccc_file_" . $this->getId())->setAlert($lng->txt("msg_input_is_required"));
                }
                return false;
            }
        }
        return true;
    }
    
    public function getFileByHash()
    {
        $hash = trim($_GET["fuf"]);
        if ($hash) {
            foreach ($this->getFiles() as $file) {
                if (md5($file) == $hash) {
                    return $file;
                }
            }
        }
    }
    
    public function getHTML($a_value)
    {
        $ilCtrl = $this->ctrl;
        
        $crit_id = $this->getId()
            ? $this->getId()
            : "file";
        $ilCtrl->setParameterByClass("ilExPeerReviewGUI", "fu", $this->giver_id . "__" . $this->peer_id . "__" . $crit_id);
        
        $files = array();
        foreach ($this->getFiles() as $file) {
            $ilCtrl->setParameterByClass("ilExPeerReviewGUI", "fuf", md5($file));
            $dl = $ilCtrl->getLinkTargetByClass("ilExPeerReviewGUI", "downloadPeerReview");
            $ilCtrl->setParameterByClass("ilExPeerReviewGUI", "fuf", "");
            
            $files[] = '<a href="' . $dl . '">' . basename($file) . '</a>';
        }
        
        $ilCtrl->setParameterByClass("ilExPeerReviewGUI", "fu", "");
        
        return implode("<br />", $files);
    }
}
