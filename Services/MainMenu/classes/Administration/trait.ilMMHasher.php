<?php

/**
 * Class ilMMHasher
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ilMMHasher
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
