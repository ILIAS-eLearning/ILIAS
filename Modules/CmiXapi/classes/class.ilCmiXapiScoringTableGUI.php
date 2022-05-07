<?php declare(strict_types=1);

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
 * Class ilCmiXapiScoringTableGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiScoringTableGUI extends ilTable2GUI
{
    const TABLE_ID = 'cmix_scoring_table_';
    
    protected bool $isMultiActorReport;

    private \ilCmiXapiScoringGUI $_parent;
    
    private bool $hasOutcomeAccess;

    private \ILIAS\DI\Container $dic;

    private ilLanguage $language;

    /**
     * ilCmiXapiScoringTableGUI constructor.
     */
    public function __construct(
        ilCmiXapiScoringGUI $a_parent_obj,
        string $a_parent_cmd,
        bool $isMultiActorReport,
        string $tableId,
        bool $hasOutcomeAccess
    ) {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $this->dic = $DIC;

        $this->isMultiActorReport = $isMultiActorReport;
        
        $this->setId(self::TABLE_ID . $tableId);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->_parent = $a_parent_obj;

        $DIC->language()->loadLanguageModule('assessment');
        $this->language = $DIC->language();
        $this->setRowTemplate('tpl.cmix_scoring_table_row.html', 'Modules/CmiXapi');

        if ($tableId === 'highscore') {
            $this->setTitle(
                sprintf($this->language->txt('toplist_top_n_results'), $this->_parent->object->getHighscoreTopNum())
            );
        } else {
            $this->setTitle($this->language->txt('toplist_your_result'));
        }

        $this->initColumns();

        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);
        $this->setMaxCount(0);
        $this->resetOffset();
        $this->setDefaultOrderField('rank');
        $this->setDefaultOrderDirection('asc');
        
        $this->hasOutcomeAccess = $hasOutcomeAccess;
    }

    protected function initColumns() : void
    {
        $this->addColumn($this->language->txt('toplist_col_rank'));
        $this->addColumn($this->language->txt('toplist_col_participant'));

        if ($this->_parent->object->getHighscoreAchievedTS()) {
            $this->addColumn($this->language->txt('toplist_col_achieved'));
        }

        if ($this->_parent->object->getHighscorePercentage()) {
            $this->addColumn($this->language->txt('toplist_col_percentage'));
        }

        if ($this->_parent->object->getHighscoreWTime()) {
            $this->addColumn($this->language->txt('toplist_col_wtime'));
        }

        $this->setEnableNumInfo(false);
        $this->setLimit($this->_parent->object->getHighscoreTopNum());
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('SCORE_RANK', $a_set['rank']);

        $this->tpl->setCurrentBlock('personal');
        $this->tpl->setVariable('SCORE_USER', $this->getUsername($a_set));
        $this->tpl->parseCurrentBlock();

        if ($this->_parent->object->getHighscoreAchievedTS()) {
            $this->tpl->setCurrentBlock('achieved');
            $this->tpl->setVariable('SCORE_ACHIEVED', $a_set['date']);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->_parent->object->getHighscorePercentage()) {
            $this->tpl->setCurrentBlock('percentage');
            $this->tpl->setVariable('SCORE_PERCENTAGE', (float) $a_set['score'] * 100);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->_parent->object->getHighscoreWTime()) {
            $this->tpl->setCurrentBlock('wtime');
            $this->tpl->setVariable('SCORE_DURATION', $a_set['duration']);
            $this->tpl->parseCurrentBlock();
        }

        $highlight = $a_set['ilias_user_id'] == $this->dic->user()->getId() ? 'tblrowmarked' : '';
        $this->tpl->setVariable('HIGHLIGHT', $highlight);
    }

    /**
     * @return string
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    protected function getUsername(array $data) : string
    {
        if ($this->hasOutcomeAccess) {
            $user = ilObjectFactory::getInstanceByObjId($data['ilias_user_id'], false);
            
            if ($user) {
                return $user->getFullname();
            }
            
            return $this->language->txt('deleted_user');
        }
        return "";
//        return $data['user'];
    }
}
