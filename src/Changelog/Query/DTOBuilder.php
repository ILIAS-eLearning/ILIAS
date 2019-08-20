<?php

namespace ILIAS\Changelog\Query;

use ILIAS\Changelog\Infrastructure\AR\EventAR;

/**
 * Class DTOBuilder
 *
 * @package ILIAS\Changelog\Query
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class DTOBuilder
{

    use SingletonTrait;

    /**
     * @param EventAR[] $EventARs
     *
     * @return EventDTO[]
     */
    public function buildEventDTOsFromARs(array $EventARs) : array
    {
        $return = [];
        foreach ($EventARs as $EventAR) {
            $return[$EventAR->getEventId()->getId()] = $this->buildEventDTOFromAR($EventAR);
        }

        return $return;
    }


    /**
     * @param EventAR $EventAR
     *
     * @return EventDTO
     */
    public function buildEventDTOFromAR(EventAR $EventAR) : EventDTO
    {
        return new EventDTO(
            $EventAR->getId(),
            $EventAR->getEventId()->getId(),
            $EventAR->getEventName(),
            $EventAR->getActorUserId(),
            $EventAR->getSubjectUserId(),
            $EventAR->getSubjectObjId(),
            $EventAR->getILIASComponent(),
            $EventAR->getAdditionalData(),
            $EventAR->getTimestamp()
        );
    }
}