<?php

namespace ILIAS\MainMenu\Storage\Consumer;

/**
 * Class DownloadConsumer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Consumer
{

    /**
     * This runs the actual Consumer. E.g. a DownloadConsumer will pass the
     * Stream of a Ressource to the HTTP-Service and download the file.
     */
    public function run() : void;
}