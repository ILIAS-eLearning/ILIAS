<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use \ILIAS\UI\Component\Modal\RoundTrip;

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
    
    /**
     * @var bool
     */
    protected $isMultiActorReport;
    
    public function __construct($a_parent_obj, $a_parent_cmd, $isMultiActorReport)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->language()->loadLanguageModule('cmix');
        
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
    
    protected function initColumns()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $this->addColumn($DIC->language()->txt('tbl_statements_date'), 'date');
        
        if ($this->isMultiActorReport) {
            $this->addColumn($DIC->language()->txt('tbl_statements_actor'), 'actor');
        }

        $this->addColumn($DIC->language()->txt('tbl_statements_verb'), 'verb');
        $this->addColumn($DIC->language()->txt('tbl_statements_object'), 'object');

        $this->addColumn('', '', '1%');
    }
    
    public function initFilter()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($this->isMultiActorReport) {
            $ti = new ilTextInputGUI('User', "actor");
            $ti->setDataSource($DIC->ctrl()->getLinkTarget($this->parent_obj, 'asyncUserAutocomplete', '', true));
            $ti->setMaxLength(64);
            $ti->setSize(20);
            $this->addFilterItem($ti);
            $ti->readFromSession();
            $this->filter["actor"] = $ti->getValue();
        }
        
        $si = new ilSelectInputGUI('Used Verb', "verb");
        if (strtolower($this->ctrl->getCmdClass()) == "illticonsumerxapistatementsgui") {
            $si->setOptions(ilCmiXapiVerbList::getInstance()->getSelectOptions());
        } else { //xapi
            $verbs = $this->parent_obj->getVerbs(); // ToDo: Caching
            $si->setOptions(ilCmiXapiVerbList::getInstance()->getDynamicSelectOptions($verbs));
        }
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["verb"] = $si->getValue();
        $dp = new ilCmiXapiDateDurationInputGUI('Period', 'period');
        $dp->setShowTime(true);
        $this->addFilterItem($dp);
        $dp->readFromSession();
        $this->filter["period"] = $dp->getValue();
    }
    
    public function fillRow($data)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $r = $DIC->ui()->renderer();
        
        $data['rowkey'] = md5(serialize($data));
        
        $rawDataModal = $this->getRawDataModal($data);
        $actionsList = $this->getActionsList($rawDataModal, $data);
        
        $date = ilDatePresentation::formatDate(
            ilCmiXapiDateTime::fromXapiTimestamp($data['date'])
        );
        
        $this->tpl->setVariable('STMT_DATE', $date);
        
        if ($this->isMultiActorReport) {
            $actor = $data['actor'];
            if (empty($actor)) {
                $this->tpl->setVariable('STMT_ACTOR', 'user_not_found');
            } else {
                $this->tpl->setVariable('STMT_ACTOR', $this->getUsername($data['actor']));
            }
        }
        
        $this->tpl->setVariable('STMT_VERB', ilCmiXapiVerbList::getVerbTranslation(
            $DIC->language(),
            $data['verb_id']
        ));
        
        $this->tpl->setVariable('STMT_OBJECT', $data['object']);
        $this->tpl->setVariable('STMT_OBJECT_INFO', $data['object_info']);
        $this->tpl->setVariable('ACTIONS', $r->render($actionsList));
        $this->tpl->setVariable('RAW_DATA_MODAL', $r->render($rawDataModal));
    }
    
    protected function getActionsList(RoundTrip $rawDataModal, $data)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $f = $DIC->ui()->factory();
        
        $actions = $f->dropdown()->standard([
            $f->button()->shy(
                $DIC->language()->txt('tbl_action_raw_data'),
                '#'
            )->withOnClick($rawDataModal->getShowSignal())
        ])->withLabel($DIC->language()->txt('actions'));
        
        return $actions;
    }
    
    protected function getRawDataModal($data)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $f = $DIC->ui()->factory();
        
        $modal = $f->modal()->roundtrip(
            'Raw Statement',
            $f->legacy('<pre>' . $data['statement'] . '</pre>')
        )->withCancelButtonLabel('close');
        
        return $modal;
    }
    
    protected function getUsername(ilCmiXapiUser $cmixUser)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ret = 'not found';
        try {
            $userObj = ilObjectFactory::getInstanceByObjId($cmixUser->getUsrId());
            $ret = $userObj->getFullname();
        } catch (Exception $e) {
            $ret = $DIC->language()->txt('deleted_user');
        }
        return $ret;
    }
}
