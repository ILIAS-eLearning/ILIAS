<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Adapter class to provide certificate data for the certificate generator
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup Services
*/
abstract class ilCertificateAdapter
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * ilCertificateAdapter constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('certificate');
    }

    /**
    * Returns the certificate path (with a trailing path separator)
    *
    * @return string The certificate path
    */
    abstract public function getCertificatePath();
    
    /**
    * Returns an array containing all variables and values which can be exchanged in the certificate.
    * The values will be taken for the certificate preview.
    *
    * @return array The certificate variables
    */
    abstract public function getCertificateVariablesForPreview();

    /**
    * Returns an array containing all variables and values which can be exchanged in the certificate
    * The values should be calculated from real data. The $params parameter array should contain all
    * necessary information to calculate the values.
    *
    * @param array $params An array of parameters to calculate the certificate parameter values
    * @return array The certificate variables
    */
    abstract public function getCertificateVariablesForPresentation($params = array());

    /**
    * Returns a description of the available certificate parameters. The description will be shown at
    * the bottom of the certificate editor text area.
    *
    * @return string The certificate parameters description
    */
    abstract public function getCertificateVariablesDescription();

    /**
    * Returns the adapter type
    * This value will be used to generate file names for the certificates
    *
    * @return string A string value to represent the adapter type
    */
    abstract public function getAdapterType();

    /**
    * Returns a certificate ID
    * This value will be used to generate unique file names for the certificates
    *
    * @return mixed A unique ID which represents a certificate
    */
    abstract public function getCertificateID();
    
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
    }

    /**
     * @return bool
     */
    public function hasAdditionalFormElements()
    {
        return false;
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
    }
    
    /**
    * Is called when the certificate is deleted
    * Add some adapter specific code if more work has to be done when the
    * certificate file was deleted
    */
    public function deleteCertificate()
    {
    }
    
    /**
    * Set the name of the certificate file
    * This method will be called when the certificate will be generated
    *
    * @return string The certificate file name
    */
    public function getCertificateFilename($params = array())
    {
        $this->lng->loadLanguageModule("certificate");

        return $this->lng->txt("certificate_file_basename") . ".pdf";
    }
    
    /**
     * Get variable descriptions
     *
     * @param bool $a_enable_last_access
     * @param bool $a_enable_completion_date
     * @return array
     */
    protected function getBaseVariablesDescription($a_enable_last_access = true, $a_enable_completion_date = true)
    {
        $vars = array(
            "USER_LOGIN" => $this->lng->txt("certificate_ph_login"),
            "USER_FULLNAME" => $this->lng->txt("certificate_ph_fullname"),
            "USER_FIRSTNAME" => $this->lng->txt("certificate_ph_firstname"),
            "USER_LASTNAME" => $this->lng->txt("certificate_ph_lastname"),
            "USER_TITLE" => $this->lng->txt("certificate_ph_title"),
            "USER_SALUTATION" => $this->lng->txt("certificate_ph_salutation"),
            "USER_BIRTHDAY" => $this->lng->txt("certificate_ph_birthday"),
            "USER_INSTITUTION" => $this->lng->txt("certificate_ph_institution"),
            "USER_DEPARTMENT" => $this->lng->txt("certificate_ph_department"),
            "USER_STREET" => $this->lng->txt("certificate_ph_street"),
            "USER_CITY" => $this->lng->txt("certificate_ph_city"),
            "USER_ZIPCODE" => $this->lng->txt("certificate_ph_zipcode"),
            "USER_COUNTRY" => $this->lng->txt("certificate_ph_country"),
            "USER_MATRICULATION" => $this->lng->txt("certificate_ph_matriculation")
        );
        
        if ($a_enable_last_access) {
            $vars["USER_LASTACCESS"] = $this->lng->txt("certificate_ph_lastaccess");
        }
        
        $vars["DATE"] = $this->lng->txt("certificate_ph_date");
        $vars["DATETIME"] = $this->lng->txt("certificate_ph_datetime");
        
        if ($a_enable_completion_date) {
            $vars["DATE_COMPLETED"] = $this->lng->txt("certificate_ph_date_completed");
            $vars["DATETIME_COMPLETED"] = $this->lng->txt("certificate_ph_datetime_completed");
        }
        
        return $vars;
    }
    
    /**
     * Get variable dummys
     *
     * @param bool $a_enable_last_access
     * @param bool $a_enable_completion_date
     * @return array
     */
    protected function getBaseVariablesForPreview($a_enable_last_access = true, $a_enable_completion_date = true)
    {
        $old = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);
        
        $vars = array(
            "USER_LOGIN" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_login")),
            "USER_FULLNAME" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_fullname")),
            "USER_FIRSTNAME" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_firstname")),
            "USER_LASTNAME" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_lastname")),
            "USER_TITLE" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_title")),
            "USER_SALUTATION" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_salutation")),
            "USER_BIRTHDAY" => ilDatePresentation::formatDate(new ilDate($this->lng->txt("certificate_var_user_birthday"), IL_CAL_DATE)),
            "USER_INSTITUTION" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_institution")),
            "USER_DEPARTMENT" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_department")),
            "USER_STREET" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_street")),
            "USER_CITY" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_city")),
            "USER_ZIPCODE" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_zipcode")),
            "USER_COUNTRY" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_country")),
            "USER_MATRICULATION" => ilUtil::prepareFormOutput($this->lng->txt("certificate_var_user_matriculation"))
        );
        
        if ($a_enable_last_access) {
            $vars["USER_LASTACCESS"] = ilDatePresentation::formatDate(new ilDateTime(time() - (24 * 60 * 60 * 5), IL_CAL_UNIX));
        };
                
        $vars["DATE"] = ilDatePresentation::formatDate(new ilDate(time(), IL_CAL_UNIX));
        $vars["DATETIME"] = ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX));
        
        if ($a_enable_completion_date) {
            $vars["DATE_COMPLETED"] = ilDatePresentation::formatDate(new ilDate(time() - (24 * 60 * 60 * 5), IL_CAL_UNIX));
            $vars["DATETIME_COMPLETED"] = ilDatePresentation::formatDate(new ilDateTime(time() - (24 * 60 * 60 * 5), IL_CAL_UNIX));
        }
        
        ilDatePresentation::setUseRelativeDates($old);
        
        return $vars;
    }
    

    /**
     * Get variable values
     *
     * @param array $a_user_data
     * @param datetime $a_last_access
     * @param datetime $a_completion_date
     * @return array
     */
    protected function getBaseVariablesForPresentation($a_user_data, $a_last_access = null, $a_completion_date = false)
    {
        $old = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);
        
        $salutation = "";
        if (strlen($a_user_data["gender"])) {
            $salutation = $this->lng->txt("salutation_" . $a_user_data["gender"]);
        }
        
        $birthday = "";
        if ($a_user_data["birthday"]) {
            $birthday = ilDatePresentation::formatDate(new ilDate($a_user_data["birthday"], IL_CAL_DATE));
        }
        
        $country = $a_user_data["sel_country"];
        if ($country) {
            $this->lng->loadLanguageModule("meta");
            $country = $this->lng->txt("meta_c_" . $country);
        } else {
            $country = $a_user_data["country"];
        }
        
        $vars = array(
            "USER_LOGIN" => ilUtil::prepareFormOutput(trim($a_user_data["login"])),
            "USER_FULLNAME" => ilUtil::prepareFormOutput(trim($a_user_data["title"] . " " . $a_user_data["firstname"] . " " . $a_user_data["lastname"])),
            "USER_FIRSTNAME" => ilUtil::prepareFormOutput($a_user_data["firstname"]),
            "USER_LASTNAME" => ilUtil::prepareFormOutput($a_user_data["lastname"]),
            "USER_TITLE" => ilUtil::prepareFormOutput($a_user_data["title"]),
            "USER_SALUTATION" => ilUtil::prepareFormOutput($salutation),
            "USER_BIRTHDAY" => ilUtil::prepareFormOutput($birthday),
            "USER_INSTITUTION" => ilUtil::prepareFormOutput($a_user_data["institution"]),
            "USER_DEPARTMENT" => ilUtil::prepareFormOutput($a_user_data["department"]),
            "USER_STREET" => ilUtil::prepareFormOutput($a_user_data["street"]),
            "USER_CITY" => ilUtil::prepareFormOutput($a_user_data["city"]),
            "USER_ZIPCODE" => ilUtil::prepareFormOutput($a_user_data["zipcode"]),
            "USER_COUNTRY" => ilUtil::prepareFormOutput($country),
            "USER_MATRICULATION" => ilUtil::prepareFormOutput($a_user_data["matriculation"])
        );
        
        if ($a_last_access) {
            $vars["USER_LASTACCESS"] = ilDatePresentation::formatDate(new ilDateTime($a_last_access, IL_CAL_DATETIME));
        }
        
        $vars["DATE"] = ilDatePresentation::formatDate(new ilDate(time(), IL_CAL_UNIX));
        $vars["DATETIME"] = ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX));
        
        
        if ($a_completion_date) {
            $vars["DATE_COMPLETED"] = ilDatePresentation::formatDate(new ilDate($a_completion_date, IL_CAL_DATETIME));

            $dateTime = new ilDateTime($a_completion_date, IL_CAL_DATETIME);
            $vars["DATETIME_COMPLETED"] = ilDatePresentation::formatDate($dateTime);
            $vars["DATETIME_COMPLETED_UNIX"] = $dateTime->get(IL_CAL_UNIX);
        }
        
        ilDatePresentation::setUseRelativeDates($old);
        
        return $vars;
    }
    
    /**
     * Get completion for user
     *
     * @param int $a_user_id
     * @param int $a_object_id
     * @return string datetime
     */
    protected function getUserCompletionDate($a_user_id, $a_object_id = null)
    {
        if (!$a_object_id) {
            $a_object_id = $this->object->getId();
        }
        include_once "Services/Tracking/classes/class.ilLPStatus.php";
        return ilLPStatus::_lookupStatusChanged($a_object_id, $a_user_id);
    }
    
    /**
     * Get user id for params
     *
     * @param
     * @return
     */
    public function getUserIdForParams($a_params)
    {
        return $a_params["user_id"];
    }
}
