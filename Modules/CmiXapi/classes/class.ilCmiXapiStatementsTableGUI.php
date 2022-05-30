<?php declare(strict_types=1);

use ILIAS\UI\Component\Modal\RoundTrip;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilCmiXapiStatementsTableGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiStatementsTableGUI extends ilTable2GUI
{
    const TABLE_ID = 'cmix_statements_table';
    
    protected bool $isMultiActorReport;
    protected array $filter = [];
    private \ILIAS\DI\Container $dic;
    private ilLanguage $language;
    
    /**
     * @param ilCmiXapiStatementsGUI|ilLTIConsumerXapiStatementsGUI $a_parent_obj
     * @param string                                                $a_parent_cmd
     * @param bool                                                  $isMultiActorReport
     * @throws ilCtrlException
     */
    public function __construct(?object $a_parent_obj, string $a_parent_cmd, bool $isMultiActorReport)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $this->dic = $DIC;
        $DIC->language()->loadLanguageModule('cmix');
        $this->language = $DIC->language();

        $this->isMultiActorReport = $isMultiActorReport;
        
        $this->setId(self::TABLE_ID);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $DIC->language()->loadLanguageModule('form');
        
        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate('tpl.cmix_statements_table_row.html', 'Modules/CmiXapi');
        
        #$this->setTitle($DIC->language()->txt('tbl_statements_header'));
        #$this->setDescription($DIC->language()->txt('tbl_statements_header_info'));
        
        $this->initColumns();
        $this->initFilter();
        
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);
        
        $this->setDefaultOrderField('date');
        $this->setDefaultOrderDirection('desc');
    }
    
    protected function initColumns() : void
    {
        $this->addColumn($this->language->txt('tbl_statements_date'), 'date');
        
        if ($this->isMultiActorReport) {
            $this->addColumn($this->language->txt('tbl_statements_actor'), 'actor');
        }

        $this->addColumn($this->language->txt('tbl_statements_verb'), 'verb');
        $this->addColumn($this->language->txt('tbl_statements_object'), 'object');

        $this->addColumn('', '', '1%');
    }
    
    public function initFilter() : void
    {
        if ($this->isMultiActorReport) {
            $ti = new ilTextInputGUI('User', "actor");
            $ti->setDataSource($this->dic->ctrl()->getLinkTarget($this->parent_obj, 'asyncUserAutocomplete', '', true));
            $ti->setMaxLength(64);
            $ti->setSize(20);
            $this->addFilterItem($ti);
            $ti->readFromSession();
            $this->filter["actor"] = $ti->getValue();
        }
        
        /**
         * dynamic verbsList (postponed or never used)
         */
        /*
        $verbs = $this->parent_obj->getVerbs(); // ToDo: Caching
        $si = new ilSelectInputGUI('Used Verb', "verb");
        $si->setOptions(ilCmiXapiVerbList::getInstance()->getDynamicSelectOptions($verbs));
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["verb"] = $si->getValue();
        */

        $si = new ilSelectInputGUI('Used Verb', "verb");
        $si->setOptions(ilCmiXapiVerbList::getInstance()->getSelectOptions());
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["verb"] = $si->getValue();

        $dp = new ilCmiXapiDateDurationInputGUI('Period', 'period');
        $dp->setShowTime(true);
        $this->addFilterItem($dp);
        $dp->readFromSession();
        $this->filter["period"] = $dp->getValue();
    }
    
    protected function fillRow(array $a_set) : void
    {
        $r = $this->dic->ui()->renderer();

        $a_set['rowkey'] = md5(serialize($a_set));
        
        $rawDataModal = $this->getRawDataModal($a_set);
        $actionsList = $this->getActionsList($rawDataModal, $a_set);
        
        $date = ilDatePresentation::formatDate(
            ilCmiXapiDateTime::fromXapiTimestamp($a_set['date'])
        );
        
        $this->tpl->setVariable('STMT_DATE', $date);
        
        if ($this->isMultiActorReport) {
            $actor = $a_set['actor'];
            if (empty($actor)) {
                $this->tpl->setVariable('STMT_ACTOR', 'user_not_found');
            } else {
                $this->tpl->setVariable('STMT_ACTOR', $this->getUsername($a_set['actor']));
            }
        }
        
        $this->tpl->setVariable('STMT_VERB', ilCmiXapiVerbList::getVerbTranslation(
            $this->language,
            $a_set['verb_id']
        ));
        
        $this->tpl->setVariable('STMT_OBJECT', $a_set['object']);
        $this->tpl->setVariable('STMT_OBJECT_INFO', $a_set['object_info']);
        $this->tpl->setVariable('ACTIONS', $r->render($actionsList));
        $this->tpl->setVariable('RAW_DATA_MODAL', $r->render($rawDataModal));
    }

    protected function getActionsList(RoundTrip $rawDataModal, array $data) : \ILIAS\UI\Component\Dropdown\Dropdown
    {
        $f = $this->dic->ui()->factory();
        
        return $f->dropdown()->standard([
            $f->button()->shy(
                $this->language->txt('tbl_action_raw_data'),
                '#'
            )->withOnClick($rawDataModal->getShowSignal())
        ])->withLabel($this->language->txt('actions'));
    }

    protected function getRawDataModal(array $data) : RoundTrip
    {
        $f = $this->dic->ui()->factory();
        
        return $f->modal()->roundtrip(
            'Raw Statement',
            $f->legacy('<pre>' . $data['statement'] . '</pre>')
        )->withCancelButtonLabel('close');
    }

    protected function getUsername(ilCmiXapiUser $cmixUser) : string
    {
        $ret = 'not found';
        try {
            $userObj = ilObjectFactory::getInstanceByObjId($cmixUser->getUsrId());
            $ret = $userObj->getFullname();
        } catch (Exception $e) {
            $ret = $this->language->txt('deleted_user');
        }
        return $ret;
    }
}
