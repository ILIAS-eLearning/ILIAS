<?php declare(strict_types=0);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HttpServices;

/**
 * Class ilObjUserTrackingGUI
 * Base class for all Learning progress gui classes.
 * Defines modes for presentation according to the context in which it was called
 * E.g: mode LP_CONTEXT_PERSONAL_DESKTOP displays only listOfObjects.
 * @author  Stefan Meyer <meyer@leifos.com>
 * @package ilias-tracking
 */
class ilLearningProgressBaseGUI
{
    protected RefineryFactory $refinery;
    protected HttpServices $http;
    protected ilGlobalTemplateInterface $tpl;
    protected ilHelpGUI $help;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilLogger $logger;
    protected ilTabsGUI $tabs_gui;
    protected ilToolbarGUI $toolbar;
    protected ilObjectDataCache $ilObjectDataCache;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected ilRbacReview $rbacreview;
    protected ilTree $tree;

    protected bool $anonymized;
    protected int $usr_id = 0;
    protected int $ref_id = 0;
    protected int $obj_id = 0;
    protected string $obj_type = '';
    protected int $mode = 0;

    public const LP_CONTEXT_PERSONAL_DESKTOP = 1;
    public const LP_CONTEXT_ADMINISTRATION = 2;
    public const LP_CONTEXT_REPOSITORY = 3;
    public const LP_CONTEXT_USER_FOLDER = 4;
    public const LP_CONTEXT_ORG_UNIT = 5;

    protected const LP_ACTIVE_SETTINGS = 1;
    protected const LP_ACTIVE_OBJECTS = 2;
    protected const LP_ACTIVE_PROGRESS = 3;

    protected const LP_ACTIVE_USERS = 5;
    protected const LP_ACTIVE_SUMMARY = 6;
    protected const LP_ACTIVE_OBJSTATACCESS = 7;
    protected const LP_ACTIVE_OBJSTATTYPES = 8;
    protected const LP_ACTIVE_OBJSTATDAILY = 9;
    protected const LP_ACTIVE_OBJSTATADMIN = 10;
    protected const LP_ACTIVE_MATRIX = 11;

    public function __construct(
        int $a_mode,
        int $a_ref_id = 0,
        int $a_usr_id = 0
    ) {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->help = $DIC->help();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('trac');
        $this->tabs_gui = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->ilObjectDataCache = $DIC['ilObjDataCache'];
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->rbacreview = $DIC->rbac()->review();
        $this->tree = $DIC->repositoryTree();

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->mode = $a_mode;
        $this->ref_id = $a_ref_id;
        $this->obj_id = $this->ilObjectDataCache->lookupObjId($this->ref_id);
        $this->obj_type = $this->ilObjectDataCache->lookupType($this->obj_id);
        $this->usr_id = $a_usr_id;

        $this->anonymized = !ilObjUserTracking::_enabledUserRelatedData();
        if (!$this->anonymized && $this->obj_id) {
            $olp = ilObjectLP::getInstance($this->obj_id);
            $this->anonymized = $olp->isAnonymized();
        }
        $this->logger = $DIC->logger()->trac();
    }

    public function isAnonymized() : bool
    {
        return $this->anonymized;
    }

    public function getMode() : int
    {
        return $this->mode;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    protected function initUserIdFromQuery() : int
    {
        if ($this->http->wrapper()->query()->has('user_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'user_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    public function getUserId() : int
    {
        if ($this->usr_id) {
            return $this->usr_id;
        }
        if ($this->initUserIdFromQuery()) {
            return $this->initUserIdFromQuery();
        }
        return 0;
    }

    public function __getDefaultCommand() : string
    {
        if (strlen($cmd = $this->ctrl->getCmd())) {
            return $cmd;
        }
        return 'show';
    }

    public function __setSubTabs(int $a_active) : void
    {
        switch ($this->getMode()) {
            case self::LP_CONTEXT_PERSONAL_DESKTOP:

                if (ilObjUserTracking::_hasLearningProgressLearner() &&
                    ilObjUserTracking::_enabledUserRelatedData()) {
                    $this->tabs_gui->addTarget(
                        'trac_progress',
                        $this->ctrl->getLinkTargetByClass(
                            'illplistofprogressgui',
                            ''
                        ),
                        "",
                        "",
                        "",
                        $a_active == self::LP_ACTIVE_PROGRESS
                    );
                }

                if (ilObjUserTracking::_hasLearningProgressOtherUsers()) {
                    $this->tabs_gui->addTarget(
                        'trac_objects',
                        $this->ctrl->getLinkTargetByClass(
                            "illplistofobjectsgui",
                            ''
                        ),
                        "",
                        "",
                        "",
                        $a_active == self::LP_ACTIVE_OBJECTS
                    );
                }
                break;

            case self::LP_CONTEXT_REPOSITORY:
                // #12771 - do not show status if learning progress is deactivated
                $olp = ilObjectLP::getInstance($this->obj_id);
                if ($olp->isActive()) {
                    $has_read = ilLearningProgressAccess::checkPermission(
                        'read_learning_progress',
                        $this->getRefId()
                    );

                    if ($this->isAnonymized() || !$has_read) {
                        $this->ctrl->setParameterByClass(
                            'illplistofprogressgui',
                            'user_id',
                            $this->getUserId()
                        );
                        $this->tabs_gui->addSubTabTarget(
                            'trac_progress',
                            $this->ctrl->getLinkTargetByClass(
                                'illplistofprogressgui',
                                ''
                            ),
                            "",
                            "",
                            "",
                            $a_active == self::LP_ACTIVE_PROGRESS
                        );
                    } else {
                        // Check if it is a course
                        $sub_tab = ($this->ilObjectDataCache->lookupType(
                            $this->ilObjectDataCache->lookupObjId(
                                $this->getRefId()
                            )
                        ) == 'crs') ?
                            'trac_crs_objects' :
                            'trac_objects';

                        $this->tabs_gui->addSubTabTarget(
                            $sub_tab,
                            $this->ctrl->getLinkTargetByClass(
                                "illplistofobjectsgui",
                                ''
                            ),
                            "",
                            "",
                            "",
                            $a_active == self::LP_ACTIVE_OBJECTS
                        );
                    }

                    if ($has_read) {
                        if (!$this->isAnonymized() &&
                            !($olp instanceof ilPluginLP) &&
                            ilObjectLP::supportsMatrixView($this->obj_type)) {
                            $this->tabs_gui->addSubTabTarget(
                                "trac_matrix",
                                $this->ctrl->getLinkTargetByClass(
                                    "illplistofobjectsgui",
                                    'showUserObjectMatrix'
                                ),
                                "",
                                "",
                                "",
                                $a_active == self::LP_ACTIVE_MATRIX
                            );
                        }

                        $this->tabs_gui->addSubTabTarget(
                            "trac_summary",
                            $this->ctrl->getLinkTargetByClass(
                                "illplistofobjectsgui",
                                'showObjectSummary'
                            ),
                            "",
                            "",
                            "",
                            $a_active == self::LP_ACTIVE_SUMMARY
                        );
                    }
                }
                if (!($olp instanceof ilPluginLP) &&
                    ilLearningProgressAccess::checkPermission(
                        'edit_learning_progress',
                        $this->getRefId()
                    )) {
                    $this->tabs_gui->addSubTabTarget(
                        'trac_settings',
                        $this->ctrl->getLinkTargetByClass(
                            'illplistofsettingsgui',
                            ''
                        ),
                        "",
                        "",
                        "",
                        $a_active == self::LP_ACTIVE_SETTINGS
                    );
                }
                break;

            case self::LP_CONTEXT_ADMINISTRATION:
                /*
                $this->tabs_gui->addSubTabTarget('trac_progress',
                                     $this->ctrl->getLinkTargetByClass('illplistofprogressgui',''),
                                     "","","",$a_active == self::LP_ACTIVE_PROGRESS);
                */
                $this->tabs_gui->addSubTabTarget(
                    'trac_objects',
                    $this->ctrl->getLinkTargetByClass(
                        "illplistofobjectsgui",
                        ''
                    ),
                    "",
                    "",
                    "",
                    $a_active == self::LP_ACTIVE_OBJECTS
                );
                break;

            case self::LP_CONTEXT_USER_FOLDER:
            case self::LP_CONTEXT_ORG_UNIT:
                // No tabs default class is lpprogressgui
                break;

            default:
                die('No valid mode given');
                break;
        }
    }

    public function __buildFooter() : void
    {
        switch ($this->getMode()) {
            case self::LP_CONTEXT_PERSONAL_DESKTOP:
                $this->tpl->printToStdout();
        }
    }

    public function __buildHeader() : void
    {
    }

    /**
     * Get image path for status
     * @param string|int
     * @return string
     * @todo separate string int
     */
    public static function _getImagePathForStatus($a_status) : string
    {
        // constants are either number or string, so make comparison string-based
        switch ($a_status) {
            case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
            case ilLPStatus::LP_STATUS_IN_PROGRESS:
            case ilLPStatus::LP_STATUS_REGISTERED:
                return ilUtil::getImagePath('scorm/incomplete.svg');

            case ilLPStatus::LP_STATUS_COMPLETED_NUM:
            case ilLPStatus::LP_STATUS_COMPLETED:
            case ilLPStatus::LP_STATUS_PARTICIPATED:
                return ilUtil::getImagePath('scorm/complete.svg');

            case ilLPStatus::LP_STATUS_NOT_ATTEMPTED:
            case ilLPStatus::LP_STATUS_NOT_PARTICIPATED:
            case ilLPStatus::LP_STATUS_NOT_REGISTERED:
                return ilUtil::getImagePath('scorm/not_attempted.svg');

            case ilLPStatus::LP_STATUS_FAILED_NUM:
            case ilLPStatus::LP_STATUS_FAILED:
                return ilUtil::getImagePath('scorm/failed.svg');

            default:
                return ilUtil::getImagePath('scorm/not_attempted.svg');
        }
    }

    /**
     * Get status alt text
     */
    public static function _getStatusText(
        int $a_status,
        ?ilLanguage $a_lng = null
    ) : string {
        global $DIC;

        $lng = $DIC->language();
        if (!$a_lng) {
            $a_lng = $lng;
        }
        switch ($a_status) {
            case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
                return $a_lng->txt(ilLPStatus::LP_STATUS_IN_PROGRESS);

            case ilLPStatus::LP_STATUS_COMPLETED_NUM:
                return $a_lng->txt(ilLPStatus::LP_STATUS_COMPLETED);

            case ilLPStatus::LP_STATUS_FAILED_NUM:
                return $a_lng->txt(ilLPStatus::LP_STATUS_FAILED);

            default:
                if ($a_status === ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM) {
                    return $a_lng->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED);
                }
                return $a_lng->txt((string) $a_status);
        }
    }

    /**
     * show details about current object. Uses an existing info_gui object.
     */
    public function __showObjectDetails(
        ilInfoScreenGUI $info,
        int $item_id = 0,
        bool $add_section = true
    ) : bool {
        $details_id = $item_id ?: $this->details_id;

        $olp = ilObjectLP::getInstance($details_id);
        $mode = $olp->getCurrentMode();
        if ($mode == ilLPObjSettings::LP_MODE_VISITS ||
            ilMDEducational::_getTypicalLearningTimeSeconds($details_id)) {
            // Section object details
            if ($add_section) {
                $info->addSection($this->lng->txt('details'));
            }

            if ($mode == ilLPObjSettings::LP_MODE_VISITS) {
                $info->addProperty(
                    $this->lng->txt('trac_required_visits'),
                    (string) ilLPObjSettings::_lookupVisits($details_id)
                );
            }

            if ($seconds = ilMDEducational::_getTypicalLearningTimeSeconds(
                $details_id
            )) {
                $info->addProperty(
                    $this->lng->txt('meta_typical_learning_time'),
                    ilDatePresentation::secondsToString($seconds)
                );
            }
            return true;
        }
        return false;
    }

    public function __appendLPDetails(
        ilInfoScreenGUI $info,
        int $item_id,
        int $user_id
    ) : void {
        $type = $this->ilObjectDataCache->lookupType($item_id);

        // Section learning_progress
        // $info->addSection($this->lng->txt('trac_learning_progress'));
        // see ilLPTableBaseGUI::parseTitle();
        $info->addSection(
            $this->lng->txt("trac_progress") . ": " . ilObject::_lookupTitle(
                $item_id
            )
        );
        $olp = ilObjectLP::getInstance($item_id);
        $info->addProperty(
            $this->lng->txt('trac_mode'),
            $olp->getModeText($olp->getCurrentMode())
        );

        if (ilObjectLP::isSupportedObjectType($type)) {
            $status = $this->__readStatus($item_id, $user_id);
            $status_path = ilLearningProgressBaseGUI::_getImagePathForStatus(
                $status
            );
            $status_text = ilLearningProgressBaseGUI::_getStatusText(
                ilLPStatus::_lookupStatus($item_id, $user_id)
            );
            $info->addProperty(
                $this->lng->txt('trac_status'),
                ilUtil::img($status_path, $status_text) . " " . $status_text
            );

            // status
            $i_tpl = new ilTemplate(
                "tpl.lp_edit_manual_info_page.html",
                true,
                true,
                "Services/Tracking"
            );
            $i_tpl->setVariable(
                "INFO_EDITED",
                $this->lng->txt("trac_info_edited")
            );
            $i_tpl->setVariable(
                "SELECT_STATUS",
                ilLegacyFormElementsUtil::formSelect(
                    (int) ilLPMarks::_hasCompleted(
                        $user_id,
                        $item_id
                    ),
                    'lp_edit',
                    [0 => $this->lng->txt('trac_not_completed'),
                     1 => $this->lng->txt('trac_completed')
                    ],
                    false,
                    true
                )
            );
            $i_tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
            $info->addProperty($this->lng->txt('trac_status'), $i_tpl->get());

            // #15334 - see ilLPTableBaseGUI::isPercentageAvailable()
            $mode = $olp->getCurrentMode();
            if (in_array(
                $mode,
                array(ilLPObjSettings::LP_MODE_TLT,
                             ilLPObjSettings::LP_MODE_VISITS,
                             // ilLPObjSettings::LP_MODE_OBJECTIVES,
                             ilLPObjSettings::LP_MODE_LTI_OUTCOME,
                             ilLPObjSettings::LP_MODE_CMIX_COMPLETED,
                             ilLPObjSettings::LP_MODE_CMIX_COMPL_WITH_FAILED,
                             ilLPObjSettings::LP_MODE_CMIX_PASSED,
                             ilLPObjSettings::LP_MODE_CMIX_PASSED_WITH_FAILED,
                             ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED,
                             ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED,
                             ilLPObjSettings::LP_MODE_SCORM,
                             ilLPObjSettings::LP_MODE_TEST_PASSED
            )
            )) {
                $perc = ilLPStatus::_lookupPercentage($item_id, $user_id);
                $info->addProperty(
                    $this->lng->txt('trac_percentage'),
                    (int) $perc . "%"
                );
            }
        }

        if (ilObjectLP::supportsMark($type)) {
            if (strlen($mark = ilLPMarks::_lookupMark($user_id, $item_id))) {
                $info->addProperty($this->lng->txt('trac_mark'), $mark);
            }
        }

        if (strlen($comment = ilLPMarks::_lookupComment($user_id, $item_id))) {
            $info->addProperty($this->lng->txt('trac_comment'), $comment);
        }

        // More infos for lm's
        if (in_array($type, ["lm", "htlm"])) {
            $progress = ilLearningProgress::_getProgress($user_id, $item_id);
            if ($progress['access_time']) {
                $info->addProperty(
                    $this->lng->txt('trac_last_access'),
                    ilDatePresentation::formatDate(
                        new ilDateTime($progress['access_time'], IL_CAL_UNIX)
                    )
                );
            } else {
                $info->addProperty(
                    $this->lng->txt('trac_last_access'),
                    $this->lng->txt('trac_not_accessed')
                );
            }

            $info->addProperty(
                $this->lng->txt('trac_visits'),
                (string) $progress['visits']
            );

            if ($type == 'lm') {
                $info->addProperty(
                    $this->lng->txt('trac_spent_time'),
                    ilDatePresentation::secondsToString(
                        $progress['spent_seconds']
                    )
                );
            }
        }
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    public static function __readStatus(int $a_obj_id, int $user_id) : string
    {
        $status = ilLPStatus::_lookupStatus($a_obj_id, $user_id);

        switch ($status) {
            case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
                return ilLPStatus::LP_STATUS_IN_PROGRESS;

            case ilLPStatus::LP_STATUS_COMPLETED_NUM:
                return ilLPStatus::LP_STATUS_COMPLETED;

            case ilLPStatus::LP_STATUS_FAILED_NUM:
                return ilLPStatus::LP_STATUS_FAILED;

            default:
            case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
                return ilLPStatus::LP_STATUS_NOT_ATTEMPTED;
        }
    }

    public function __getLegendHTML() : string
    {
        $tpl = new ilTemplate(
            "tpl.lp_legend.html",
            true,
            true,
            "Services/Tracking"
        );
        $tpl->setVariable(
            "IMG_NOT_ATTEMPTED",
            ilUtil::getImagePath("scorm/not_attempted.svg")
        );
        $tpl->setVariable(
            "IMG_IN_PROGRESS",
            ilUtil::getImagePath("scorm/incomplete.svg")
        );
        $tpl->setVariable(
            "IMG_COMPLETED",
            ilUtil::getImagePath("scorm/completed.svg")
        );
        $tpl->setVariable(
            "IMG_FAILED",
            ilUtil::getImagePath("scorm/failed.svg")
        );
        $tpl->setVariable(
            "TXT_NOT_ATTEMPTED",
            $this->lng->txt("trac_not_attempted")
        );
        $tpl->setVariable(
            "TXT_IN_PROGRESS",
            $this->lng->txt("trac_in_progress")
        );
        $tpl->setVariable(
            "TXT_COMPLETED",
            $this->lng->txt("trac_completed")
        );
        $tpl->setVariable(
            "TXT_FAILED",
            $this->lng->txt("trac_failed")
        );

        $panel = ilPanelGUI::getInstance();
        $panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
        $panel->setBody($tpl->get());

        return $panel->getHTML();
    }

    protected function initEditUserForm(
        int $a_user_id,
        int $a_obj_id,
        ?string $a_cancel = null
    ) : ilPropertyFormGUI {
        $olp = ilObjectLP::getInstance($a_obj_id);
        $lp_mode = $olp->getCurrentMode();

        $form = new ilPropertyFormGUI();

        $form->setFormAction($this->ctrl->getFormAction($this, "updateUser"));

        $form->setTitle(
            $this->lng->txt("edit") . ": " . ilObject::_lookupTitle($a_obj_id)
        );
        $form->setDescription(
            $this->lng->txt('trac_mode') . ": " . $olp->getModeText($lp_mode)
        );

        $user = new ilNonEditableValueGUI($this->lng->txt("user"), '', true);
        $user->setValue(ilUserUtil::getNamePresentation($a_user_id, true));
        $form->addItem($user);

        $marks = new ilLPMarks($a_obj_id, $a_user_id);

        if (ilObjectLP::supportsMark(ilObject::_lookupType($a_obj_id))) {
            $mark = new ilTextInputGUI($this->lng->txt("trac_mark"), "mark");
            $mark->setValue($marks->getMark());
            $mark->setMaxLength(32);
            $form->addItem($mark);
        }

        $comm = new ilTextInputGUI($this->lng->txt("trac_comment"), "comment");
        $comm->setValue($marks->getComment());
        $form->addItem($comm);

        if ($lp_mode == ilLPObjSettings::LP_MODE_MANUAL ||
            $lp_mode == ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR) {
            $completed = ilLPStatus::_lookupStatus($a_obj_id, $a_user_id);

            $status = new ilCheckboxInputGUI(
                $this->lng->txt('trac_completed'),
                "completed"
            );
            $status->setChecked(
                ($completed == ilLPStatus::LP_STATUS_COMPLETED_NUM)
            );
            $form->addItem($status);
        }

        $form->addCommandButton("updateUser", $this->lng->txt('save'));

        if ($a_cancel) {
            $form->addCommandButton($a_cancel, $this->lng->txt('cancel'));
        }

        return $form;
    }

    public function __showEditUser(
        int $a_user_id,
        int $a_ref_id,
        ?string $a_cancel = null,
        int $a_sub_id = 0
    ) : string {
        if (!$a_sub_id) {
            $obj_id = ilObject::_lookupObjId($a_ref_id);
        } else {
            $this->ctrl->setParameter($this, 'userdetails_id', $a_sub_id);
            $obj_id = ilObject::_lookupObjId($a_sub_id);
        }
        $this->ctrl->setParameter($this, 'user_id', $a_user_id);
        $this->ctrl->setParameter($this, 'details_id', $a_ref_id);
        $form = $this->initEditUserForm($a_user_id, $obj_id, $a_cancel);
        return $form->getHTML();
    }

    public function __updateUser(int $user_id, int $obj_id) : void
    {
        $form = $this->initEditUserForm($user_id, $obj_id);
        if ($form->checkInput()) {
            $marks = new ilLPMarks($obj_id, $user_id);
            $marks->setMark($form->getInput("mark"));
            $marks->setComment($form->getInput("comment"));

            $do_lp = false;

            // status/completed is optional
            $status = $form->getItemByPostVar("completed");
            if (is_object($status)) {
                if ($marks->getCompleted() != $form->getInput("completed")) {
                    $marks->setCompleted($form->getInput("completed"));
                    $do_lp = true;
                }
            }

            $marks->update();

            // #11600
            if ($do_lp) {
                ilLPStatusWrapper::_updateStatus($obj_id, $user_id);
            }
        }
    }

    /**
     * @param int $a_obj_id
     * @param string $a_type
     * @return bool
     * @todo switch to centralized offline status
     */
    public static function isObjectOffline(
        int $a_obj_id,
        string $a_type = ''
    ) : bool {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        if (!$a_type) {
            $a_type = $ilObjDataCache->lookupType($a_obj_id);
        }

        if ($objDefinition->isPluginTypeName($a_type)) {
            return false;
        }
        $class = "ilObj" . $objDefinition->getClassName($a_type) . "Access";
        return (bool) $class::_isOffline($a_obj_id);
    }
}
