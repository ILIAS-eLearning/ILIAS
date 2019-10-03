<?php

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Data\Column\Formater;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Column\Formater\Formater;

/**
 * Class AbstractFormater
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Column\Formater
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractFormater implements Formater
{

    /**
     * @var Container
     */
    protected $dic;


    /**
     * AbstractFormater constructor
     *
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }
}
