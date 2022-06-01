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

/**
 * Glossary definition page object
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryDefPage extends ilPageObject
{
    public function getParentType() : string
    {
        return "gdf";
    }

    public function beforePageContentUpdate(ilPageContent $a_page_content) : void
    {
        if ($a_page_content->getType() == "par") {
            $glos = ilObjGlossary::lookupAutoGlossaries($this->getParentId());
            $a_page_content->autoLinkGlossaries($glos);
        }
    }
}
