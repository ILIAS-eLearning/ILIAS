<?php

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

declare(strict_types=1);

/**
 * Handles scorm mail placeholders
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @package ModulesCourse
 */
class ilScormMailTemplateLPContext extends ilMailTemplateContext
{
    public const ID = 'sahs_context_lp';

    public function getId(): string
    {
        return self::ID;
    }

    public function getTitle(): string
    {
        global $DIC;
        $lng = $DIC->language();

        $lng->loadLanguageModule('sahs');

        return $lng->txt('sahs_mail_context_lp');
    }

    public function getDescription(): string
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
    public function getSpecificPlaceholders(): array
    {
        /**
         * @var $lng ilLanguage
         */
        global $DIC;
        $lng = $DIC->language();

        $lng->loadLanguageModule('trac');
        $tracking = new ilObjUserTracking();


        $placeholders = [];


        $placeholders['scorm_title'] = [
            'placeholder' => 'SCORM_TITLE',
            'label' => $lng->txt('obj_sahs')
        ];

        $placeholders['scorm_status'] = [
            'placeholder' => 'SCORM_STATUS',
            'label' => $lng->txt('trac_status')
        ];

        $placeholders['scorm_mark'] = [
            'placeholder' => 'SCORM_MARK',
            'label' => $lng->txt('trac_mark')
        ];

        // #17969
        $lng->loadLanguageModule('content');
        $placeholders['scorm_score'] = [
            'placeholder' => 'SCORM_SCORE',
            'label' => $lng->txt('cont_score')
        ];

        if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS)) {
            $placeholders['scorm_time_spent'] = [
                'placeholder' => 'SCORM_TIME_SPENT',
                'label' => $lng->txt('trac_spent_seconds')
            ];
        }

        if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS)) {
            $placeholders['scorm_first_access'] = [
                'placeholder' => 'SCORM_FIRST_ACCESS',
                'label' => $lng->txt('trac_first_access')
            ];

            $placeholders['scorm_last_access'] = [
                'placeholder' => 'SCORM_LAST_ACCESS',
                'label' => $lng->txt('trac_last_access')
            ];
        }


        $placeholders['scorm_link'] = [
            'placeholder' => 'SCORM_LINK',
            'label' => $lng->txt('perma_link')
        ];

        return $placeholders;
    }

    /**
     * @throws ilDateTimeException
     */
    public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ?ilObjUser $recipient = null
    ): string {
        /**
         * @var $ilObjDataCache ilObjectDataCache
         */
        global $DIC;
        $ilObjDataCache = $DIC['ilObjDataCache'];

        $obj_id = $ilObjDataCache->lookupObjId((int) $context_parameters['ref_id']);
        $tracking = new ilObjUserTracking();

        switch ($placeholder_id) {
            case 'scorm_title':
                return $ilObjDataCache->lookupTitle($obj_id);

            case 'scorm_link':
                return ilLink::_getLink((int) $context_parameters['ref_id'], 'sahs');

            case 'scorm_status':
                if ($recipient === null) {
                    return '';
                }
                $status = ilLPStatus::_lookupStatus($obj_id, $recipient->getId());
                if (!$status) {
                    $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                }
                return ilLearningProgressBaseGUI::_getStatusText($status, $this->getLanguage());

            case 'scorm_mark':
                if ($recipient === null) {
                    return '';
                }
                $mark = ilLPMarks::_lookupMark($recipient->getId(), $obj_id);
                return strlen(trim($mark)) ? $mark : '-';

            case 'scorm_score':
                if ($recipient === null) {
                    return '';
                }

                $scores = [];
                $obj_id = ilObject::_lookupObjId((int) $context_parameters['ref_id']);
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

            case 'scorm_time_spent':
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

            case 'scorm_first_access':
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

            case 'scorm_last_access':
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
