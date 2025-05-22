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
use ILIAS\GlobalScreen\Scope\Footer\Provider\StaticFooterProvider;
use ILIAS\GlobalScreen\Scope\Footer\Factory\FooterItemFactory;
use ILIAS\GlobalScreen\Scope\Footer\Factory\Permanent;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class AbstractStaticFooterProvider extends AbstractProvider implements StaticFooterProvider
{
    protected IdentificationProviderInterface $id_factory;
    protected FooterItemFactory $item_factory;

    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->item_factory = $this->globalScreen()->footer();
        $this->id_factory = $this->globalScreen()->identification()->core($this);
    }

    public function getAdditionalTexts(): array
    {
        return [];
    }

    public function getPermanentURI(): ?Permanent
    {
        return null;
    }

}
