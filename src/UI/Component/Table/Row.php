<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Table;

interface Row extends \ILIAS\UI\Component\Component
{
    public function getId() : string;

    /**
     * Refer to an Action by its id and disable it for this row/record only.
     */
    public function withDisabledAction(string $action_id, bool $disable = true) : Row;

    /**
     * @return <string, Column>
     */
    public function getColumns() : array;

    /**
     * @return <string, Action>
     */
    public function getActions() : array;

    public function getCellContent(string $col_id) : string;
}
