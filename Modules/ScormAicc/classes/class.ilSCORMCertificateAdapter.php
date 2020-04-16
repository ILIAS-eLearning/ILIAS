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
* SCORM certificate adapter
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesScormAicc
*/
class ilSCORMCertificateAdapter extends ilCertificateAdapter
{
    protected $object;
    
    /**
    * ilSCORMCertificateAdapter contructor
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
        return CLIENT_WEB_DIR . "/certificates/scorm/" . $this->object->getId() . "/";
    }
    
    /**
    * Returns an array containing all variables and values which can be exchanged in the certificate.
    * The values will be taken for the certificate preview.
    *
    * @return array The certificate variables
    */
    public function getCertificateVariablesForPreview()
    {
        $vars = $this->getBaseVariablesForPreview();
        $vars["SCORM_TITLE"] = ilUtil::prepareFormOutput($this->object->getTitle());
        $vars["SCORM_POINTS"] = number_format(80.7, 1, $this->lng->txt("lang_sep_decimal"), $this->lng->txt("lang_sep_thousand")) . " %";
        $vars["SCORM_POINTS_MAX"] = number_format(90, 0, $this->lng->txt("lang_sep_decimal"), $this->lng->txt("lang_sep_thousand"));
        
        $insert_tags = array();
        foreach ($vars as $id => $caption) {
            $insert_tags["[" . $id . "]"] = $caption;
        }

        include_once 'Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($this->object->getId());
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $counter = 0;
            foreach ($collection->getPossibleItems() as $item_id => $sahs_item) {
                if ($collection->isAssignedEntry($item_id)) {
                    $insert_tags['[SCO_T_' . $counter . ']'] = $sahs_item['title'];
                    $insert_tags['[SCO_P_' . $counter . ']'] = number_format(30.3, 1, $this->lng->txt("lang_sep_decimal"), $this->lng->txt("lang_sep_thousand"));
                    $insert_tags['[SCO_PM_' . $counter . ']'] = number_format(90.9, 1, $this->lng->txt("lang_sep_decimal"), $this->lng->txt("lang_sep_thousand"));
                    $insert_tags['[SCO_PP_' . $counter . ']'] = number_format(33.3333, 1, $this->lng->txt("lang_sep_decimal"), $this->lng->txt("lang_sep_thousand")) . " %";
                    $counter++;
                }
            }
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
        $this->lng->loadLanguageModule('certificate');
                
        $points = $this->object->getPointsInPercent();
        $txtPoints = "";
        if (is_null($points)) {
            $txtPoints = $this->lng->txt("certificate_points_notavailable");
        } else {
            $txtPoints = number_format($points, 1, $this->lng->txt("lang_sep_decimal"), $this->lng->txt("lang_sep_thousand")) . " %";
        }
        
        $max_points = $this->object->getMaxPoints();
        $txtMaxPoints = '';
        if (is_null($max_points)) {
            $txtMaxPoints = $this->lng->txt("certificate_points_notavailable");
        } else {
            if ($max_points != floor($max_points)) {
                $txtMaxPoints = number_format($max_points, 1, $this->lng->txt("lang_sep_decimal"), $this->lng->txt("lang_sep_thousand"));
            } else {
                $txtMaxPoints = $max_points;
            }
        }
        
        $user_data = $params["user_data"];
        $completion_date = $this->getUserCompletionDate($user_data["usr_id"]);
        
        $vars = $this->getBaseVariablesForPresentation($user_data, $params["last_access"], $completion_date);
        $vars["SCORM_TITLE"] = ilUtil::prepareFormOutput($this->object->getTitle());
        $vars["SCORM_POINTS"] = $txtPoints;
        $vars["SCORM_POINTS_MAX"] = $txtMaxPoints;
        
        foreach ($vars as $id => $caption) {
            $insert_tags["[" . $id . "]"] = $caption;
        }
        
        include_once 'Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($this->object->getId());
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $counter = 0;
            foreach ($collection->getPossibleItems() as $item_id => $sahs_item) {
                if ($collection->isAssignedEntry($item_id)) {
                    $insert_tags['[SCO_T_' . $counter . ']'] = $sahs_item['title'];//." getId=".$this->object->getId()." item_id=".$item_id." user_id=".$ilUser->getId()
                    $a_scores = $collection->getScoresForUserAndCP_Node_Id($item_id, $GLOBALS['DIC']['ilUser']->getId());
                    if ($a_scores["raw"] == null) {
                        $insert_tags['[SCO_P_' . $counter . ']'] = $this->lng->txt("certificate_points_notavailable");
                    } else {
                        $insert_tags['[SCO_P_' . $counter . ']'] = number_format($a_scores["raw"], 1, $this->lng->txt("lang_sep_decimal"), $this->lng->txt("lang_sep_thousand"));
                    }
                    if ($a_scores["max"] == null) {
                        $insert_tags['[SCO_PM_' . $counter . ']'] = $this->lng->txt("certificate_points_notavailable");
                    } else {
                        $insert_tags['[SCO_PM_' . $counter . ']'] = number_format($a_scores["max"], 1, $this->lng->txt("lang_sep_decimal"), $this->lng->txt("lang_sep_thousand"));
                    }
                    if ($a_scores["scaled"] == null) {
                        $insert_tags['[SCO_PP_' . $counter . ']'] = $this->lng->txt("certificate_points_notavailable");
                    } else {
                        $insert_tags['[SCO_PP_' . $counter . ']'] = number_format(($a_scores["scaled"] * 100), 1, $this->lng->txt("lang_sep_decimal"), $this->lng->txt("lang_sep_thousand")) . " %";
                    }
                    $counter++;
                }
            }
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
        $vars = $this->getBaseVariablesDescription();
        $vars["SCORM_TITLE"] = $this->lng->txt("certificate_ph_scormtitle");
        $vars["SCORM_POINTS"] = $this->lng->txt("certificate_ph_scormpoints");
        $vars["SCORM_POINTS_MAX"] = $this->lng->txt("certificate_ph_scormmaxpoints");
        
        $template = new ilTemplate("tpl.certificate_edit.html", true, true, "Modules/ScormAicc");
        $template->setCurrentBlock("items");
        foreach ($vars as $id => $caption) {
            $template->setVariable("ID", $id);
            $template->setVariable("TXT", $caption);
            $template->parseCurrentBlock();
        }

        $template->setVariable("PH_INTRODUCTION", $this->lng->txt("certificate_ph_introduction"));

        include_once 'Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($this->object->getId());
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $items = $collection->getPossibleItems();
        }

        if (!$items) {
            $template->setCurrentBlock('NO_SCO');
            $template->setVariable('PH_NO_SCO', $this->lng->txt('certificate_ph_no_sco'));
            $template->parseCurrentBlock();
        } else {
            $template->setCurrentBlock('SCOS');
            $template->setVariable('PH_SCOS', $this->lng->txt('certificate_ph_scos'));
            $template->parseCurrentBlock();
            $template->setCurrentBlock('SCO_HEADER');
            $template->setVariable('PH_TITLE_SCO', $this->lng->txt('certificate_ph_title_sco'));
            //$template->setVariable('PH_PH',$lng->txt('certificate_ph_ph'));
            $template->setVariable('PH_SCO_TITLE', $this->lng->txt('certificate_ph_sco_title'));
            $template->setVariable('PH_SCO_POINTS_RAW', $this->lng->txt('certificate_ph_sco_points_raw'));
            $template->setVariable('PH_SCO_POINTS_MAX', $this->lng->txt('certificate_ph_sco_points_max'));
            $template->setVariable('PH_SCO_POINTS_SCALED', $this->lng->txt('certificate_ph_sco_points_scaled'));
            $template->parseCurrentBlock();
        }

        if ($collection) {
            $counter = 0;
            foreach ($items as $item_id => $sahs_item) {
                if ($collection->isAssignedEntry($item_id)) {
                    $template->setCurrentBlock("SCO");
                    $template->setVariable('SCO_TITLE', $sahs_item['title']);
                    $template->setVariable('PH_SCO_TITLE', '[SCO_T_' . $counter . ']');
                    $template->setVariable('PH_SCO_POINTS_RAW', '[SCO_P_' . $counter . ']');
                    $template->setVariable('PH_SCO_POINTS_MAX', '[SCO_PM_' . $counter . ']');
                    $template->setVariable('PH_SCO_POINTS_SCALED', '[SCO_PP_' . $counter . ']');
                    $template->parseCurrentBlock();
                    $counter++;
                }
            }
        }

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
        $short_name = new ilTextInputGUI($this->lng->txt("certificate_short_name"), "short_name");
        $short_name->setRequired(true);
        require_once "./Services/Utilities/classes/class.ilStr.php";
        $short_name->setValue(strlen($form_fields["short_name"]) ? $form_fields["short_name"] : ilStr::subStr($this->object->getTitle(), 0, 30));
        $short_name->setSize(30);
        if (strlen($form_fields["short_name"])) {
            $short_name->setInfo(str_replace("[SHORT_TITLE]", $form_fields["short_name"], $this->lng->txt("certificate_short_name_description")));
        } else {
            $short_name->setInfo($this->lng->txt("certificate_short_name_description"));
        }
        if (count($_POST)) {
            $short_name->checkInput();
        }
        $form->addItem($short_name);
    }

    /**
     * @inheritdoc
     */
    public function hasAdditionalFormElements()
    {
        return true;
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
        $form_fields["certificate_enabled_scorm"] = $_POST["certificate_enabled_scorm"];
        $form_fields["short_name"] = $_POST["short_name"];
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
        $scormSetting = new ilSetting("scorm");
        $form_fields["certificate_enabled_scorm"] = $scormSetting->get("certificate_" . $this->object->getId());
        $form_fields["short_name"] = $scormSetting->get("certificate_short_name_" . $this->object->getId());
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
        $scormSetting = new ilSetting("scorm");
        $scormSetting->set("certificate_" . $this->object->getId(), $form_fields["certificate_enabled_scorm"]);
        $scormSetting->set("certificate_short_name_" . $this->object->getId(), $form_fields["short_name"]);
    }

    /**
    * Returns the adapter type
    * This value will be used to generate file names for the certificates
    *
    * @return string A string value to represent the adapter type
    */
    public function getAdapterType()
    {
        return "scorm";
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
    * Set the name of the certificate file
    * This method will be called when the certificate will be generated
    *
    * @return string The certificate file name
    */
    public function getCertificateFilename($params = array())
    {
        $basename = parent::getCertificateFilename($params);
        
        $user_data = $params["user_data"];
        if (!is_array($user_data)) {
            global $DIC;
            $ilSetting = $DIC['ilSetting'];
            $scormSetting = new ilSetting("scorm");
            $short_title = $scormSetting->get("certificate_short_name_" . $this->object->getId());
            return strftime("%y%m%d", time()) . "_" . $this->lng->txt("certificate_var_user_lastname") . "_" . $short_title . "_" . $basename;
        } else {
            return strftime("%y%m%d", time()) . "_" . $user_data["lastname"] . "_" . $params["short_title"] . "_.$basename";
        }
    }

    /**
    * Is called when the certificate is deleted
    * Add some adapter specific code if more work has to be done when the
    * certificate file was deleted
    */
    public function deleteCertificate()
    {
        $scormSetting = new ilSetting("scorm");
        $scormSetting->delete("certificate_" . $this->object->getId());
    }
    
    /**
     * Get user id for params
     *
     * @param
     * @return
     */
    public function getUserIdForParams($a_params)
    {
        return $a_params["user_data"]["usr_id"];
    }
}
