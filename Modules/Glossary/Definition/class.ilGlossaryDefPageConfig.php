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
 * Glossary definition page configuration
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryDefPageConfig extends ilPageConfig
{
    public function init() : void
    {
        global $DIC;

        $ref_id = $DIC->glossary()->internal()->gui()->editing()->request()->getRefId();

        $this->setEnableKeywords(true);
        $this->setEnableInternalLinks(true);
        $this->setIntLinkHelpDefaultType("GlossaryItem");
        $this->setIntLinkHelpDefaultId($ref_id);
    }
}
