<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailTemplateContext.php';

/**
 * Handles course mail placeholders
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @package ModulesCourse
 */
class ilCourseMailTemplateTutorContext extends ilMailTemplateContext
{
    const ID = 'crs_context_tutor_manual';
    
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
        
        $lng->loadLanguageModule('crs');
        
        return $lng->txt('crs_mail_context_tutor_title');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        global $DIC;

        $lng = $DIC['lng'];

        $lng->loadLanguageModule('crs');

        return $lng->txt('crs_mail_context_tutor_info');
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

        $lng->loadLanguageModule('crs');
        $lng->loadLanguageModule('trac');
        
        // tracking settings
        include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
        $tracking = new ilObjUserTracking();
        

        $placeholders = array();
        
        
        $placeholders['crs_title'] = array(
            'placeholder'	=> 'COURSE_TITLE',
            'label'			=> $lng->txt('crs_title')
        );
        
        $placeholders['crs_status'] = array(
            'placeholder'	=> 'COURSE_STATUS',
            'label'			=> $lng->txt('trac_status')
        );

        $placeholders['crs_mark'] = array(
            'placeholder'	=> 'COURSE_MARK',
            'label'			=> $lng->txt('trac_mark')
        );
        
        if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS)) {
            $placeholders['crs_time_spent'] = array(
                'placeholder'	=> 'COURSE_TIME_SPENT',
                'label'			=> $lng->txt('trac_spent_seconds')
            );
        }
        
        if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS)) {
            $placeholders['crs_first_access'] = array(
                'placeholder'	=> 'COURSE_FIRST_ACCESS',
                'label'			=> $lng->txt('trac_first_access')
            );

            $placeholders['crs_last_access'] = array(
                'placeholder'	=> 'COURSE_LAST_ACCESS',
                'label'			=> $lng->txt('trac_last_access')
            );
        }


        $placeholders['crs_link'] = array(
            'placeholder'	=> 'COURSE_LINK',
            'label'			=> $lng->txt('crs_mail_permanent_link')
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

        if (!in_array($placeholder_id, array(
            'crs_title',
            'crs_link',
            'crs_status',
            'crs_mark',
            'crs_time_spent',
            'crs_first_access',
            'crs_last_access'))) {
            return '';
        }

        $obj_id = $ilObjDataCache->lookupObjId($context_parameters['ref_id']);

        include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
        $tracking = new ilObjUserTracking();
        
        $this->getLanguage()->loadLanguageModule('trac');
        $this->getLanguage()->loadLanguageModule('crs');
        
        switch ($placeholder_id) {
            case 'crs_title':
                return $ilObjDataCache->lookupTitle($obj_id);
                
            case 'crs_link':
                require_once './Services/Link/classes/class.ilLink.php';
                return ilLink::_getLink($context_parameters['ref_id'], 'crs');
            
            case 'crs_status':
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
                
            case 'crs_mark':
                if ($recipient === null) {
                    return '';
                }

                include_once './Services/Tracking/classes/class.ilLPMarks.php';
                $mark = ilLPMarks::_lookupMark($recipient->getId(), $obj_id);
                return strlen(trim($mark)) ? $mark : '-';
                
            case 'crs_time_spent':
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
                
            case 'crs_first_access':
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
                
            case 'crs_last_access':
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
