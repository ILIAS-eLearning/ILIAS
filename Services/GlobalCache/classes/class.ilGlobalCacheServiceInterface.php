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
 
/**
 * Class ilGlobalCacheService
 * Base class for all concrete cache implementations.
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.1
 */
interface ilGlobalCacheServiceInterface
{
    /**
     * @param mixed $serialized_value
     * @return mixed
     */
    public function unserialize($serialized_value);

    /**
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param mixed $serialized_value
     */
    public function set(string $key, $serialized_value, int $ttl = null) : bool;

    public function getServiceId() : string;

    public function setServiceId(string $service_id) : void;

    public function getComponent() : string;

    public function setComponent(string $component) : void;

    public function isActive() : bool;

    public function isInstallable() : bool;

    public function returnKey(string $key) : string;

    public function getInfo() : array;

    public function getInstallationFailureReason() : string;

    public function exists(string $key) : bool;

    public function delete(string $key) : bool;

    public function flush(bool $complete = false) : bool;

    public function setServiceType(int $service_type);

    public function getServiceType() : int;

    /**
     * Declare a key as valid. If the key is already known no action is taken.
     */
    public function setValid(string $key) : void;

    /**
     * Checks whether the cache key is valid or not.
     */
    public function isValid(string $key) : bool;
}
