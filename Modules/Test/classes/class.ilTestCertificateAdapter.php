<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Certificate/classes/class.ilCertificateAdapter.php";

/**
* Test certificate adapter
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTest
*/
class ilTestCertificateAdapter extends ilCertificateAdapter
{
    protected $object;
    
    /**
    * ilTestCertificateAdapter contructor
    *
    * @param object $object A reference to a test object
    */
    public function __construct($object)
    {
        $this->object = $object;
        parent::__construct();
    }

    /**
    * Returns the certificate path (with a trailing path separator)
    *
    * @return string The certificate path
    */
    public function getCertificatePath()
    {
        return CLIENT_WEB_DIR . "/assessment/certificates/" . $this->object->getId() . "/";
    }
    
    /**
    * Returns an array containing all variables and values which can be exchanged in the certificate.
    * The values will be taken for the certificate preview.
    *
    * @return array The certificate variables
    */
    public function getCertificateVariablesForPreview()
    {
        $vars = $this->getBaseVariablesForPreview(false);
        $vars["RESULT_PASSED"] = ilUtil::prepareFormOutput($this->lng->txt("certificate_var_result_passed"));
        $vars["RESULT_POINTS"] = ilUtil::prepareFormOutput($this->lng->txt("certificate_var_result_points"));
        $vars["RESULT_PERCENT"] = ilUtil::prepareFormOutput($this->lng->txt("certificate_var_result_percent"));
        $vars["MAX_POINTS"] = ilUtil::prepareFormOutput($this->lng->txt("certificate_var_max_points"));
        $vars["RESULT_MARK_SHORT"] = ilUtil::prepareFormOutput($this->lng->txt("certificate_var_result_mark_short"));
        $vars["RESULT_MARK_LONG"] = ilUtil::prepareFormOutput($this->lng->txt("certificate_var_result_mark_long"));
        $vars["TEST_TITLE"] = ilUtil::prepareFormOutput($this->object->getTitle());
        
        $insert_tags = array();
        foreach ($vars as $id => $caption) {
            $insert_tags["[" . $id . "]"] = $caption;
        }
        
        return $insert_tags;
    }

    /**
    * Returns an array containing all variables and values which can be exchanged in the certificate
    * The values should be calculated from real data. The $params parameter array should contain all
    * necessary information to calculate the values.
    *
    * @param array $params An array of parameters to calculate the certificate parameter values
    * @return array The certificate variables
    */
    public function getCertificateVariablesForPresentation($params = array())
    {
        $active_id = $params["active_id"];
        $pass = $params["pass"];
        $userfilter = array_key_exists("userfilter", $params) ? $params["userfilter"] : "";
        $passedonly = array_key_exists("passedonly", $params) ? $params["passedonly"] : false;
        if (strlen($pass)) {
            $result_array =&$this->object->getTestResult($active_id, $pass);
        } else {
            $result_array =&$this->object->getTestResult($active_id);
        }
        if (($passedonly) && ($result_array["test"]["passed"] == false)) {
            return "";
        }
        $passed = $result_array["test"]["passed"] ? $this->lng->txt("certificate_passed") : $this->lng->txt("certificate_failed");
        if (!$result_array["test"]["total_max_points"]) {
            $percentage = 0;
        } else {
            $percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
        }
        $mark_obj = $this->object->mark_schema->getMatchingMark($percentage);
        $user_id = $this->object->_getUserIdFromActiveId($active_id);
        include_once './Services/User/classes/class.ilObjUser.php';
        $user_data = ilObjUser::_lookupFields($user_id);
        
        if (strlen($userfilter)) {
            if (!@preg_match("/$userfilter/i", $user_data["lastname"] . ", " . $user_data["firstname"] . " " . $user_data["title"])) {
                return "";
            }
        }
        
        if (ilObjUserTracking::_enabledLearningProgress() && $user_data["usr_id"] > 0) {
            $completion_date = $this->getUserCompletionDate($user_data["usr_id"]);
        } else {
            $dt = new ilDateTime($result_array['test']['result_tstamp'], IL_CAL_UNIX);
            $completion_date = $dt->get(IL_CAL_DATETIME);
        }

        $vars = $this->getBaseVariablesForPresentation($user_data, null, $completion_date);
        $vars["RESULT_PASSED"] = ilUtil::prepareFormOutput($passed);
        $vars["RESULT_POINTS"] = ilUtil::prepareFormOutput($result_array["test"]["total_reached_points"]);
        $vars["RESULT_PERCENT"] = sprintf("%2.2f", $percentage) . "%";
        $vars["MAX_POINTS"] = ilUtil::prepareFormOutput($result_array["test"]["total_max_points"]);
        $vars["RESULT_MARK_SHORT"] = ilUtil::prepareFormOutput($mark_obj->getShortName());
        $vars["RESULT_MARK_LONG"] = ilUtil::prepareFormOutput($mark_obj->getOfficialName());
        $vars["TEST_TITLE"] = ilUtil::prepareFormOutput($this->object->getTitle());
        
        foreach ($vars as $id => $caption) {
            $insert_tags["[" . $id . "]"] = $caption;
        }

        return $insert_tags;
    }
    
    /**
    * Returns a description of the available certificate parameters. The description will be shown at
    * the bottom of the certificate editor text area.
    *
    * @return string The certificate parameters description
    */
    public function getCertificateVariablesDescription()
    {
        $vars = $this->getBaseVariablesDescription(false);
        $vars["RESULT_PASSED"] = $this->lng->txt("certificate_ph_passed");
        $vars["RESULT_POINTS"] = $this->lng->txt("certificate_ph_resultpoints");
        $vars["RESULT_PERCENT"] = $this->lng->txt("certificate_ph_resultpercent");
        $vars["MAX_POINTS"] = $this->lng->txt("certificate_ph_maxpoints");
        $vars["RESULT_MARK_SHORT"] = $this->lng->txt("certificate_ph_markshort");
        $vars["RESULT_MARK_LONG"] = $this->lng->txt("certificate_ph_marklong");
        $vars["TEST_TITLE"] = $this->lng->txt("certificate_ph_testtitle");
                
        $template = new ilTemplate("tpl.il_as_tst_certificate_edit.html", true, true, "Modules/Test");
        $template->setCurrentBlock("items");
        foreach ($vars as $id => $caption) {
            $template->setVariable("ID", $id);
            $template->setVariable("TXT", $caption);
            $template->parseCurrentBlock();
        }

        
        $template->setVariable("PH_INTRODUCTION", $this->lng->txt("certificate_ph_introduction"));

        return $template->get();
    }

    /**
    * Allows to add additional form fields to the certificate editor form
    * This method will be called when the certificate editor form will built
    * using the ilPropertyFormGUI class. Additional fields will be added at the
    * bottom of the form.
    *
    * @param object $form An ilPropertyFormGUI instance
    * @param array $form_fields An array containing the form values. The array keys are the names of the form fields
    */
    public function addAdditionalFormElements(&$form, $form_fields)
    {
        $visibility = new ilRadioGroupInputGUI($this->lng->txt("certificate_visibility"), "certificate_visibility");
        $visibility->addOption(new ilRadioOption($this->lng->txt("certificate_visibility_always"), 0));
        $visibility->addOption(new ilRadioOption($this->lng->txt("certificate_visibility_passed"), 1));
        $visibility->addOption(new ilRadioOption($this->lng->txt("certificate_visibility_never"), 2));
        $visibility->setInfo($this->lng->txt("certificate_visibility_introduction"));
        $visibility->setValue($form_fields["certificate_visibility"]);
        if (count($_POST)) {
            $visibility->checkInput();
        }
        $form->addItem($visibility);
    }

    /**
    * Allows to add additional form values to the array of form values evaluating a
    * HTTP POST action.
    * This method will be called when the certificate editor form will be saved using
    * the form save button.
    *
    * @param array $form_fields A reference to the array of form values
    */
    public function addFormFieldsFromPOST(&$form_fields)
    {
        $form_fields["certificate_visibility"] = $_POST["certificate_visibility"];
    }

    /**
    * Allows to add additional form values to the array of form values evaluating the
    * associated adapter class if one exists
    * This method will be called when the certificate editor form will be shown and the
    * content of the form has to be retrieved from wherever the form values are saved.
    *
    * @param array $form_fields A reference to the array of form values
    */
    public function addFormFieldsFromObject(&$form_fields)
    {
        $form_fields["certificate_visibility"] = $this->object->getCertificateVisibility();
    }
    
    /**
    * Allows to save additional adapter form fields
    * This method will be called when the certificate editor form is complete and the
    * form values will be saved.
    *
    * @param array $form_fields A reference to the array of form values
    */
    public function saveFormFields(&$form_fields)
    {
        $this->object->saveCertificateVisibility($form_fields["certificate_visibility"]);
    }

    /**
    * Returns the adapter type
    * This value will be used to generate file names for the certificates
    *
    * @return string A string value to represent the adapter type
    */
    public function getAdapterType()
    {
        return "test";
    }

    /**
    * Returns a certificate ID
    * This value will be used to generate unique file names for the certificates
    *
    * @return mixed A unique ID which represents a certificate
    */
    public function getCertificateID()
    {
        return $this->object->getId();
    }
    
    /**
     * Get user id for params
     *
     * @param
     * @return
     */
    public function getUserIdForParams($a_params)
    {
        return $this->object->_getUserIdFromActiveId($a_params["active_id"]);
    }
}
