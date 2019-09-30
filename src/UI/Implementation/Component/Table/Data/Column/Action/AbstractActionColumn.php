<?php

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Data\Column\Action;

use ILIAS\DI\Container;
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
    public function __construct(Container $dic, string $key, string $title)
    {
        parent::__construct($dic, $key, $title);

        $this->formater = new ActionFormater($this->dic);
    }
}
