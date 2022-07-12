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

use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HttpService;

/**
 * TableGUI class for learning progress
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesTracking
 */
class ilLPTableBaseGUI extends ilTable2GUI
{
    public const HIT_LIMIT = 5000;
    protected RefineryFactory $refinery;
    protected HttpService $http;

    protected array $filter = [];
    protected bool $anonymized = true;

    private ilObjUser $user;
    protected ilSetting $setting;
    protected ilObjectDataCache $ilObjDataCache;
    protected ilObjectDefinition $objDefinition;
    protected ilTree $tree;
    protected \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        ?object $a_parent_obj,
        string $a_parent_cmd = "",
        string $a_template_context = ""
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->objDefinition = $DIC['objDefinition'];
        $this->ilObjDataCache = $DIC['ilObjDataCache'];
        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();
        $this->setting = $DIC->settings();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

        // country names
        $this->lng->loadLanguageModule("meta");
        $this->anonymized = !ilObjUserTracking::_enabledUserRelatedData();
        if (!$this->anonymized && isset($this->obj_id) && $this->obj_id > 0) {
            $olp = ilObjectLP::getInstance($this->obj_id);
            $this->anonymized = $olp->isAnonymized();
        }
    }

    protected function initItemIdFromPost() : array
    {
        if ($this->http->wrapper()->post()->has('item_id')) {
            return $this->http->wrapper()->post()->retrieve(
                'item_id',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    protected function initUidFromPost() : array
    {
        if ($this->http->wrapper()->post()->has('uid')) {
            return $this->http->wrapper()->post()->retrieve(
                'uid',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    public function executeCommand() : bool
    {
        $this->determineSelectedFilters();
        if (!$this->ctrl->getNextClass($this)) {
            $to_hide = false;

            switch ($this->ctrl->getCmd()) {
                case "applyFilter":
                    $this->resetOffset();
                    $this->writeFilterToSession();
                    break;

                case "resetFilter":
                    $this->resetOffset();
                    $this->resetFilter();
                    break;

                case "hideSelected":
                    $to_hide = $this->initItemIdFromPost();
                    break;

                case "hide":
                    $hide = 0;
                    if ($this->http->wrapper()->query()->has('hide')) {
                        $hide = $this->http->wrapper()->query()->retrieve(
                            'hide',
                            $this->refinery->kindlyTo()->int()
                        );
                    }
                    $to_hide = [$hide];
                    break;

                case "mailselectedusers":
                    if (!$this->initUidFromPost()) {
                        $this->main_tpl->setOnScreenMessage(
                            'failure',
                            $this->lng->txt(
                                "no_checkbox"
                            ),
                            true
                        );
                    } else {
                        $this->sendMail(
                            $this->initUidFromPost(),
                            $this->parent_obj,
                            $this->parent_cmd
                        );
                    }
                    break;

                case 'addToClipboard':
                    if (!$this->initUidFromPost()) {
                        $this->main_tpl->setOnScreenMessage(
                            'failure',
                            $this->lng->txt(
                                'no_checkbox'
                            ),
                            true
                        );
                    } else {
                        $this->addToClipboard();
                    }
                    break;

                // page selector
                default:
                    $this->determineOffsetAndOrder();
                    $this->storeNavParameter();
                    break;
            }

            if ($to_hide) {
                $obj = $this->getFilterItemByPostVar("hide");
                $value = array_unique(
                    array_merge((array) $obj->getValue(), $to_hide)
                );
                $obj->setValue($value);
                $obj->writeToSession();
            }

            if ($this->requested_tmpl_create !== "") {
                $this->ctrl->setParameter(
                    $this->parent_obj,
                    "tbltplcrt",
                    $this->requested_tmpl_create
                );
            }
            if ($this->requested_tmpl_delete !== "") {
                $this->ctrl->setParameter(
                    $this->parent_obj,
                    "tbltpldel",
                    $this->requested_tmpl_delete
                );
            }
            $this->ctrl->redirect($this->parent_obj, $this->parent_cmd);
        } else {
            // e.g. repository selector
            return parent::executeCommand();
        }
        return true;
    }

    /**
     * @return int[]
     */
    protected function findReferencesForObjId(int $a_obj_id) : array
    {
        $ref_ids = array_keys(ilObject::_getAllReferences($a_obj_id));
        sort($ref_ids, SORT_NUMERIC);
        return $ref_ids;
    }



    protected function sendMail(
        array $a_user_ids,
        $a_parent_obj,
        string $a_parent_cmd
    ) : void {
        // see ilObjCourseGUI::sendMailToSelectedUsersObject()

        $rcps = array();
        foreach ($a_user_ids as $usr_id) {
            $rcps[] = ilObjUser::_lookupLogin($usr_id);
        }

        $template = array();
        $sig = null;

        $ref_id = 0;
        if ($this->http->wrapper()->query()->has('ref_id')) {
            $ref_id = $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        // repository-object-specific
        if ($ref_id) {
            $obj_lp = ilObjectLP::getInstance(
                ilObject::_lookupObjectId($ref_id)
            );
            $tmpl_id = $obj_lp->getMailTemplateId();

            if ($tmpl_id) {
                $template = array(
                    ilMailFormCall::CONTEXT_KEY => $tmpl_id,
                    'ref_id' => $ref_id,
                    'ts' => time()
                );
            } else {
                $sig = ilLink::_getLink($ref_id);
                $sig = rawurlencode(base64_encode($sig));
            }
        }

        ilUtil::redirect(
            ilMailFormCall::getRedirectTarget(
                $a_parent_obj,
                $a_parent_cmd,
                array(),
                array(
                    'type' => 'new',
                    'rcp_to' => implode(',', $rcps),
                    'sig' => $sig
                ),
                $template
            )
        );
    }

    /**
     * Search objects that match current filters
     */
    protected function searchObjects(
        array $filter,
        string $permission,
        ?array $preset_obj_ids = null,
        bool $a_check_lp_activation = true
    ) : array {
        $query_parser = new ilQueryParser($filter["query"] ?? '');
        $query_parser->setMinWordLength(0);
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
        $query_parser->parse();
        if (!$query_parser->validate()) {
            ilLoggerFactory::getLogger('trac')->notice(
                $query_parser->getMessage()
            );
            // echo $query_parser->getMessage();
            return [];
        }

        if ($filter["type"] == "lres") {
            $filter["type"] = array('lm', 'sahs', 'htlm');
        } else {
            $filter["type"] = array($filter["type"]);
        }

        $object_search = new ilLikeObjectSearch($query_parser);
        $object_search->setFilter($filter["type"]);
        if ($preset_obj_ids) {
            $object_search->setIdFilter($preset_obj_ids);
        }
        $res = $object_search->performSearch();

        if ($permission) {
            $res->setRequiredPermission($permission);
        }

        $res->setMaxHits(self::HIT_LIMIT);

        if ($a_check_lp_activation) {
            $res->addObserver($this, "searchFilterListener");
        }

        if (!$this->filter["area"]) {
            $res->filter(ROOT_FOLDER_ID, false);
        } else {
            $res->filter($this->filter["area"], false);
        }

        $objects = array();
        foreach ($res->getResults() as $obj_data) {
            $objects[$obj_data['obj_id']][] = $obj_data['ref_id'];
        }
        return $objects ?: array();
    }

    /**
     * Listener for SearchResultFilter
     * Checks wheather the object is hidden and mode is not LP_MODE_DEACTIVATED
     * @access public
     */
    public function searchFilterListener(int $a_ref_id, array $a_data) : bool
    {
        if (is_array($this->filter["hide"]) && in_array(
            $a_data["obj_id"],
            $this->filter["hide"]
        )) {
            return false;
        }
        $olp = ilObjectLP::getInstance($a_data["obj_id"]);
        if (get_class(
            $olp
        ) != "ilObjectLP" && // #13654 - LP could be unsupported
            !$olp->isActive()) {
            return false;
        }
        return true;
    }

    protected function initRepositoryFilter(array $filter) : array
    {
        $repo = new ilRepositorySelector2InputGUI(
            $this->lng->txt('trac_filter_area'),
            'effective_from',
            true
        );
        $white_list = [];
        foreach ($this->objDefinition->getAllRepositoryTypes() as $type) {
            if ($this->objDefinition->isContainer($type)) {
                $white_list[] = $type;
            }
        }
        $repo->getExplorerGUI()->setTypeWhiteList($white_list);
        $this->addFilterItem($repo);
        $repo->readFromSession();
        $filter['area'] = (int) $repo->getValue();
        return $filter;
    }

    /**
     * Init filter
     */
    public function initBaseFilter(
        bool $a_split_learning_resources = false,
        bool $a_include_no_status_filter = true
    ) {
        $this->setDisableFilterHiding(true);

        // object type selection
        $si = new ilSelectInputGUI($this->lng->txt("obj_type"), "type");
        $si->setOptions($this->getPossibleTypes($a_split_learning_resources));
        $this->addFilterItem($si);
        $si->readFromSession();
        if (!$si->getValue()) {
            $si->setValue("crs");
        }
        $this->filter["type"] = $si->getValue();

        // hidden items
        $msi = new ilMultiSelectInputGUI(
            $this->lng->txt("trac_filter_hidden"),
            "hide"
        );
        $this->addFilterItem($msi);
        $msi->readFromSession();
        $this->filter["hide"] = $msi->getValue();
        if ($this->filter["hide"]) {
            // create options from current value
            $types = $this->getCurrentFilter(true);
            $type = $types["type"];
            $options = array();
            if ($type == 'lres') {
                $type = array('lm', 'sahs', 'htlm');
            } else {
                $type = array($type);
            }
            foreach ($this->filter["hide"] as $obj_id) {
                if (in_array(
                    $this->ilObjDataCache->lookupType((int) $obj_id),
                    $type
                )) {
                    $options[$obj_id] = $this->ilObjDataCache->lookupTitle(
                        (int) $obj_id
                    );
                }
            }
            $msi->setOptions($options);
        }

        // title/description
        $ti = new ilTextInputGUI(
            $this->lng->txt("trac_title_description"),
            "query"
        );
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["query"] = $ti->getValue();

        // repository area selection
        $rs = new ilRepositorySelectorInputGUI(
            $this->lng->txt("trac_filter_area"),
            "area"
        );
        $rs->setSelectText($this->lng->txt("trac_select_area"));
        $this->addFilterItem($rs);
        $rs->readFromSession();
        $this->filter["area"] = $rs->getValue();

        // hide "not started yet"
        if ($a_include_no_status_filter) {
            $cb = new ilCheckboxInputGUI(
                $this->lng->txt("trac_filter_has_status"),
                "status"
            );
            $this->addFilterItem($cb);
            $cb->readFromSession();
            $this->filter["status"] = $cb->getChecked();
        }
    }

    /**
     */
    protected function buildPath(array $ref_ids) : array
    {
        if (!count($ref_ids)) {
            return [];
        }
        $result = [];
        foreach ($ref_ids as $ref_id) {
            $path = "...";
            $counter = 0;
            $path_full = $this->tree->getPathFull($ref_id);
            foreach ($path_full as $data) {
                if (++$counter < (count($path_full) - 1)) {
                    continue;
                }
                $path .= " &raquo; ";
                if ($ref_id != $data['ref_id']) {
                    $path .= $data['title'];
                } else {
                    $path .= ('<a target="_top" href="' .
                        ilLink::_getLink(
                            $data['ref_id'],
                            $data['type']
                        ) . '">' .
                        $data['title'] . '</a>');
                }
            }

            $result[$ref_id] = $path;
        }
        return $result;
    }

    protected function getPossibleTypes(
        bool $a_split_learning_resources = false,
        bool $a_include_digilib = false,
        bool $a_allow_undefined_lp = false
    ) : array {
        global $DIC;

        $component_repository = $DIC['component.repository'];

        $options = array();

        if ($a_split_learning_resources) {
            $options['lm'] = $this->lng->txt('objs_lm');
            $options['sahs'] = $this->lng->txt('objs_sahs');
            $options['htlm'] = $this->lng->txt('objs_htlm');
        } else {
            $options['lres'] = $this->lng->txt('obj_lrss');
        }

        $options['crs'] = $this->lng->txt('objs_crs');
        $options['grp'] = $this->lng->txt('objs_grp');
        $options['exc'] = $this->lng->txt('objs_exc');
        $options['file'] = $this->lng->txt('objs_file');
        $options['mcst'] = $this->lng->txt('objs_mcst');
        $options['svy'] = $this->lng->txt('objs_svy');
        $options['tst'] = $this->lng->txt('objs_tst');
        $options['prg'] = $this->lng->txt('objs_prg');
        $options['iass'] = $this->lng->txt('objs_iass');
        $options['copa'] = $this->lng->txt('objs_copa');
        $options['frm'] = $this->lng->txt('objs_frm');
        $options['cmix'] = $this->lng->txt('objs_cmix');
        $options['lti'] = $this->lng->txt('objs_lti');
        $options['lso'] = $this->lng->txt('objs_lso');

        if ($a_allow_undefined_lp) {
            $options['root'] = $this->lng->txt('obj_reps');
            $options['cat'] = $this->lng->txt('objs_cat');
            $options["webr"] = $this->lng->txt("objs_webr");
            $options["wiki"] = $this->lng->txt("objs_wiki");
            $options["blog"] = $this->lng->txt("objs_blog");
            $options["prtf"] = $this->lng->txt("objs_prtf");
            $options["prtt"] = $this->lng->txt("objs_prtt");
        }

        // repository plugins (currently only active)
        $plugins = $component_repository->getPluginSlotById(
            "robj"
        )->getActivePlugins();
        foreach ($plugins as $pl) {
            $pl_id = $pl->getId();
            if (ilRepositoryObjectPluginSlot::isTypePluginWithLP($pl_id)) {
                $options[$pl_id] = ilObjectPlugin::lookupTxtById(
                    $pl_id,
                    "objs_" . $pl_id
                );
            }
        }

        asort($options);
        return $options;
    }

    protected function parseValue(
        string $id,
        ?string $value,
        string $type
    ) : string {
        // get rid of aggregation
        $pos = strrpos($id, "_");
        if ($pos !== false) {
            $function = strtoupper(substr($id, $pos + 1));
            if (in_array(
                $function,
                array("MIN", "MAX", "SUM", "AVG", "COUNT")
            )) {
                $id = substr($id, 0, $pos);
            }
        }

        if (trim($value) == "" && $id != "status") {
            if ($id == "title" &&
                get_class($this) != "ilTrObjectUsersPropsTableGUI" &&
                get_class($this) != "ilTrMatrixTableGUI") {
                return "--" . $this->lng->txt("none") . "--";
            }
            return " ";
        }

        switch ($id) {
            case "first_access":
            case "create_date":
            case 'status_changed':
                $value = ilDatePresentation::formatDate(
                    new ilDateTime($value, IL_CAL_DATETIME)
                );
                break;

            case "last_access":
                $value = ilDatePresentation::formatDate(
                    new ilDateTime($value, IL_CAL_UNIX)
                );
                break;

            case "birthday":
                $value = ilDatePresentation::formatDate(
                    new ilDate($value, IL_CAL_DATE)
                );
                break;

            case "spent_seconds":
                if (!ilObjectLP::supportsSpentSeconds($type)) {
                    $value = "-";
                } else {
                    $value = ilDatePresentation::secondsToString(
                        $value,
                        ($value < 3600 ? true : false)
                    ); // #14858
                }
                break;

            case "percentage":
                if (false /* $this->isPercentageAvailable() */) {
                    $value = "-";
                } else {
                    $value = $value . "%";
                }
                break;

            case "mark":
                if (!ilObjectLP::supportsMark($type)) {
                    $value = "-";
                }
                break;

            case "gender":
                $value = $this->lng->txt("gender_" . $value);
                break;

            case "status":
                $path = ilLearningProgressBaseGUI::_getImagePathForStatus(
                    $value
                );
                $text = ilLearningProgressBaseGUI::_getStatusText($value);
                $value = ilUtil::img($path, $text);
                break;

            case "language":
                $this->lng->loadLanguageModule("meta");
                $value = $this->lng->txt("meta_l_" . $value);
                break;

            case "sel_country":
                $value = $this->lng->txt("meta_c_" . $value);
                break;
        }

        return $value;
    }

    public function getCurrentFilter(bool $as_query = false) : array
    {
        $result = array();
        foreach ($this->filter as $id => $value) {
            $item = $this->getFilterItemByPostVar($id);
            switch ($id) {
                case "title":
                case "country":
                case "gender":
                case "city":
                case "language":
                case "login":
                case "firstname":
                case "lastname":
                case "mark":
                case "u_comment":
                case "institution":
                case "department":
                case "street":
                case "zipcode":
                case "email":
                case "matriculation":
                case "sel_country":
                case "query":
                case "type":
                case "area":
                    if ($value) {
                        $result[$id] = $value;
                    }
                    break;

                case "status":
                    if ($value !== false) {
                        $result[$id] = $value;
                    }
                    break;

                case "user_total":
                case "read_count":
                case "percentage":
                case "hide":
                case "spent_seconds":
                    if (is_array($value) && implode("", $value)) {
                        $result[$id] = $value;
                    }
                    break;

                case "registration":
                case "create_date":
                case "first_access":
                case "last_access":
                case 'status_changed':
                    if ($value) {
                        if ($value["from"]) {
                            $result[$id]["from"] = $value["from"]->get(
                                IL_CAL_DATETIME
                            );
                        }
                        if ($value["to"]) {
                            $result[$id]["to"] = $value["to"]->get(
                                IL_CAL_DATETIME
                            );
                        }
                    }
                    break;

                case "birthday":
                    if ($value) {
                        if ($value["from"]) {
                            $result[$id]["from"] = $value["from"]->get(
                                IL_CAL_DATETIME
                            );
                            $result[$id]["from"] = substr(
                                $result[$id]["from"],
                                0,
                                -8
                            ) . "00:00:00";
                        }
                        if ($value["to"]) {
                            $result[$id]["to"] = $value["to"]->get(
                                IL_CAL_DATETIME
                            );
                            $result[$id]["to"] = substr(
                                $result[$id]["to"],
                                0,
                                -8
                            ) . "23:59:59";
                        }
                    }
                    break;
            }
        }

        return $result;
    }

    protected function isPercentageAvailable(int $a_obj_id) : bool
    {
        $olp = ilObjectLP::getInstance($a_obj_id);
        $mode = $olp->getCurrentMode();
        if (in_array(
            $mode,
            array(ilLPObjSettings::LP_MODE_TLT,
                         ilLPObjSettings::LP_MODE_VISITS,
                         ilLPObjSettings::LP_MODE_SCORM,
                         ilLPObjSettings::LP_MODE_LTI_OUTCOME,
                         ilLPObjSettings::LP_MODE_CMIX_COMPLETED,
                         ilLPObjSettings::LP_MODE_CMIX_COMPL_WITH_FAILED,
                         ilLPObjSettings::LP_MODE_CMIX_PASSED,
                         ilLPObjSettings::LP_MODE_CMIX_PASSED_WITH_FAILED,
                         ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED,
                         ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED,
                         ilLPObjSettings::LP_MODE_VISITED_PAGES,
                         ilLPObjSettings::LP_MODE_TEST_PASSED
        )
        )) {
            return true;
        }
        return false;
    }

    protected function parseTitle(
        int $a_obj_id,
        string $action,
        int $a_user_id = 0
    ) {
        global $DIC;

        $user = "";
        if ($a_user_id) {
            if ($a_user_id != $this->user->getId()) {
                $a_user = ilObjectFactory::getInstanceByObjId($a_user_id);
            } else {
                $a_user = $this->user;
            }
            $user .= ", " . $a_user->getFullName(
                ); // " [".$a_user->getLogin()."]";
        }

        if ($a_obj_id != ROOT_FOLDER_ID) {
            $this->setTitle(
                $this->lng->txt(
                    $action
                ) . ": " . $this->ilObjDataCache->lookupTitle($a_obj_id) . $user
            );

            $olp = ilObjectLP::getInstance($a_obj_id);
            $this->setDescription(
                $this->lng->txt('trac_mode') . ": " . $olp->getModeText(
                    $olp->getCurrentMode()
                )
            );
        } else {
            $this->setTitle($this->lng->txt($action));
        }
    }

    /**
     * Build export meta data
     */
    protected function getExportMeta() : array
    {
        global $DIC;

        $ilClientIniFile = $DIC['ilClientIniFile'];

        /* see spec
            Name of installation
            Name of the course
            Permalink to course
            Owner of course object
            Date of report generation
            Reporting period
            Name of person who generated the report.
        */

        ilDatePresentation::setUseRelativeDates(false);

        $data = array();
        $data[$this->lng->txt(
            "trac_name_of_installation"
        )] = $ilClientIniFile->readVariable('client', 'name');

        if ($this->obj_id) {
            $data[$this->lng->txt(
                "trac_object_name"
            )] = $this->ilObjDataCache->lookupTitle((int) $this->obj_id);
            if ($this->ref_id) {
                $data[$this->lng->txt("trac_object_link")] = ilLink::_getLink(
                    $this->ref_id,
                    ilObject::_lookupType($this->obj_id)
                );
            }
            $data[$this->lng->txt(
                "trac_object_owner"
            )] = ilObjUser::_lookupFullname(
                ilObject::_lookupOwner($this->obj_id)
            );
        }

        $data[$this->lng->txt(
            "trac_report_date"
        )] = ilDatePresentation::formatDate(
            new ilDateTime(
                time(),
                IL_CAL_UNIX
            )
        );
        $data[$this->lng->txt("trac_report_owner")] = $this->user->getFullName(
        );

        return $data;
    }

    protected function fillMetaExcel(ilExcel $a_excel, int &$a_row) : void
    {
        foreach ($this->getExportMeta() as $caption => $value) {
            $a_excel->setCell($a_row, 0, $caption);
            $a_excel->setCell($a_row, 1, $value);
            $a_row++;
        }
        $a_row++;
    }

    protected function fillMetaCSV(ilCSVWriter $a_csv) : void
    {
        foreach ($this->getExportMeta() as $caption => $value) {
            $a_csv->addColumn(strip_tags($caption));
            $a_csv->addColumn(strip_tags($value));
            $a_csv->addRow();
        }
        $a_csv->addRow();
    }

    /**
     * @param int $a_ref_id
     * @param int $a_user_id
     * @return bool|mixed
     */
    protected function showTimingsWarning(int $a_ref_id, int $a_user_id)
    {
        $timing_cache = ilTimingCache::getInstanceByRefId($a_ref_id);
        if ($timing_cache->isWarningRequired($a_user_id)) {
            $timings = ilTimingCache::_getTimings($a_ref_id);
            if ($timings['item']['changeable'] && $timings['user'][$a_user_id]['end']) {
                $end = $timings['user'][$a_user_id]['end'];
            } elseif ($timings['item']['suggestion_end']) {
                $end = $timings['item']['suggestion_end'];
            } else {
                $end = true;
            }
            return $end;
        }
        return false;
    }

    protected function formatSeconds(
        int $seconds,
        bool $a_shorten_zero = false
    ) : string {
        $seconds = ($seconds > 0) ? $seconds : 0;
        if ($a_shorten_zero && !$seconds) {
            return "-";
        }

        $hours = floor($seconds / 3600);
        $rest = $seconds % 3600;

        $minutes = floor($rest / 60);
        $rest = $rest % 60;

        if ($rest) {
            $minutes++;
        }

        return sprintf("%dh%02dm", $hours, $minutes);
    }

    /**
     * @param mixed $a_value
     * @param false $a_force_number
     * @return mixed
     */
    protected function anonymizeValue($a_value, bool $a_force_number = false)
    {
        // currently inactive
        return $a_value;
    }

    protected function buildValueScale(
        int $a_max_value,
        bool $a_anonymize = false,
        bool $a_format_seconds = false
    ) : array {
        $step = 0;
        if ($a_max_value) {
            $step = $a_max_value / 10;
            $base = ceil(log($step, 10));
            $fac = ceil($step / pow(10, ($base - 1)));
            $step = pow(10, $base - 1) * $fac;
        }
        if ($step <= 1) {
            $step = 1;
        }
        $ticks = range(0, $a_max_value + $step, $step);

        $value_ticks = array(0 => 0);
        foreach ($ticks as $tick) {
            $value = $tvalue = $tick;
            if ($a_anonymize) {
                $value = $this->anonymizeValue($value, true);
                $tvalue = $this->anonymizeValue($tvalue);
            }
            if ($a_format_seconds) {
                $tvalue = $this->formatSeconds($value);
            }
            $value_ticks[$value] = $tvalue;
        }

        return $value_ticks;
    }

    protected function getMonthsFilter($a_short = false) : array
    {
        $options = array();
        for ($loop = 0; $loop < 10; $loop++) {
            $year = date("Y") - $loop;
            $options[$year] = $year;
            for ($loop2 = 12; $loop2 > 0; $loop2--) {
                $month = str_pad($loop2, 2, "0", STR_PAD_LEFT);
                if ($year . $month <= date("Ym")) {
                    if (!$a_short) {
                        $caption = $year . " / " . $this->lng->txt(
                            "month_" . $month . "_long"
                        );
                    } else {
                        $caption = $year . "/" . $month;
                    }
                    $options[$year . "-" . $month] = $caption;
                }
            }
        }
        return $options;
    }

    protected function getMonthsYear($a_year = null, $a_short = false) : array
    {
        if (!$a_year) {
            $a_year = date("Y");
        }

        $all = array();
        for ($loop = 1; $loop < 13; $loop++) {
            $month = str_pad($loop, 2, "0", STR_PAD_LEFT);
            if ($a_year . "-" . $month <= date("Y-m")) {
                if (!$a_short) {
                    $caption = $this->lng->txt("month_" . $month . "_long");
                } else {
                    $caption = $this->lng->txt("month_" . $month . "_short");
                }
                $all[$a_year . "-" . $month] = $caption;
            }
        }
        return $all;
    }

    protected function getSelectableUserColumns(
        int $a_in_course = 0,
        int $a_in_group = 0
    ) : array {
        $cols = $privacy_fields = array();

        $up = new ilUserProfile();
        $up->skipGroup("preferences");
        $up->skipGroup("settings");
        $up->skipGroup("interests");
        $ufs = $up->getStandardFields();

        // default fields
        $cols["login"] = array(
            "txt" => $this->lng->txt("login"),
            "default" => true
        );

        if (!$this->anonymized) {
            $cols["firstname"] = array(
                "txt" => $this->lng->txt("firstname"),
                "default" => true
            );
            $cols["lastname"] = array(
                "txt" => $this->lng->txt("lastname"),
                "default" => true
            );
        }

        // show only if extended data was activated in lp settings
        $tracking = new ilObjUserTracking();
        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS
        )) {
            $cols["first_access"] = array(
                "txt" => $this->lng->txt("trac_first_access"),
                "default" => true
            );
            $cols["last_access"] = array(
                "txt" => $this->lng->txt("trac_last_access"),
                "default" => true
            );
        }
        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_READ_COUNT
        )) {
            $cols["read_count"] = array(
                "txt" => $this->lng->txt("trac_read_count"),
                "default" => true
            );
        }
        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS
        ) &&
            ilObjectLP::supportsSpentSeconds($this->type)) {
            $cols["spent_seconds"] = array(
                "txt" => $this->lng->txt("trac_spent_seconds"),
                "default" => true
            );
        }

        if ($this->isPercentageAvailable($this->obj_id)) {
            $cols["percentage"] = array(
                "txt" => $this->lng->txt("trac_percentage"),
                "default" => true
            );
        }

        // do not show status if learning progress is deactivated
        $olp = ilObjectLP::getInstance($this->obj_id);
        if ($olp->isActive()) {
            $cols["status"] = array(
                "txt" => $this->lng->txt("trac_status"),
                "default" => true
            );

            $cols['status_changed'] = array(
                'txt' => $this->lng->txt('trac_status_changed'),
                'default' => false
            );
        }

        if (ilObjectLP::supportsMark($this->type)) {
            $cols["mark"] = array(
                "txt" => $this->lng->txt("trac_mark"),
                "default" => true
            );
        }

        $cols["u_comment"] = array(
            "txt" => $this->lng->txt("trac_comment"),
            "default" => false
        );

        $cols["create_date"] = array(
            "txt" => $this->lng->txt("create_date"),
            "default" => false
        );
        $cols["language"] = array(
            "txt" => $this->lng->txt("language"),
            "default" => false
        );

        // add user data only if object is [part of] course
        if (!$this->anonymized &&
            ($a_in_course || $a_in_group)) {
            // only show if export permission is granted
            if (ilPrivacySettings::getInstance()->checkExportAccess(
                $this->ref_id
            )) {
                // other user profile fields
                foreach ($ufs as $f => $fd) {
                    if (!isset($cols[$f]) && $f != "username" && !$fd["lists_hide"]) {
                        if ($a_in_course &&
                            !($fd["course_export_fix_value"] || $this->setting->get(
                                "usr_settings_course_export_" . $f
                            ))) {
                            continue;
                        }
                        if ($a_in_group &&
                            !($fd["group_export_fix_value"] || $this->setting->get(
                                "usr_settings_group_export_" . $f
                            ))) {
                            continue;
                        }

                        $cols[$f] = array(
                            "txt" => $this->lng->txt($f),
                            "default" => false
                        );

                        $privacy_fields[] = $f;
                    }
                }

                // additional defined user data fields
                $user_defined_fields = ilUserDefinedFields::_getInstance();
                if ($a_in_course) {
                    $user_defined_fields = $user_defined_fields->getCourseExportableFields(
                    );
                } else {
                    $user_defined_fields = $user_defined_fields->getGroupExportableFields(
                    );
                }
                foreach ($user_defined_fields as $definition) {
                    if ($definition["field_type"] != UDF_TYPE_WYSIWYG) {
                        $f = "udf_" . $definition["field_id"];
                        $cols[$f] = array(
                            "txt" => $definition["field_name"],
                            "default" => false
                        );

                        $privacy_fields[] = $f;
                    }
                }
            }
        }

        return array($cols, $privacy_fields);
    }

    /**
     * Add selected users to clipboard
     */
    protected function addToClipboard() : void
    {
        $users = $this->initUidFromPost();
        $clip = ilUserClipboard::getInstance($this->user->getId());
        $clip->add($users);
        $clip->save();
        $this->lng->loadLanguageModule('user');
        $this->main_tpl->setOnScreenMessage(
            'success',
            $this->lng->txt(
                'clipboard_user_added'
            ),
            true
        );
    }
}
