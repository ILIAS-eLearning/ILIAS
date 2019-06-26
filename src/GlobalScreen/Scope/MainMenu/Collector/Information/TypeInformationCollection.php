<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information;

/**
 * Class TypeInformationCollection
 *
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
    public function add(TypeInformation $information)
    {
        $this->type_informations[$information->getType()] = $information;
    }


    /**
     * @param string $type
     *
     * @return TypeInformation
     */
    public function get(string $type)
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
    public function append(TypeInformationCollection $collection)
    {
        foreach ($collection->getAll() as $type_information) {
            $this->add($type_information);
        }
    }
}
