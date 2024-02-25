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

use ILIAS\Test\TestDIC;
use ILIAS\Test\RequestDataCollector;

class ilTestExpressPage
{
    private RequestDataCollector $testrequest;
    public function __construct()
    {
        $local_dic = TestDIC::dic();
        $this->testrequest = $local_dic['request_data_collector'];
    }

    public static function getReturnToPageLink(?int $q_id = null)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $request_data_collector = TestDIC::dic()['request_data_collector'];

        if ($q_id === null
            || $q_id === 0) {
            $q_id = $request_data_collector->int('prev_qid');
        }

        if ($q_id === 0) {
            $q_id = $request_data_collector->int('q_id');
        }

        $refId = self::fetchTargetRefIdParameter();

        if ($request_data_collector->raw('q_id')) {
            $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'test_express_mode', 1);
            $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'ref_id', $refId);
            $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'q_id', $q_id);

            return $ilCtrl->getLinkTargetByClass(
                array('ilobjtestgui', 'iltestexpresspageobjectgui'),
                'edit',
                '',
                false,
                false
            );
        }

        $ilCtrl->setParameterByClass('ilobjtestgui', 'test_express_mode', 1);
        $ilCtrl->setParameterByClass('ilobjtestgui', 'ref_id', $refId);

        return $ilCtrl->getLinkTargetByClass('ilobjtestgui', 'showQuestionsPerPage', '', false, false);
    }

    /**
     * @param $params
     * @return mixed
     */
    private static function fetchTargetRefIdParameter()
    {
        $request_data_collector = TestDIC::dic()['request_data_collector'];
        if ($request_data_collector->raw('calling_test')) {
            return $request_data_collector->raw('calling_test');
        }

        if ($request_data_collector->raw('test_ref_id')) {
            return $request_data_collector->raw('test_ref_id');
        }

        return $request_data_collector->raw('ref_id');
    }
}
