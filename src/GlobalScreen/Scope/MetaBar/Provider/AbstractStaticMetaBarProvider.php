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
namespace ILIAS\GlobalScreen\Scope\MetaBar\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationProviderInterface;
use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\MetaBarItemFactory;

/**
 * Interface AbstractStaticMetaBarProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractStaticMetaBarProvider extends AbstractProvider implements StaticMetaBarProvider
{
    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;
    /**
     * @var \ILIAS\GlobalScreen\Identification\IdentificationProviderInterface
     */
    protected $if;
    /**
     * @var \ILIAS\GlobalScreen\Scope\MetaBar\Factory\MetaBarItemFactory
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
