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
class URLSafeTransport implements Transport
{
    public function prepareForTransport(string $compressed_token): string
    {
        return rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($compressed_token)), '=');
    }

    public function readFromTransport(string $compressed_token): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $compressed_token . '=='));
    }
}
