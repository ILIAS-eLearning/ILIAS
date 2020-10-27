<?php

namespace ILIAS\ResourceStorage\Consumer;

/**
 * Interface DeliveryConsumer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DeliveryConsumer
{

    /**
     * This runs the actual DeliveryConsumer. E.g. a DownloadConsumer will pass the
     * Stream of a Ressource to the HTTP-Service and download the file.
     */
    public function run() : void;
}
