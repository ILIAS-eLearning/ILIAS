<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilTestExpressPage
{
    public static function getReturnToPageLink($q_id = null)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $q_id = $q_id ? $q_id : $_REQUEST['q_id'];
        $refId = self::fetchTargetRefIdParameter();

        if ($_REQUEST['q_id']) {
            $q_id = $q_id ? $q_id : (isset($_REQUEST['prev_qid']) ? $_REQUEST['prev_qid'] : $_REQUEST['q_id']);

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
        if ($_REQUEST['calling_test']) {
            return $_REQUEST['calling_test'];
        } elseif ($_REQUEST['test_ref_id']) {
            return $_REQUEST['test_ref_id'];
        }

        return $_REQUEST['ref_id'];
    }
}
