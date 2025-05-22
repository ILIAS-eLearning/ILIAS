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
/**
* Lucene query input form gui
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*/
class ilLuceneQueryInputGUI extends ilTextInputGUI
{
    public function checkInput(): bool
    {
        $ok = parent::checkInput();

        $query = '';
        if ($this->http->wrapper()->post()->has($this->getPostVar())) {
            $query = $this->http->wrapper()->post()->retrieve(
                $this->getPostVar(),
                $this->refinery->kindlyTo()->string()
            );
        }
        if (!$ok or !strlen($query)) {
            return false;
        }
        try {
            ilLuceneQueryParser::validateQuery($query);
            return true;
        } catch (ilLuceneQueryParserException $e) {
            $this->setAlert($this->lng->txt($e->getMessage()));
            return false;
        }
    }
}
