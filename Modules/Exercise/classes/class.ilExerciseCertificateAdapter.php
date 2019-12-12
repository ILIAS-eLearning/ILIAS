<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

include_once "./Services/Certificate/classes/class.ilCertificateAdapter.php";

/**
* Exercise certificate adapter
*
* @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version	$Id$
* @ingroup ModulesExercise
*/
class ilExerciseCertificateAdapter extends ilCertificateAdapter
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
        return CLIENT_WEB_DIR . "/exercise/certificates/" . $this->object->getId() . "/";
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
        $vars["RESULT_MARK"] = ilUtil::prepareFormOutput($this->lng->txt("certificate_var_result_mark_short"));
        $vars["EXERCISE_TITLE"] = ilUtil::prepareFormOutput($this->object->getTitle());
        
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
        $user_id = $params["user_id"];
        
        include_once 'Services/Tracking/classes/class.ilLPMarks.php';
        $mark = ilLPMarks::_lookupMark($user_id, $this->object->getId());
        include_once 'Modules/Exercise/classes/class.ilExerciseMembers.php';
        $status = ilExerciseMembers::_lookupStatus($this->object->getId(), $user_id);
        
        $user_data = ilObjUser::_lookupFields($user_id);
        $completion_date = $this->getUserCompletionDate($user_id);
        
        $vars = $this->getBaseVariablesForPresentation($user_data, null, $completion_date);
        $vars["RESULT_PASSED"] = ilUtil::prepareFormOutput($this->lng->txt("exc_" . $status));
        $vars["RESULT_MARK"] = ilUtil::prepareFormOutput($mark);
        $vars["EXERCISE_TITLE"] = ilUtil::prepareFormOutput($this->object->getTitle());
        
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
        $vars["RESULT_PASSED"] = $this->lng->txt("certificate_ph_passed_exercise");
        $vars["RESULT_MARK"] = $this->lng->txt("certificate_ph_mark");
        $vars["EXERCISE_TITLE"] = $this->lng->txt("certificate_ph_exercisetitle");
                
        $template = new ilTemplate("tpl.certificate_edit.html", true, true, "Modules/Exercise");
        $template->setCurrentBlock("items");
        foreach ($vars as $id => $caption) {
            $template->setVariable("ID", $id);
            $template->setVariable("TXT", $caption);
            $template->parseCurrentBlock();
        }

        $template->setVariable("PH_INTRODUCTION", $this->lng->txt("certificate_ph_introduction"));

        return $template->get();
    }
    
    public function addAdditionalFormElements(&$form, $form_fields)
    {
        $visibility = new ilRadioGroupInputGUI($this->lng->txt("certificate_visibility"), "certificate_visibility");
        $visibility->addOption(new ilRadioOption($this->lng->txt("certificate_visibility_always"), 0));
        $visibility->addOption(new ilRadioOption($this->lng->txt("certificate_visibility_passed_exercise"), 1));
        $visibility->addOption(new ilRadioOption($this->lng->txt("certificate_visibility_never"), 2));
        $visibility->setValue($form_fields["certificate_visibility"]);
        if (count($_POST)) {
            $visibility->checkInput();
        }
        $form->addItem($visibility);
    }

    public function addFormFieldsFromPOST(&$form_fields)
    {
        $form_fields["certificate_visibility"] = $_POST["certificate_visibility"];
    }

    public function addFormFieldsFromObject(&$form_fields)
    {
        $form_fields["certificate_visibility"] = $this->object->getCertificateVisibility();
    }

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
        return "exc";
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
     * This code was orignaly located in `ilObjExercise` and has been moved
     * here for `ilCertificateMigrationJob`
     *
     * @param int $objectId
     * @param int $userId
     * @return bool
     */
    public function hasUserCertificate(int $userId)
    {
        // show certificate?
        if (ilCertificate::isActive() && ilCertificate::isObjectActive($this->object->getId())) {
            $certificate_visible = $this->object->getCertificateVisibility();
            // if not never
            if ($certificate_visible != 2) {
                // if passed only
                $status = ilExerciseMembers::_lookupStatus($this->object->getId(), $userId);
                if ($certificate_visible == 1 && $status == "passed") {
                    return true;
                }

                // always (excluding notgraded)
                elseif ($certificate_visible == 0 && $status != "notgraded") {
                    return true;
                }
            }
        }

        return false;
    }
}
