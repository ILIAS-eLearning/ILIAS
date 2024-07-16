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

namespace ILIAS\FileDelivery\Token\Transport;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class URLSafeSplitPathTransport implements Transport
{
    public function __construct(private int $max_length = 128)
    {
    }

    public function prepareForTransport(string $compressed_token): string
    {
        $string = rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($compressed_token)), '=');
        // split string into chunks of 255 characters and separate them with a slash
        $string = chunk_split($string, $this->max_length, "/");

        return $string;
    }

    public function readFromTransport(string $compressed_token): string
    {
        // combine chunks
        $compressed_token = str_replace("/", "", $compressed_token);

        $string = base64_decode(str_replace(['-', '_'], ['+', '/'], $compressed_token . '=='));

        return $string;
    }

}
