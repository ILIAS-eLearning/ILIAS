<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailTemplateContext.php';

/**
 * Handles scorm mail placeholders
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @package ModulesCourse
 */
class ilScormMailTemplateLPContext extends ilMailTemplateContext
{
    const ID = 'sahs_context_lp';
    
    /**
     * @return string
     */
    public function getId()
    {
        return self::ID;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule('sahs');
        
        return $lng->txt('sahs_mail_context_lp');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        global $DIC;
        $lng = $DIC['lng'];

        $lng->loadLanguageModule('sahs');

        return $lng->txt('sahs_mail_context_lp_info');
    }

    /**
     * Return an array of placeholders
     * @return array
     */
    public function getSpecificPlaceholders()
    {
        /**
         * @var $lng ilLanguage
         */
        global $DIC;
        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule('trac');
        
        // tracking settings
        include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
        $tracking = new ilObjUserTracking();
        

        $placeholders = array();
        
        
        $placeholders['sahs_title'] = array(
            'placeholder' => 'SCORM_TITLE',
            'label' => $lng->txt('obj_sahs')
        );
        
        $placeholders['sahs_status'] = array(
            'placeholder' => 'SCORM_STATUS',
            'label' => $lng->txt('trac_status')
        );

        $placeholders['sahs_mark'] = array(
            'placeholder' => 'SCORM_MARK',
            'label' => $lng->txt('trac_mark')
        );
        
        // #17969
        $lng->loadLanguageModule('content');
        $placeholders['sahs_score'] = array(
            'placeholder' => 'SCORM_SCORE',
            'label' => $lng->txt('cont_score')
        );
        
        if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS)) {
            $placeholders['sahs_time_spent'] = array(
                'placeholder' => 'SCORM_TIME_SPENT',
                'label' => $lng->txt('trac_spent_seconds')
            );
        }
        
        if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS)) {
            $placeholders['sahs_first_access'] = array(
                'placeholder' => 'SCORM_FIRST_ACCESS',
                'label' => $lng->txt('trac_first_access')
            );

            $placeholders['sahs_last_access'] = array(
                'placeholder' => 'SCORM_LAST_ACCESS',
                'label' => $lng->txt('trac_last_access')
            );
        }


        $placeholders['sahs_link'] = array(
            'placeholder' => 'SCORM_LINK',
            'label' => $lng->txt('perma_link')
        );
        
        return $placeholders;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveSpecificPlaceholder($placeholder_id, array $context_parameters, ilObjUser $recipient = null, $html_markup = false)
    {
        /**
         * @var $ilObjDataCache ilObjectDataCache
         */
        global $DIC;
        $ilObjDataCache = $DIC['ilObjDataCache'];

        if (!in_array($placeholder_id, array('sahs_title', 'sahs_link'))) {
            return '';
        }

        $obj_id = $ilObjDataCache->lookupObjId($context_parameters['ref_id']);
        
        include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
        $tracking = new ilObjUserTracking();

        switch ($placeholder_id) {
            case 'sahs_title':
                return $ilObjDataCache->lookupTitle($obj_id);
                
            case 'sahs_link':
                require_once './Services/Link/classes/class.ilLink.php';
                return ilLink::_getLink($context_parameters['ref_id'], 'sahs');
            
            case 'sahs_status':
                if ($recipient === null) {
                    return '';
                }

                include_once './Services/Tracking/classes/class.ilLPStatus.php';
                include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
                $status = ilLPStatus::_lookupStatus($obj_id, $recipient->getId());
                if (!$status) {
                    $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                }
                return ilLearningProgressBaseGUI::_getStatusText($status, $this->getLanguage());
                
            case 'sahs_mark':
                if ($recipient === null) {
                    return '';
                }

                include_once './Services/Tracking/classes/class.ilLPMarks.php';
                $mark = ilLPMarks::_lookupMark($recipient->getId(), $obj_id);
                return strlen(trim($mark)) ? $mark : '-';
                
            case 'sahs_score':
                if ($recipient === null) {
                    return '';
                }

                $scores = array();
                $obj_id = ilObject::_lookupObjId($context_parameters['ref_id']);
                include_once 'Modules/ScormAicc/classes/class.ilScormLP.php';
                $coll = ilScormLP::getInstance($obj_id)->getCollectionInstance();
                if ($coll->getItems()) {
                    include_once 'Services/Tracking/classes/class.ilTrQuery.php';
                    //changed static call into dynamic one//ukohnle
                    //foreach(ilTrQuery::getSCOsStatusForUser($recipient->getId(), $obj_id, $coll->getItems()) as $item)
                    $SCOStatusForUser = (new ilTrQuery)->getSCOsStatusForUser($recipient->getId(), $obj_id, $coll->getItems());
                    foreach ($SCOStatusForUser as $item) {
                        $scores[] = $item['title'] . ': ' . $item['score'];
                    }
                }
                return implode("\n", $scores);
                
            case 'sahs_time_spent':
                if ($recipient === null) {
                    return '';
                }

                if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS)) {
                    include_once './Services/Tracking/classes/class.ilLearningProgress.php';
                    $progress = ilLearningProgress::_getProgress($recipient->getId(), $obj_id);
                    if (isset($progress['spent_seconds'])) {
                        return ilDatePresentation::secondsToString($progress['spent_seconds'], false, $this->getLanguage());
                    }
                }
                break;
                
            case 'sahs_first_access':
                if ($recipient === null) {
                    return '';
                }

                if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS)) {
                    include_once './Services/Tracking/classes/class.ilLearningProgress.php';
                    $progress = ilLearningProgress::_getProgress($recipient->getId(), $obj_id);
                    if (isset($progress['access_time_min'])) {
                        return ilDatePresentation::formatDate(new ilDateTime($progress['access_time_min'], IL_CAL_UNIX));
                    }
                }
                break;
                
            case 'sahs_last_access':
                if ($recipient === null) {
                    return '';
                }

                if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS)) {
                    include_once './Services/Tracking/classes/class.ilLearningProgress.php';
                    $progress = ilLearningProgress::_getProgress($recipient->getId(), $obj_id);
                    if (isset($progress['access_time'])) {
                        return ilDatePresentation::formatDate(new ilDateTime($progress['access_time'], IL_CAL_UNIX));
                    }
                }
                break;
        }
        
        return '';
    }
}
