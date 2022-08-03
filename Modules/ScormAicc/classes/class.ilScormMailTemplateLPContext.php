<?php declare(strict_types=1);

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
 * Handles scorm mail placeholders
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @package ModulesCourse
 */
class ilScormMailTemplateLPContext extends ilMailTemplateContext
{
    public const ID = 'sahs_context_lp';

    public function getId() : string
    {
        return self::ID;
    }

    public function getTitle() : string
    {
        global $DIC;
        $lng = $DIC->language();

        $lng->loadLanguageModule('sahs');

        return $lng->txt('sahs_mail_context_lp');
    }

    public function getDescription() : string
    {
        global $DIC;
        $lng = $DIC->language();

        $lng->loadLanguageModule('sahs');

        return $lng->txt('sahs_mail_context_lp_info');
    }

    /**
     * Return an array of placeholders
     * @return array<string, mixed[]>
     */
    public function getSpecificPlaceholders() : array
    {
        /**
         * @var $lng ilLanguage
         */
        global $DIC;
        $lng = $DIC->language();

        $lng->loadLanguageModule('trac');
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
     * @throws ilDateTimeException
     */
    public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ?ilObjUser $recipient = null,
        bool $html_markup = false
    ) : string {
        /**
         * @var $ilObjDataCache ilObjectDataCache
         */
        global $DIC;
        $ilObjDataCache = $DIC['ilObjDataCache'];

        if (!in_array($placeholder_id, array('sahs_title', 'sahs_link'))) {
            return '';
        }

        $obj_id = $ilObjDataCache->lookupObjId((int) $context_parameters['ref_id']);
        $tracking = new ilObjUserTracking();

        switch ($placeholder_id) {
            case 'sahs_title':
                return $ilObjDataCache->lookupTitle($obj_id);

            case 'sahs_link':
                return ilLink::_getLink($context_parameters['ref_id'], 'sahs');

            case 'sahs_status':
                if ($recipient === null) {
                    return '';
                }
                $status = ilLPStatus::_lookupStatus($obj_id, $recipient->getId());
                if (!$status) {
                    $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                }
                return ilLearningProgressBaseGUI::_getStatusText($status, $this->getLanguage());

            case 'sahs_mark':
                if ($recipient === null) {
                    return '';
                }
                $mark = ilLPMarks::_lookupMark($recipient->getId(), $obj_id);
                return strlen(trim($mark)) ? $mark : '-';

            case 'sahs_score':
                if ($recipient === null) {
                    return '';
                }

                $scores = array();
                $obj_id = ilObject::_lookupObjId($context_parameters['ref_id']);
                $coll = ilScormLP::getInstance($obj_id)->getCollectionInstance();
                if ($coll !== null && $coll->getItems()) {
                    //changed static call into dynamic one//ukohnle
                    //foreach(ilTrQuery::getSCOsStatusForUser($recipient->getId(), $obj_id, $coll->getItems()) as $item)
                    $SCOStatusForUser = ilTrQuery::getSCOsStatusForUser(
                        $recipient->getId(),
                        $obj_id,
                        $coll->getItems()
                    );
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
                    $progress = ilLearningProgress::_getProgress($recipient->getId(), $obj_id);
                    if (isset($progress['spent_seconds'])) {
                        return ilDatePresentation::secondsToString(
                            $progress['spent_seconds'],
                            false,
                            $this->getLanguage()
                        );
                    }
                }
                break;

            case 'sahs_first_access':
                if ($recipient === null) {
                    return '';
                }

                if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS)) {
                    $progress = ilLearningProgress::_getProgress($recipient->getId(), $obj_id);
                    if (isset($progress['access_time_min'])) {
                        return ilDatePresentation::formatDate(new ilDateTime(
                            $progress['access_time_min'],
                            IL_CAL_UNIX
                        ));
                    }
                }
                break;

            case 'sahs_last_access':
                if ($recipient === null) {
                    return '';
                }

                if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS)) {
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
