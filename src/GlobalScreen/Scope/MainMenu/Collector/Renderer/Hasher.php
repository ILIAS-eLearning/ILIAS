<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

/**
 * Class Hasher
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait Hasher
{

    /**
     * @param $string
     *
     * @return string
     */
    private function hash(string $string) : string
    {
        return bin2hex($string);
    }


    /**
     * @param $string
     *
     * @return string
     */
    private function unhash(string $string) : string
    {
        return hex2bin($string);
    }
}
