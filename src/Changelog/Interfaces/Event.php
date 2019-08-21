<?php

namespace ILIAS\Changelog\Interfaces;

/**
 * Interface Event
 *
 * @package ILIAS\Changelog\Interfaces
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface Event
{

    /**
     * Mandatory globally-unique event name
     *
     * @return String
     */
    public function getName() : String;


    /**
     * Mandatory component name
     *
     * @return String
     */
    public function getILIASComponent() : String;


    /**
     * May be 0 if no user is acting (e.g. for system initiated actions)
     *
     * @return int
     */
    public function getActorUserId() : int;


    /**
     * May be 0 if no user is subject of the action
     *
     * @return int
     */
    public function getSubjectUserId() : int;


    /**
     * May be 0 if no object is involved
     *
     * @return int
     */
    public function getSubjectObjId() : int;


    /**
     * May be empty
     *
     * @return array
     */
    public function getAdditionalData() : array;
}