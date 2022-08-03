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

//use \ILIAS\UI\Component\Modal\RoundTrip;
/**
 * Class ilLTIConsumerScoringTableGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilLTIConsumerScoringTableGUI extends ilTable2GUI
{
    const TABLE_ID = 'cmix_scoring_table_';

    /**
     * @var bool
     */
    protected bool $isMultiActorReport;

    /**
     * @var bool
     */
    protected bool $hasOutcomeAccess;

    private \ilLTIConsumerScoringGUI $_parent;

    /**
     * ilLTIConsumerScoringTableGUI constructor.
     */
    public function __construct(ilLTIConsumerScoringGUI $a_parent_obj, string $a_parent_cmd, bool $isMultiActorReport, string $tableId, bool $hasOutcomeAccess)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->isMultiActorReport = $isMultiActorReport;
        
        $this->setId(self::TABLE_ID . $tableId);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->_parent = $a_parent_obj;

        $DIC->language()->loadLanguageModule('assessment');
        
        $this->setRowTemplate('tpl.lti_consumer_scoring_table_row.html', 'Modules/LTIConsumer');

        if ($tableId === 'highscore') {
            $this->setTitle(
                sprintf(
                    $DIC->language()->txt('toplist_top_n_results'),
                    $this->_parent->getObject()->getHighscoreTopNum()
                )
            );
        } else {
            $this->setTitle($DIC->language()->txt('toplist_your_result'));
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
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->addColumn($DIC->language()->txt('toplist_col_rank'));
        $this->addColumn($DIC->language()->txt('toplist_col_participant'));

        if ($this->_parent->getObject()->getHighscoreAchievedTS()) {
            $this->addColumn($DIC->language()->txt('toplist_col_achieved'));
        }

        if ($this->_parent->getObject()->getHighscorePercentage()) {
            $this->addColumn($DIC->language()->txt('toplist_col_percentage'));
        }

        if ($this->_parent->getObject()->getHighscoreWTime()) {
            $this->addColumn($DIC->language()->txt('toplist_col_wtime'));
        }

        $this->setEnableNumInfo(false);
        $this->setLimit($this->_parent->getObject()->getHighscoreTopNum());
    }

    protected function fillRow(array $a_set) : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->tpl->setVariable('SCORE_RANK', $a_set['rank']);

        $this->tpl->setCurrentBlock('personal');
        $this->tpl->setVariable('SCORE_USER', $this->getUsername($a_set));
        $this->tpl->parseCurrentBlock();

        if ($this->_parent->getObject()->getHighscoreAchievedTS()) {
            $this->tpl->setCurrentBlock('achieved');
            $this->tpl->setVariable('SCORE_ACHIEVED', $a_set['date']);
            $this->tpl->parseCurrentBlock();
        }


        if ($this->_parent->getObject()->getHighscorePercentage()) {
            $this->tpl->setCurrentBlock('percentage');
            $this->tpl->setVariable('SCORE_PERCENTAGE', (float) $a_set['score'] * 100);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->_parent->getObject()->getHighscoreWTime()) {
            $this->tpl->setCurrentBlock('wtime');
            $this->tpl->setVariable('SCORE_DURATION', $a_set['duration']);
            $this->tpl->parseCurrentBlock();
        }

        $highlight = $a_set['ilias_user_id'] == $DIC->user()->getId() ? 'tblrowmarked' : '';
        $this->tpl->setVariable('HIGHLIGHT', $highlight);
    }
    
    protected function getUsername(array $data) : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($this->hasOutcomeAccess) {
            $user = ilObjectFactory::getInstanceByObjId($data['ilias_user_id'], false);
            
            if ($user) {
                return $user->getFullname();
            }
            
            return $DIC->language()->txt('deleted_user');
        }
        
        return $data['user'];
    }
}
