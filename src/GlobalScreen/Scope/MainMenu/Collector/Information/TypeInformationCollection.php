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
namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information;

/**
 * Class TypeInformationCollection
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class TypeInformationCollection
{
    /**
     * @var TypeInformation[]
     */
    protected $type_informations = [];

    /**
     * @param TypeInformation $information
     */
    public function add(TypeInformation $information) : void
    {
        $this->type_informations[$information->getType()] = $information;
    }

    public function get(string $type) : TypeInformation
    {
        if (isset($this->type_informations[$type]) && $this->type_informations[$type] instanceof TypeInformation) {
            return $this->type_informations[$type];
        }

        return new TypeInformation($type, $type, null);
    }

    /**
     * @return TypeInformation[]
     */
    public function getAll() : array
    {
        return $this->type_informations;
    }

    /**
     * @param TypeInformationCollection $collection
     */
    public function append(TypeInformationCollection $collection) : void
    {
        foreach ($collection->getAll() as $type_information) {
            $this->add($type_information);
        }
    }
}
