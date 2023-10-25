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

namespace ILIAS\Glossary\Taxonomy;

use ILIAS\Taxonomy\DomainService;

class TaxonomyManager
{
    protected DomainService $tax_domain;
    protected \ilObjGlossary $glossary;

    public function __construct(
        DomainService $tax_domain,
        \ilObjGlossary $glossary
    ) {
        $this->glossary = $glossary;
        $this->tax_domain = $tax_domain;
    }

    public function showInEditing(): bool
    {
        if (!$this->tax_domain->isActivated($this->glossary->getId())) {
            return false;
        }
        $usage = $this->tax_domain->getUsageOfObject($this->glossary->getId());
        return count($usage) === 1;
    }
    public function showInPresentation(): bool
    {
        return $this->showInEditing() && $this->glossary->getShowTaxonomy();
    }

    public function getTaxonomyId(): int
    {
        $usage = $this->tax_domain->getUsageOfObject($this->glossary->getId());
        if (count($usage) === 1) {
            return (int) current($usage);
        }
        return 0;
    }
}
