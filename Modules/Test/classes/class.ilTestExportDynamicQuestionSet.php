<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestExport.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestExportDynamicQuestionSet extends ilTestExport
{
    protected function initXmlExport()
    {
    }

    protected function populateQuestionSetConfigXml(ilXmlWriter $xmlWriter)
    {
    }

    protected function getQuestionsQtiXml()
    {
        return '';
    }
    
    protected function getQuestionIds()
    {
        return array();
    }
}
