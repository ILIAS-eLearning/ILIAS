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

class ilCourseMailTemplateTutorContextPreview extends ilCourseMailTemplateTutorContext
{
    public const ID = 'crs_context_tutor_manual_preview';
    public const DEFAULT_COURSE_TITLE = "preview_crs_title";
    public const DEFAULT_COURSE_STATUS = "preview_crs_status";
    public const DEFAULT_COURSE_MARK = "preview_crs_mark";
    public const DEFAULT_COURSE_TIME_SPENT = "3671";

    public function __construct()
    {
        global $DIC;
        $this->g_lng = $DIC->language();
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return self::ID;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveSpecificPlaceholder(
        $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        $html_markup = false
    ): string {
        if (!in_array($placeholder_id, array('crs_title', 'crs_link'))) {
            return "";
        }

        $this->g_lng->loadLanguageModule('sess');
        $ret = null;
        switch ($placeholder_id) {
            case 'crs_title':
                $ret = $this->g_lng->txt(self::DEFAULT_COURSE_TITLE);
                break;
            case 'crs_link':
                require_once './Services/Link/classes/class.ilLink.php';
                $ret = ilLink::_getLink($context_parameters['ref_id'], 'crs');
                break;
            case 'crs_status':
                $ret = $this->g_lng->txt(self::DEFAULT_COURSE_STATUS);
                break;
            case 'crs_mark':
                $ret = $this->g_lng->txt(self::DEFAULT_COURSE_MARK);
                break;
            case 'crs_time_spent':
                if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS)) {
                    require_once("Services/Calendar/classes/class.ilDatePresentation.php");
                    $ret = ilDatePresentation::secondsToString(self::DEFAULT_COURSE_TIME_SPENT, true, $this->g_lng);
                }
                break;
            case 'crs_first_access':
                if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS)) {
                    $ret = date("d.m.Y", strtotime("-5 day"));
                }
                break;
            case 'crs_last_access':
                if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS)) {
                    $ret = date("d.m.Y", strtotime("-1 day"));
                }
                break;
            default:
                $ret = "";
        }

        return $ret;
    }
}
