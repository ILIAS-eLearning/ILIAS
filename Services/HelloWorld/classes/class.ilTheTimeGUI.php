<?php

class ilTheTimeGUI {
    function executeCommand() {
        global $tpl;
//        $tpl->getStandardTemplate();
        $tpl->setContent(date('H:m'));
//        $tpl->show();
    }
}