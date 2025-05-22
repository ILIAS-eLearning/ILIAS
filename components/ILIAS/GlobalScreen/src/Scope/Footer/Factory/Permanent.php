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

namespace ILIAS\GlobalScreen\Scope\Footer\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Permanent extends AbstractBaseItem implements hasAction, hasTitle
{
    use hasTitleTrait;
    use hasActionTrait;

    public function __construct(
        IdentificationInterface $provider_identification,
        string $title,
        private URI $uri
    ) {
        parent::__construct($provider_identification);
        $this->title = $title;
    }

    public function getURI(): URI
    {
        return $this->uri;
    }

    public function withPermanentLink(URI $link): self
    {
        $clone = clone $this;
        $clone->uri = $link;
        return $clone;
    }

    public function isTop(): bool
    {
        return false;
    }

}
