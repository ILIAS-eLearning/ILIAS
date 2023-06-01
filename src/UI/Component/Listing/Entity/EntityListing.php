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

namespace ILIAS\UI\Component\Listing\Entity;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Entity\Entity;

/**
 * This is what an EntityListings looks like
 */
interface EntityListing extends Component
{
    /**
     * An Entity Listing is constructed with an instance of
     * \ILIAS\UI\Implementation\Component\Listing\Entity\EntityFactory.
     * The purpose of the factory is to "translate" your $data - be it an array
     * record of any sort or a repository, e.g. - into a respective Entity.
     */
    public function withData(mixed $data): self;
}
