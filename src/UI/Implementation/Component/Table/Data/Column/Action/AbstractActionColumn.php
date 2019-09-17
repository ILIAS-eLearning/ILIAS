<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Column\Action;

use ILIAS\UI\Component\Table\Data\Column\Action\ActionColumn as ActionColumnInterface;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Column;

/**
 * Class AbstractActionColumn
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Column\Action
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractActionColumn extends Column implements ActionColumnInterface
{

    /**
     * @inheritDoc
     */
    protected $sortable = false;
    /**
     * @inheritDoc
     */
    protected $selectable = false;
    /**
     * @inheritDoc
     */
    protected $exportable = false;


    /**
     * @inheritDoc
     */
    public function __construct(string $key, string $title)
    {
        parent::__construct($key, $title);

        global $DIC; // TODO: !!!
        $this->formater = new ActionFormater($DIC);
    }
}
