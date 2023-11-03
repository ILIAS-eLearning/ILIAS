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

namespace ILIAS\MetaData\Elements;

use ILIAS\MetaData\Elements\Base\BaseSet;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;

class Set extends BaseSet implements SetInterface
{
    private RessourceIDInterface $ressource_id;

    public function __construct(
        RessourceIDInterface $ressource_id,
        ElementInterface $root
    ) {
        parent::__construct($root);
        $this->ressource_id = $ressource_id;
    }

    public function getRessourceID(): RessourceIDInterface
    {
        return $this->ressource_id;
    }

    public function getRoot(): ElementInterface
    {
        $root = parent::getRoot();
        if ($root instanceof ElementInterface) {
            return $root;
        }
        throw new \ilMDElementsException(
            'Metadata set has invalid root element.'
        );
    }
}
