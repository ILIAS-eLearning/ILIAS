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

use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\RequestDataCollector;

/**
 * abstract parent class for concrete question hint GUI classes
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Grégory Saive <gsaive@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
abstract class ilAssQuestionHintAbstractGUI
{
    protected RequestDataCollector $request;
    protected ?assQuestionGUI $question_gui = null;
    protected ?assQuestion $question_obj = null;
    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;

    /**
     * Constructor
     *
     * @access	public
     * @param	assQuestionGUI	$question_gui
     */
    public function __construct(assQuestionGUI $question_gui)
    {
        global $DIC;
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC['lng'];
        $this->ctrl = $DIC['ilCtrl'];

        $local_dic = QuestionPoolDIC::dic();
        $this->request = $local_dic['request_data_collector'];

        $this->question_gui = $question_gui;
        $this->question_obj = $question_gui->getObject();
    }
}
