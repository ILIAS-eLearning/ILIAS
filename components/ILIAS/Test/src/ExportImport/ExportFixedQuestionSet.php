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

namespace ILIAS\Test\ExportImport;

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package components\ILIAS/Test
 */
class ExportFixedQuestionSet extends Export
{
    protected function initXmlExport()
    {
    }

    protected function populateQuestionSetConfigXml(\ilXmlWriter $xml_writer)
    {
    }

    protected function getQuestionsQtiXml(): string
    {
        $question_qti_xml = '';

        foreach ($this->test_obj->questions as $question_id) {
            $question_qti_xml .= $this->getQuestionQtiXml($question_id);
        }

        return $question_qti_xml;
    }

    protected function getQuestionIds(): array
    {
        return $this->test_obj->questions;
    }
}
