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

class ilDclDetailedViewDefinitionConfig extends ilPageConfig
{
    public function init(): void
    {
        $this->setPreventHTMLUnmasking(true);
        $this->setEnableInternalLinks(false);
        $this->setEnableWikiLinks(false);
        $this->setEnableActivation(false);
    }

    public function getTextTemplates(): array
    {
        global $DIC;
        $tableview = new ilDclTableView($DIC->http()->wrapper()->query()->retrieve('tableview_id', $DIC->refinery()->kindlyTo()->int()));
        $placeholder = [];
        foreach (ilDclCache::getTableCache($tableview->getTableId())->getFields() as $p) {
            $placeholder[$p->getTitle()] = '[[' . $p->getTitle() . ']]';
        }
        return $placeholder;
    }

    public function getTextTemplatesDropdownCaption(): string
    {
        return $this->lng->txt('dcl_legend_placeholders');
    }
}
