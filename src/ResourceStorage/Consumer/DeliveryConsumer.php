<?php

namespace ILIAS\ResourceStorage\Consumer;

/**
 * Interface DeliveryConsumer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DeliveryConsumer
{

    /**
     * This runs the actual DeliveryConsumer. E.g. a DownloadConsumer will pass the
     * Stream of a Ressource to the HTTP-Service and download the file.
     */
    public function run() : void;

    /**
     * @param int $revision_number of a specific revision. otherwise the latest
     *                             will be chosen during run()
     * @return DeliveryConsumer
     */
    public function setRevisionNumber(int $revision_number) : DeliveryConsumer;
}
