<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestExport.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestExportFixedQuestionSet extends ilTestExport
{
    protected function initXmlExport()
    {
    }

    protected function populateQuestionSetConfigXml(ilXmlWriter $xmlWriter)
    {
    }
    
    protected function getQuestionsQtiXml()
    {
        $questionQtiXml = '';

        foreach ($this->test_obj->questions as $questionId) {
            $questionQtiXml .= $this->getQuestionQtiXml($questionId);
        }

        return $questionQtiXml;
    }
    
    protected function getQuestionIds()
    {
        return $this->test_obj->questions;
    }
}
