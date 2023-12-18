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

namespace ILIAS\Glossary\Editing;

use ILIAS\Repository\BaseGUIRequest;

class EditingGUIRequest
{
    use BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getTermId(): int
    {
        return $this->int("term_id");
    }

    public function getTaxNode(): int
    {
        return $this->int("tax_node");
    }

    public function getNewType(): string
    {
        return $this->str("new_type");
    }

    public function getBaseClass(): string
    {
        return $this->str("baseClass");
    }

    public function getGlossaryRefId(): int
    {
        $id = $this->int("glo_ref_id");
        if ($id == 0) {
            $id = $this->int("root_id");
        }
        return $id;
    }

    public function getSearchRootExpand(): int
    {
        return $this->int("search_root_expand");
    }

    public function getGlossaryIdInModal(): int
    {
        return $this->getInterruptiveItemIds()[0];
    }

    public function getForeignGlossaryRefId(): int
    {
        return $this->int("fglo_ref_id");
    }

    public function getStyleId(): int
    {
        return $this->int("style_id");
    }

    public function getNewTerm(): string
    {
        return trim($this->str("new_term"));
    }

    public function getTermLanguage(): string
    {
        return $this->str("term_language");
    }

    /**
     * @return string[]
     */
    public function getFiles(): array
    {
        return $this->strArray("file");
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->intArray("id");
    }

    /**
     * @return int[]
     */
    public function getTaxNodes(): array
    {
        return $this->intArray("tax_node");
    }

    public function getSelectedGlossaryRefId(): int
    {
        return $this->int("sel_glo_ref_id");
    }

    /**
     * @return int[]
     */
    public function getInterruptiveItemIds(): array
    {
        return $this->intArray("interruptive_items");
    }

    public function getBulkCreationData(): string
    {
        return $this->str("bulk_data");
    }

    /**
     * @return string[]
     */
    protected function getTableIds(string $key): array
    {
        return $this->strArray($key);
    }

    protected function getTableAction(string $key): string
    {
        return $this->str($key);
    }

    public function getTableGlossaryAutoLinkAction(): string
    {
        return $this->getTableAction("glo_auto_link_table_action");
    }

    /**
     * @return string[]
     */
    public function getTableGlossaryAutoLinkIds(): array
    {
        return $this->getTableIds("glo_auto_link_table_glo_ids");
    }

    public function getTableGlossaryForeignTermAction(): string
    {
        return $this->getTableAction("glo_foreign_term_table_action");
    }

    /**
     * @return string[]
     */
    public function getTableGlossaryForeignTermIds(): array
    {
        return $this->getTableIds("glo_foreign_term_table_term_ids");
    }

    public function getTableGlossaryTermListAction(): string
    {
        return $this->getTableAction("glo_term_list_table_action");
    }

    /**
     * @return string[]
     */
    public function getTableGlossaryTermListIds(): array
    {
        return $this->getTableIds("glo_term_list_table_term_ids");
    }

    /**
     * @return int[]
     */
    public function getTermIdsInModal(): array
    {
        return $this->getInterruptiveItemIds();
    }
}
