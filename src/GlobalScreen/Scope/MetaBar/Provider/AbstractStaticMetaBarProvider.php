<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationProviderInterface;
use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\MetaBarItemFactory;

/**
 * Interface AbstractStaticMetaBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractStaticMetaBarProvider extends AbstractProvider implements StaticMetaBarProvider
{

    /**
     * @var Container
     */
    protected $dic;
    /**
     * @var IdentificationProviderInterface
     */
    protected $if;
    /**
     * @var MetaBarItemFactory
     */
    protected $meta_bar;


    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->meta_bar = $this->globalScreen()->metaBar();
        $this->if = $this->globalScreen()->identification()->core($this);
    }
}
