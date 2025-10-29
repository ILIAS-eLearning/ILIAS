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

namespace ILIAS\TestQuestionPool;

use Psr\Http\Message\ServerRequestInterface;

interface RequestDataCollectorInterface
{
    public function getRequest(): ServerRequestInterface;

    public function isset(string $key): bool;

    public function hasRefId(): bool;

    public function getRefId(): int;

    public function hasQuestionId(): bool;

    public function getQuestionId(): int;

    /**
     * @return array<string>
     */
    public function getIds(): array;

    public function raw(string $key): mixed;


    public function getParsedBody(): object|array|null;

    /**
     * @return array<string|int>
     */
    public function getPostKeys(): array;


    /**
     * @return array|string<int>
     */
    public function getMultiSelectionIds(string $key): array|string;
}
