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

declare(strict_types=1);

namespace ILIAS\MetaData\OERExposer\OAIPMH\FlowControl;

use ILIAS\MetaData\OERExposer\OAIPMH\Requests\RequestInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Argument;
use ILIAS\MetaData\OERExposer\OAIPMH\DateHelper;

class TokenHandler implements TokenHandlerInterface
{
    use DateHelper;

    public function generateToken(
        int $offset,
        ?\DateTimeImmutable $from_date,
        ?\DateTimeImmutable $until_date
    ): string {
        /*
         * (Re-)setting the until date helps with
         * returning consistent results over multiple request if changes
         * have been made in the meantime.
         */
        $current_date = $this->getCurrentDate();
        if (is_null($until_date) || $until_date > $current_date) {
            $until_date = $current_date;
        }

        $token_parts = [$offset, $until_date->format('Y-m-d')];
        if (!is_null($from_date)) {
            $token_parts[] = $from_date->format('Y-m-d');
        }

        return $this->encodeFromArray($token_parts);
    }

    public function isTokenValid(string $token): bool
    {
        $token_parts = $this->decodeToArray($token);

        if (count($token_parts) < 2 || count($token_parts) > 3) {
            return false;
        }

        if (
            !isset($token_parts[0]) ||
            !is_int($token_parts[0]) ||
            $token_parts[0] < 0
        ) {
            return false;
        }

        if (
            !isset($token_parts[1]) ||
            !is_string($token_parts[1]) ||
            !$this->isStringValidAsDate($token_parts[1])
        ) {
            return false;
        }

        // last parameter is optional
        if (
            isset($token_parts[2]) &&
            (!is_string($token_parts[2]) || !$this->isStringValidAsDate($token_parts[2]))
        ) {
            return false;
        }

        return true;
    }

    public function appendArgumentsFromTokenToRequest(
        RequestInterface $request,
        string $token
    ): RequestInterface {
        $token_parts = $this->decodeToArray($token);

        if (isset($token_parts[1])) {
            $request = $request->withArgument(
                Argument::UNTIL_DATE,
                $token_parts[1]
            );
        }

        if (isset($token_parts[2])) {
            $request = $request->withArgument(
                Argument::FROM_DATE,
                $token_parts[2]
            );
        }

        return $request;
    }

    public function getOffsetFromToken(string $token): int
    {
        return (int) ($this->decodeToArray($token)[0] ?? 0);
    }

    protected function decodeToArray(string $string): array
    {
        $decoded = json_decode(base64_decode($string));
        if (is_array($decoded)) {
            return $decoded;
        }
        return [];
    }

    protected function encodeFromArray(array $array): string
    {
        return base64_encode(json_encode($array));
    }
}
