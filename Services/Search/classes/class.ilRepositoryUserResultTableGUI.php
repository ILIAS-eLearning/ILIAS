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
* TableGUI class user search results
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @ingroup ServicesSearch
*/
class ilRepositoryUserResultTableGUI extends ilTable2GUI
{
    public const TYPE_STANDARD = 1;
    public const TYPE_GLOBAL_SEARCH = 2;
    
    private ?ilLuceneSearchResult $lucene_result = null;
    
    protected static array $all_selectable_cols = [];
    protected bool $admin_mode;
    protected int $type;
    protected bool $user_limitations = true;

    protected ilObjUser $user;
    protected ilRbacReview $review;
    

    public function __construct($a_parent_obj, $a_parent_cmd, $a_admin_mode = false, $a_type = self::TYPE_STANDARD)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->review = $DIC->rbac()->review();

        $this->admin_mode = (bool) $a_admin_mode;
        $this->type = $a_type;

        $this->setId("rep_search_" . $this->user->getId());
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        
        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
        $this->setTitle($this->lng->txt('search_results'));
        $this->setEnableTitle(true);
        $this->setShowRowsSelector(true);
        

        if ($this->getType() == self::TYPE_STANDARD) {
            $this->setRowTemplate("tpl.rep_search_usr_result_row.html", "Services/Search");
            $this->addColumn("", "", "1", true);
            $this->enable('select_all');
            $this->setSelectAllCheckbox("user[]");
            $this->setDefaultOrderField("login");
            $this->setDefaultOrderDirection("asc");
        } else {
            $this->setRowTemplate("tpl.global_search_usr_result_row.html", "Services/Search");
            $this->addColumn('', '', "110px");
        }

        $all_cols = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $col) {
            $this->addColumn($all_cols[$col]['txt'], $col);
        }
        
        if ($this->getType() == self::TYPE_STANDARD) {
        } else {
            $this->addColumn($this->lng->txt('lucene_relevance_short'), 'relevance');
            if (ilBuddySystem::getInstance()->isEnabled()) {
                $this->addColumn('', '');
            }
            $this->setDefaultOrderField("relevance");
            $this->setDefaultOrderDirection("desc");
        }
    }
    
    /**
     * enable numeric ordering for relevance
     * @param string $a_field
     * @return boolean
     */
    public function numericOrdering(string $a_field) : bool
    {
        if ($a_field == 'relevance') {
            return true;
        }
        return parent::numericOrdering($a_field);
    }

    public function getType() : int
    {
        return $this->type;
    }

    public function setLuceneResult(ilLuceneSearchResult $res) : void
    {
        $this->lucene_result = $res;
    }

    public function getLuceneResult() : ilLuceneSearchResult
    {
        return $this->lucene_result;
    }

    public function setUserLimitations(bool $a_limitations) : void
    {
        $this->user_limitations = $a_limitations;
    }

    public function getUserLimitations() : bool
    {
        return $this->user_limitations;
    }

    /**
     * @return array
     */
    public function getSelectableColumns() : array
    {
        if (!self::$all_selectable_cols) {
            $columns = ilUserSearchOptions::getSelectableColumnInfo($this->review->isAssigned($this->user->getId(), SYSTEM_ROLE_ID));
            
            if ($this->admin_mode) {
                // #11293
                $columns['access_until'] = array('txt' => $this->lng->txt('access_until'));
                $columns['last_login'] = array('txt' => $this->lng->txt('last_login'));
            }
            
            self::$all_selectable_cols = $columns;
        }
        return self::$all_selectable_cols;
    }

    public function initMultiCommands(array $a_commands) : bool
    {
        if (!count($a_commands)) {
            $this->addMultiCommand('addUser', $this->lng->txt('btn_add'));
            return true;
        }
        $this->addMultiItemSelectionButton('member_type', $a_commands, 'addUser', $this->lng->txt('btn_add'));
        return true;
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("VAL_ID", $a_set["usr_id"]);
        
        $link = '';
        if ($this->getType() == self::TYPE_GLOBAL_SEARCH) {
            $link = ilUserUtil::getProfileLink($a_set['usr_id']);
            if ($link) {
                $this->tpl->setVariable('IMG_LINKED_TO_PROFILE', $link);
                $this->tpl->setVariable(
                    'USR_IMG_SRC_LINKED',
                    ilObjUser::_getPersonalPicturePath($a_set['usr_id'], 'xsmall')
                );
            } else {
                $this->tpl->setVariable(
                    'USR_IMG_SRC',
                    ilObjUser::_getPersonalPicturePath($a_set['usr_id'], 'xsmall')
                );
            }
        }
        
        
        foreach ($this->getSelectedColumns() as $field) {
            switch ($field) {
                case 'gender':
                    $a_set['gender'] = $a_set['gender'] ? $this->lng->txt('gender_' . $a_set['gender']) : '';
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', $a_set[$field]);
                    $this->tpl->parseCurrentBlock();
                    break;

                case 'birthday':
                    $a_set['birthday'] = $a_set['birthday'] ? ilDatePresentation::formatDate(new ilDate($a_set['birthday'], IL_CAL_DATE)) : $this->lng->txt('no_date');
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', $a_set[$field]);
                    $this->tpl->parseCurrentBlock();
                    break;
                
                case 'access_until':
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('CUST_CLASS', ' ' . $a_set['access_class']);
                    $this->tpl->setVariable('VAL_CUST', $a_set[$field]);
                    $this->tpl->parseCurrentBlock();
                    break;
                
                case 'last_login':
                    $a_set['last_login'] = $a_set['last_login'] ? ilDatePresentation::formatDate(new ilDateTime($a_set['last_login'], IL_CAL_DATETIME)) : $this->lng->txt('no_date');
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', $a_set[$field]);
                    $this->tpl->parseCurrentBlock();
                    break;
                
                case 'interests_general':
                case 'interests_help_offered':
                case 'interests_help_looking':
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', implode(', ', (array) $a_set[$field]));
                    $this->tpl->parseCurrentBlock();
                    break;
                
                case 'org_units':
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable(
                        'VAL_CUST',
                        ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits((int) $a_set['usr_id'])
                    );
                    $this->tpl->parseCurrentBlock();
                    break;
                

                case 'login':
                    if ($this->admin_mode) {
                        $this->ctrl->setParameterByClass("ilobjusergui", "ref_id", "7");
                        $this->ctrl->setParameterByClass("ilobjusergui", "obj_id", $a_set["usr_id"]);
                        $this->ctrl->setParameterByClass("ilobjusergui", "search", "1");
                        $link = $this->ctrl->getLinkTargetByClass(array("iladministrationgui", "ilobjusergui"), "view");
                        $a_set[$field] = "<a href=\"" . $link . "\">" . $a_set[$field] . "</a>";
                    } elseif ($this->getType() == self::TYPE_GLOBAL_SEARCH) {
                        $a_set[$field] = "<a href=\"" . $link . "\">" . $a_set[$field] . "</a>";
                    }
                    // fallthrough
                
                    // no break
                default:
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', (string) ($a_set[$field] ?: ''));
                    $this->tpl->parseCurrentBlock();
                    break;
            }
        }
        
        if ($this->getType() == self::TYPE_GLOBAL_SEARCH) {
            $this->tpl->setVariable('SEARCH_RELEVANCE', $this->getRelevanceHTML($a_set['relevance']));
            if (ilBuddySystem::getInstance()->isEnabled() && $a_set['usr_id'] != $this->user->getId()) {
                require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemLinkButton.php';
                $this->tpl->setVariable('CONTACT_ACTIONS', ilBuddySystemLinkButton::getInstanceByUserId((int) $a_set['usr_id'])->getHtml());
            } else {
                $this->tpl->setVariable('CONTACT_ACTIONS', '');
            }
        }
    }

    public function parseUserIds(array $a_user_ids) : bool
    {
        if (!$a_user_ids) {
            $this->setData(array());
            return true;
        }

        $additional_fields = $this->getSelectedColumns();
        
        $parse_access = false;
        if (isset($additional_fields['access_until'])) {
            $parse_access = true;
            unset($additional_fields['access_until']);
        }
        
        $udf_ids = $usr_data_fields = $odf_ids = array();
        foreach ($additional_fields as $field) {
            if ($field == 'org_units') {
                continue;
            }
            if (substr($field, 0, 3) == 'udf') {
                $udf_ids[] = substr($field, 4);
                continue;
            }
            $usr_data_fields[] = $field;
        }

        $u_query = new ilUserQuery();
        $u_query->setOrderField('login');
        $u_query->setOrderDirection('ASC');
        $u_query->setLimit(999999);

        if (!ilSearchSettings::getInstance()->isInactiveUserVisible() && $this->getUserLimitations()) {
            $u_query->setActionFilter("active");
        }

        if (!ilSearchSettings::getInstance()->isLimitedUserVisible() && $this->getUserLimitations()) {
            $u_query->setAccessFilter(true);
        }

        $u_query->setAdditionalFields($usr_data_fields);
        $u_query->setUserFilter($a_user_ids);

        $usr_data = $u_query->query();

        
        if ($this->admin_mode && $parse_access) {
            // see ilUserTableGUI
            $current_time = time();
            foreach ($usr_data['set'] as $k => $user) {
                if ($user['active']) {
                    if ($user["time_limit_unlimited"]) {
                        $txt_access = $this->lng->txt("access_unlimited");
                        $usr_data["set"][$k]["access_class"] = "smallgreen";
                    } elseif ($user["time_limit_until"] < $current_time) {
                        $txt_access = $this->lng->txt("access_expired");
                        $usr_data["set"][$k]["access_class"] = "smallred";
                    } else {
                        $txt_access = ilDatePresentation::formatDate(new ilDateTime($user["time_limit_until"], IL_CAL_UNIX));
                        $usr_data["set"][$k]["access_class"] = "small";
                    }
                } else {
                    $txt_access = $this->lng->txt("inactive");
                    $usr_data["set"][$k]["access_class"] = "smallred";
                }
                $usr_data["set"][$k]["access_until"] = $txt_access;
            }
        }
        
        // Custom user data fields
        if ($udf_ids) {
            $data = ilUserDefinedData::lookupData($a_user_ids, $udf_ids);

            $users = array();
            $counter = 0;
            foreach ($usr_data['set'] as $set) {
                $users[$counter] = $set;
                foreach ($udf_ids as $udf_field) {
                    $users[$counter]['udf_' . $udf_field] = $data[$set['usr_id']][$udf_field];
                }
                ++$counter;
            }
        } else {
            $users = $usr_data['set'];
        }
        
        if ($this->getType() == self::TYPE_GLOBAL_SEARCH) {
            if ($this->getLuceneResult() instanceof ilLuceneSearchResult) {
                foreach ($users as $counter => $ud) {
                    $users[$counter]['relevance'] = $this->getLuceneResult()->getRelevance($ud['usr_id']);
                }
            }
        }
        
        
        
        $this->setData($users);
        return true;
    }


    public function getRelevanceHTML(float $a_rel) : string
    {
        $tpl = new ilTemplate('tpl.lucene_relevance.html', true, true, 'Services/Search');

        $pbar = ilProgressBar::getInstance();
        $pbar->setCurrent($a_rel);
        
        $tpl->setCurrentBlock('relevance');
        $tpl->setVariable('REL_PBAR', $pbar->render());
        $tpl->parseCurrentBlock();
        
        return $tpl->get();
    }
}
