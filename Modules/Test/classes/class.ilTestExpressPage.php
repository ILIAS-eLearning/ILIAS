<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilTestExpressPage
{
    public function __construct()
    {
        global $DIC;
        $this->testrequest = $DIC->test()->internal()->request();
    }

    public static function getReturnToPageLink($q_id = null)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $q_id = $q_id ?: $DIC->test()->internal()->request()->raw('q_id');
        $refId = self::fetchTargetRefIdParameter();

        if ($DIC->test()->internal()->request()->raw('q_id')) {
            $q_id = $q_id ?: ($DIC->test()->internal()->request()->raw('prev_qid') ?? $DIC->test()->internal()->request()->raw('q_id'));

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
        global $DIC;
        if ($DIC->test()->internal()->request()->raw('calling_test')) {
            return $DIC->test()->internal()->request()->raw('calling_test');
        } elseif ($DIC->test()->internal()->request()->raw('test_ref_id')) {
            return $DIC->test()->internal()->request()->raw('test_ref_id');
        }

        return $DIC->test()->internal()->request()->raw('ref_id');
    }
}
