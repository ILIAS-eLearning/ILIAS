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
 * Class ilLTIConsumerAdminProviderTableGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerProviderTableGUI extends ilTable2GUI
{
    /**
     * @var string
     */
    protected string $editProviderCmd = '';
    
    /**
     * @var string
     */
    protected string $acceptProviderAsGlobalCmd = '';
    
    /**
     * @var string
     */
    protected string $acceptProviderAsGlobalMultiCmd = '';
    
    /**
     * @var string
     */
    protected string $resetProviderToUserScopeCmd = '';
    
    /**
     * @var string
     */
    protected string $resetProviderToUserScopeMultiCmd = '';
    
    /**
     * @var string
     */
    protected string $selectProviderCmd = '';
    
    /**
     * @var string
     */
    protected string $deleteProviderCmd = '';
    
    /**
     * @var string
     */
    protected string $deleteProviderMultiCmd = '';

    /**
     * @var bool
     */
    protected bool $availabilityColumnEnabled = false;
    
    /**
     * @var bool
     */
    protected bool $ownProviderColumnEnabled = false;
    
    /**
     * @var bool
     */
    protected bool $providerCreatorColumnEnabled = false;
    
    /**
     * @var bool
     */
    protected bool $actionsColumnEnabled = false;
    
    /**
     * @var bool
     */
    protected bool $detailedUsagesEnabled = false;

    protected array $filter;
    
    public function __construct(?object $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->setId('providers');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate('tpl.lti_consume_provider_table_row.html', 'Modules/LTIConsumer');

        $this->setTitle($DIC->language()->txt('tbl_provider_header'));
        //$this->setDescription($DIC->language()->txt('tbl_provider_header_info'));
    }
    
    public function getTitle() : string
    {
        return $this->title;
    }
    
    public function getEditProviderCmd() : string
    {
        return $this->editProviderCmd;
    }
    
    public function setEditProviderCmd(string $editProviderCmd) : void
    {
        $this->editProviderCmd = $editProviderCmd;
    }
    
    public function getAcceptProviderAsGlobalCmd() : string
    {
        return $this->acceptProviderAsGlobalCmd;
    }
    
    public function setAcceptProviderAsGlobalCmd(string $acceptProviderAsGlobalCmd) : void
    {
        $this->acceptProviderAsGlobalCmd = $acceptProviderAsGlobalCmd;
    }
    
    public function getAcceptProviderAsGlobalMultiCmd() : string
    {
        return $this->acceptProviderAsGlobalMultiCmd;
    }
    
    public function setAcceptProviderAsGlobalMultiCmd(string $acceptProviderAsGlobalMultiCmd) : void
    {
        $this->acceptProviderAsGlobalMultiCmd = $acceptProviderAsGlobalMultiCmd;
    }
    
    public function getResetProviderToUserScopeCmd() : string
    {
        return $this->resetProviderToUserScopeCmd;
    }
    
    public function setResetProviderToUserScopeCmd(string $resetProviderToUserScopeCmd) : void
    {
        $this->resetProviderToUserScopeCmd = $resetProviderToUserScopeCmd;
    }
    
    public function getResetProviderToUserScopeMultiCmd() : string
    {
        return $this->resetProviderToUserScopeMultiCmd;
    }
    
    public function setResetProviderToUserScopeMultiCmd(string $resetProviderToUserScopeMultiCmd) : void
    {
        $this->resetProviderToUserScopeMultiCmd = $resetProviderToUserScopeMultiCmd;
    }
    
    public function getSelectProviderCmd() : string
    {
        return $this->selectProviderCmd;
    }
    
    public function setSelectProviderCmd(string $selectProviderCmd) : void
    {
        $this->selectProviderCmd = $selectProviderCmd;
    }
    
    public function getDeleteProviderCmd() : string
    {
        return $this->deleteProviderCmd;
    }
    
    public function setDeleteProviderCmd(string $deleteProviderCmd) : void
    {
        $this->deleteProviderCmd = $deleteProviderCmd;
    }
    
    public function getDeleteProviderMultiCmd() : string
    {
        return $this->deleteProviderMultiCmd;
    }
    
    public function setDeleteProviderMultiCmd(string $deleteProviderMultiCmd) : void
    {
        $this->deleteProviderMultiCmd = $deleteProviderMultiCmd;
    }
    
    public function isAvailabilityColumnEnabled() : bool
    {
        return $this->availabilityColumnEnabled;
    }
    
    public function setAvailabilityColumnEnabled(bool $availabilityColumnEnabled) : void
    {
        $this->availabilityColumnEnabled = $availabilityColumnEnabled;
    }
    
    public function isOwnProviderColumnEnabled() : bool
    {
        return $this->ownProviderColumnEnabled;
    }
    
    public function setOwnProviderColumnEnabled(bool $ownProviderColumnEnabled) : void
    {
        $this->ownProviderColumnEnabled = $ownProviderColumnEnabled;
    }
    
    public function isProviderCreatorColumnEnabled() : bool
    {
        return $this->providerCreatorColumnEnabled;
    }
    
    public function setProviderCreatorColumnEnabled(bool $providerCreatorColumnEnabled) : void
    {
        $this->providerCreatorColumnEnabled = $providerCreatorColumnEnabled;
    }
    
    public function isActionsColumnEnabled() : bool
    {
        return $this->actionsColumnEnabled;
    }
    
    public function setActionsColumnEnabled(bool $actionsColumnEnabled) : void
    {
        $this->actionsColumnEnabled = $actionsColumnEnabled;
    }
    
    public function isDetailedUsagesEnabled() : bool
    {
        return $this->detailedUsagesEnabled;
    }
    
    public function setDetailedUsagesEnabled(bool $detailedUsagesEnabled) : void
    {
        $this->detailedUsagesEnabled = $detailedUsagesEnabled;
    }
    
    public function hasMultiCommands() : bool
    {
        if ($this->getAcceptProviderAsGlobalMultiCmd()) {
            return true;
        }
        
        if ($this->getResetProviderToUserScopeMultiCmd()) {
            return true;
        }
        
        return false;
    }
    
    public function init() : void
    {
        parent::determineSelectedColumns();
        
        $this->initColumns();
        $this->initFilter();
        $this->initCommands();
    }
    
    protected function initCommands() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($this->getAcceptProviderAsGlobalMultiCmd()) {
            $this->addMultiCommand(
                $this->getAcceptProviderAsGlobalMultiCmd(),
                $DIC->language()->txt('lti_action_accept_providers_as_global')
            );
        }
        
        if ($this->getResetProviderToUserScopeMultiCmd()) {
            $this->addMultiCommand(
                $this->getResetProviderToUserScopeMultiCmd(),
                $DIC->language()->txt('lti_action_reset_providers_to_user_scope')
            );
        }
        
        if ($this->getDeleteProviderMultiCmd()) {
            $this->addMultiCommand(
                $this->getDeleteProviderMultiCmd(),
                $DIC->language()->txt('lti_action_delete_providers')
            );
        }
    }
    
    protected function initColumns() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if ($this->hasMultiCommands()) {
            $this->addColumn('', '', '1%');
        }
        
        $this->addColumn($DIC->language()->txt('tbl_lti_prov_icon'), 'icon');
        $this->addColumn($DIC->language()->txt('tbl_lti_prov_title'), 'title');
        
        if ($this->isColumnSelected('description')) {
            $this->addColumn($DIC->language()->txt('tbl_lti_prov_description'), 'description');
        }
        if ($this->isColumnSelected('category')) {
            $this->addColumn($DIC->language()->txt('tbl_lti_prov_category'), 'category');
        }
        if ($this->isColumnSelected('keywords')) {
            $this->addColumn($DIC->language()->txt('tbl_lti_prov_keywords'), 'keywords');
        }
        if ($this->isColumnSelected('outcome')) {
            $this->addColumn($DIC->language()->txt('tbl_lti_prov_outcome'), 'outcome');
        }
        if ($this->isColumnSelected('internal')) {
            $this->addColumn($DIC->language()->txt('tbl_lti_prov_internal'), 'external');
        }
        if ($this->isColumnSelected('with_key')) {
            $this->addColumn($DIC->language()->txt('tbl_lti_prov_with_key'), 'provider_key_customizable');
        }
        
        if ($this->isColumnSelected('availability') && $this->isAvailabilityColumnEnabled()) {
            $this->addColumn($DIC->language()->txt('tbl_lti_prov_availability'), 'availability');
        }
        
        if ($this->isColumnSelected('own_provider') && $this->isOwnProviderColumnEnabled()) {
            $this->addColumn($DIC->language()->txt('tbl_lti_prov_own_provider'), 'own_provider');
        }
        
        if ($this->isColumnSelected('provider_creator') && $this->isProviderCreatorColumnEnabled()) {
            $this->addColumn($DIC->language()->txt('tbl_lti_prov_provider_creator'), 'provider_creator');
        }
        
        if ($this->isDetailedUsagesEnabled() && self::isTrashEnabled()) {
            if ($this->isColumnSelected('usages_untrashed')) {
                $this->addColumn($DIC->language()->txt('tbl_lti_prov_usages_untrashed'), 'usages_untrashed');
            }
            
            if ($this->isColumnSelected('usages_trashed')) {
                $this->addColumn($DIC->language()->txt('tbl_lti_prov_usages_trashed'), 'usages_trashed');
            }
        } elseif ($this->isColumnSelected('usages_untrashed')) {
            $this->addColumn($DIC->language()->txt('tbl_lti_prov_usages'), 'usages_untrashed');
        }
        
        if ($this->isActionsColumnEnabled()) {
            $this->addColumn('', '', '1%');
        }
    }
    
    public function determineSelectedColumns() : void
    {
        /**
         * - do nothing to avoid ilTable2::__construct() from initialising to early
         * - we do late call to parent method within self::init()
         */
    }
    
    /**
     * @return array<string, mixed[]>
     */
    public function getSelectableColumns() : array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $columns = [];
        
        $columns['description'] = [
            'default' => true, 'txt' => $DIC->language()->txt('tbl_lti_prov_description')
        ];
        
        $columns['category'] = [
            'default' => false, 'txt' => $DIC->language()->txt('tbl_lti_prov_category')
        ];
        
        $columns['keywords'] = [
            'default' => true, 'txt' => $DIC->language()->txt('tbl_lti_prov_keywords')
        ];
        
        $columns['outcome'] = [
            'default' => false, 'txt' => $DIC->language()->txt('tbl_lti_prov_outcome')
        ];
        
        $columns['internal'] = [
            'default' => false, 'txt' => $DIC->language()->txt('tbl_lti_prov_internal')
        ];
        
        $columns['with_key'] = [
            'default' => true, 'txt' => $DIC->language()->txt('tbl_lti_prov_with_key')
        ];
        
        if ($this->isAvailabilityColumnEnabled()) {
            $columns['availability'] = [
                'default' => true, 'txt' => $DIC->language()->txt('tbl_lti_prov_availability')
            ];
        }
        
        if ($this->isOwnProviderColumnEnabled()) {
            $columns['own_provider'] = [
                'default' => false, 'txt' => $DIC->language()->txt('tbl_lti_prov_own_provider')
            ];
        }
        
        if ($this->isProviderCreatorColumnEnabled()) {
            $columns['provider_creator'] = [
                'default' => false, 'txt' => $DIC->language()->txt('tbl_lti_prov_provider_creator')
            ];
        }
        
        $columns['usages_untrashed'] = [
            'default' => true, 'txt' => $DIC->language()->txt('tbl_lti_prov_usages_untrashed')
        ];
        
        if ($this->isDetailedUsagesEnabled() && self::isTrashEnabled()) {
            $columns['usages_trashed'] = [
                'default' => false, 'txt' => $DIC->language()->txt('tbl_lti_prov_usages_trashed')
            ];
        }
        
        return $columns;
    }
    
    public function initFilter() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $title = new ilTextInputGUI($DIC->language()->txt('tbl_lti_prov_title'), 'title');
        $title->setMaxLength(64);
        $title->setSize(20);
        $this->addFilterItem($title);
        $title->readFromSession();
        $this->filter['title'] = $title->getValue();
        
        $keyword = new ilTextInputGUI($DIC->language()->txt('tbl_lti_prov_keyword'), 'keyword');
        $keyword->setMaxLength(64);
        $keyword->setSize(20);
        $this->addFilterItem($keyword);
        $keyword->readFromSession();
        $this->filter['keyword'] = $keyword->getValue();
        
        $hasOutcome = new ilCheckboxInputGUI($DIC->language()->txt('tbl_lti_prov_outcome'), 'outcome');
        $this->addFilterItem($hasOutcome);
        $hasOutcome->readFromSession();
        $this->filter['outcome'] = $hasOutcome->getValue();
        
        $isInternal = new ilCheckboxInputGUI($DIC->language()->txt('tbl_lti_prov_internal'), 'internal');
        $this->addFilterItem($isInternal);
        $isInternal->readFromSession();
        $this->filter['internal'] = $isInternal->getValue();

        $isWithKey = new ilCheckboxInputGUI($DIC->language()->txt('tbl_lti_prov_with_key'), 'with_key');
        $this->addFilterItem($isWithKey);
        $isWithKey->readFromSession();
        $this->filter['with_key'] = $isWithKey->getValue();

        $category = new ilSelectInputGUI($DIC->language()->txt('tbl_lti_prov_category'), 'category');
        $category->setOptions(array_merge(
            ['' => $DIC->language()->txt('tbl_lti_prov_all_categories')],
            ilLTIConsumeProvider::getCategoriesSelectOptions()
        ));
        $this->addFilterItem($category);
        $category->readFromSession();
        $this->filter['category'] = $category->getValue();
    }
    
    protected function fillRow(array $a_set) : void
    {
        if ($this->hasMultiCommands()) {
            $this->tpl->setCurrentBlock('checkbox_col');
            $this->tpl->setVariable('PROVIDER_ID', $a_set['id']);
            $this->tpl->parseCurrentBlock();
        }
        
        if ($this->getSelectProviderCmd()) {
            $this->tpl->setCurrentBlock('title_linked');
            $this->tpl->setVariable('TITLE', $a_set['title']);
            $this->tpl->setVariable('TITLE_HREF', $this->buildProviderLink(
                $a_set['id'],
                $this->getSelectProviderCmd()
            ));
            $this->tpl->parseCurrentBlock();
        } elseif ($this->getEditProviderCmd()) {
            $this->tpl->setCurrentBlock('title_linked');
            $this->tpl->setVariable('TITLE', $a_set['title']);
            $this->tpl->setVariable('TITLE_HREF', $this->buildProviderLink(
                $a_set['id'],
                $this->getEditProviderCmd()
            ));
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('title');
            $this->tpl->setVariable('TITLE', $a_set['title']);
            $this->tpl->parseCurrentBlock();
        }
        
        if (isset($a_set['icon'])) {
            $this->tpl->setVariable('ICON_SRC', $a_set['icon']);
            $this->tpl->setVariable('ICON_ALT', basename($a_set['icon']));
        } else {
            $icon = ilObject::_getIcon(0, "small", "lti");
            $this->tpl->setVariable('ICON_SRC', $icon);
            $this->tpl->setVariable('ICON_ALT', 'lti');
        }
        
        if ($this->isColumnSelected('description')) {
            $this->tpl->setVariable('DESCRIPTION', $a_set['description']);
        }
        
        if ($this->isColumnSelected('category')) {
            $this->tpl->setVariable('CATEGORY', $this->getCategoryTranslation($a_set['category']));
        }
        
        if ($this->isColumnSelected('keywords')) {
            $this->tpl->setVariable('KEYWORDS', $this->getKeywordsFormatted($a_set['keywords']));
        }
        
        if ($this->isColumnSelected('outcome')) {
            $this->tpl->setVariable('OUTCOME', $this->getHasOutcomeFormatted($a_set['outcome']));
        }
        
        if ($this->isColumnSelected('internal')) {
            $this->tpl->setVariable('INTERNAL', $this->getIsInternalFormatted(!(bool) $a_set['external']));
        }
        
        if ($this->isColumnSelected('with_key')) {
            $this->tpl->setVariable('WITH_KEY', $this->getIsWithKeyFormatted(!(bool) $a_set['provider_key_customizable']));
        }
        
        if ($this->isColumnSelected('availability') && $this->isAvailabilityColumnEnabled()) {
            $this->tpl->setVariable('AVAILABILITY', $this->getAvailabilityLabel($a_set));
        }
        
        if ($this->isColumnSelected('own_provider') && $this->isOwnProviderColumnEnabled()) {
            $this->tpl->setVariable('OWN_PROVIDER', $this->getOwnProviderLabel($a_set));
        }
        
        if ($this->isColumnSelected('provider_creator') && $this->isProviderCreatorColumnEnabled()) {
            $this->tpl->setVariable('PROVIDER_CREATOR', $this->getProviderCreatorLabel($a_set));
        }
        
        if ($this->isColumnSelected('usages_untrashed')) {
            $usagesUntrashed = $a_set['usages_untrashed'] ? $a_set['usages_untrashed'] : '';
            $this->tpl->setVariable('USAGES_UNTRASHED', $usagesUntrashed);
        }
        
        if ($this->isColumnSelected('usages_trashed') && $this->isDetailedUsagesEnabled() && self::isTrashEnabled()) {
            $usagesTrashed = $a_set['usages_trashed'] ? $a_set['usages_trashed'] : '';
            $this->tpl->setVariable('USAGES_TRASHED', $usagesTrashed);
        }
        
        if ($this->isActionsColumnEnabled()) {
            $this->tpl->setVariable('ACTIONS', $this->buildActionsListHtml($a_set));
        }
    }
    
    protected function getHasOutcomeFormatted(bool $hasOutcome) : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return $hasOutcome ? $DIC->language()->txt('yes') : '';
    }
    
    protected function getIsInternalFormatted(bool $isInternal) : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return $isInternal ? $DIC->language()->txt('yes') : '';
    }
    
    protected function getIsWithKeyFormatted(bool $isWithKey) : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return $isWithKey ? $DIC->language()->txt('yes') : '';
    }

    protected function getCategoryTranslation(string $category) : string
    {
        $categories = ilLTIConsumeProvider::getCategoriesSelectOptions();
        return $categories[$category];
    }
    
    protected function getKeywordsFormatted(array $keywords) : string
    {
        return implode('<br />', $keywords);
    }
    
    protected function getAvailabilityLabel(array $data) : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        switch ($data['availability']) {
            case ilLTIConsumeProvider::AVAILABILITY_CREATE:
                
                return $DIC->language()->txt('lti_con_prov_availability_create');
                
            case ilLTIConsumeProvider::AVAILABILITY_EXISTING:
                
                return $DIC->language()->txt('lti_con_prov_availability_existing');
                
            case ilLTIConsumeProvider::AVAILABILITY_NONE:
                
                return $DIC->language()->txt('lti_con_prov_availability_non');
        }
        return '';
    }
    
    protected function getOwnProviderLabel(array $data) : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($data['creator'] == $DIC->user()->getId()) {
            return $DIC->language()->txt('yes');
        }
        
        return '';
    }
    
    protected function getProviderCreatorLabel(array $data) : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($data['creator']) {
            /* @var ilObjUser $user */
            $user = ilObjectFactory::getInstanceByObjId($data['creator'], false);
            
            if ($user) {
                return $user->getFullname();
            }
            
            return $DIC->language()->txt('deleted_user');
        }
        
        return '';
    }
    
    protected function buildActionsListHtml(array $data) : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $items = $this->getActionItems($data);
        
        if ($items !== []) {
            return $DIC->ui()->renderer()->render(
                $DIC->ui()->factory()->dropdown()->standard($items)->withLabel(
                    $DIC->language()->txt('actions')
                )
            );
        }
        
        return '';
    }
    
    /**
     * @return mixed[]
     */
    protected function getActionItems(array $data) : array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $items = array();
        
        if ($this->getEditProviderCmd()) {
            $items[] = $DIC->ui()->factory()->button()->shy(
                $DIC->language()->txt('lti_action_edit_provider'),
                $this->buildProviderLink($data['id'], $this->getEditProviderCmd())
            );
        }
        
        if ($this->getAcceptProviderAsGlobalCmd()) {
            $items[] = $DIC->ui()->factory()->button()->shy(
                $DIC->language()->txt('lti_action_accept_provider_as_global'),
                $this->buildProviderLink($data['id'], $this->getAcceptProviderAsGlobalCmd())
            );
        }
        
        if ($this->getResetProviderToUserScopeCmd() && $this->isUserCreatedProviderResettableToUserScope($data)) {
            $items[] = $DIC->ui()->factory()->button()->shy(
                $DIC->language()->txt('lti_action_reset_provider_to_user_scope'),
                $this->buildProviderLink($data['id'], $this->getResetProviderToUserScopeCmd())
            );
        }
        
        if ($this->getSelectProviderCmd()) {
            $items[] = $DIC->ui()->factory()->button()->shy(
                $DIC->language()->txt('lti_select_provider'),
                $this->buildProviderLink($data['id'], $this->getSelectProviderCmd())
            );
        }
        
        if ($this->getDeleteProviderCmd() && !$data['usages_untrashed'] && !$data['usages_trashed']) {
            $items[] = $DIC->ui()->factory()->button()->shy(
                $DIC->language()->txt('lti_delete_provider'),
                $this->buildProviderLink($data['id'], $this->getDeleteProviderCmd())
            );
        }
        
        return $items;
    }
    
    protected function buildProviderLink(int $providerId, string $command) : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->ctrl()->setParameter($this->parent_obj, 'provider_id', $providerId);
        $link = $DIC->ctrl()->getLinkTarget($this->parent_obj, $command);
        $DIC->ctrl()->setParameter($this->parent_obj, 'provider_id', 0);
        
        return $link;
    }
    
    protected function isUserCreatedProviderResettableToUserScope(array $data) : bool
    {
        return (bool) $data['creator'] && (bool) $data['accepted_by'];
    }
    
    protected static function isTrashEnabled() : bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return (bool) $DIC->settings()->get('enable_trash', "0");
    }
}
