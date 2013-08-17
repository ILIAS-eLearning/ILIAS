<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilTestExpressPage {

    public static function getNodeId($cls) {
        global $ilDB;
        $query = 'SELECT cid FROM ctrl_classfile WHERE class = %s';
        $types = array('text');
        $values = array($cls);
        $res = $ilDB->queryF($query, $types, $values);

        if ($res && $row = $ilDB->fetchAssoc($res)) {
            return $row['cid'];
        } else {
            throw new Exception('unknown ctrl class "' . $cls . '"');
        }
    }

    public static function getReturnToPageLink($q_id = null) {
        $params = array();
	$q_id = $q_id ? $q_id : $_REQUEST['q_id'];
        $params['baseClass'] = 'ilObjTestGUI';
	
	if ($_REQUEST['calling_test']) {
	    $params['ref_id'] = $_REQUEST['calling_test'];
	}
	else if ($_REQUEST['test_ref_id']) {
	    $params['ref_id'] = $_REQUEST['test_ref_id'];
	}
	else {
	    $params['ref_id'] = $_REQUEST['ref_id'];
	}
        $params['test_express_mode'] = 1;
        #$nodeParts = explode(':', $_REQUEST['cmdNode']);

        if ($_REQUEST['q_id']) {
            $params['cmd'] = 'edit';
            $params['q_id'] = $q_id ? $q_id : (isset($_REQUEST['prev_qid']) ? $_REQUEST['prev_qid'] : $_REQUEST['q_id']);
            $params['cmdClass'] = 'iltestexpresspageobjectgui';
            $params['cmdNode'] = ilTestExpressPage::getNodeId('ilobjtestgui') . ':' . ilTestExpressPage::getNodeId('iltestexpresspageobjectgui');
            #ref_id=44&cmd=post&cmdClass=iltestexpresspageobjectgui&cmdNode=6o:61&baseClass=ilObjTestGUI
        } else {
            $params['cmd'] = 'showQuestionsPerPage';
            $params['cmdNode'] = ilTestExpressPage::getNodeId('ilobjtestgui');
        }

        return 'ilias.php?' . http_build_query($params);
    }

}