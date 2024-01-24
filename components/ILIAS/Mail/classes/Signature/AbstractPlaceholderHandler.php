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

namespace ILIAS\Mail\Signature;

use ilLanguage;

abstract class AbstractPlaceholderHandler implements Placeholder
{
    protected ?Placeholder $next = null;

    public function __construct(protected ilLanguage $lng)
    {
    }

    public function getLabel(): string
    {
        return $this->lng->txt('mail_nacc_' . strtolower($this->getId()));
    }

    public function setNext(Placeholder $next): Placeholder
    {
        $this->next = $next;

        return $next;
    }

    public function getNext(): ?Placeholder
    {
        return $this->next;
    }

    /**
     * @param Signature $signature
     * @return string<string, string>
     */
    public function handle(Signature $signature): array
    {
        $placeholders = [];

        if ($this->next) {
            $placeholders = $this->next->handle($signature);
        }

        if ($signature->supports($this)) {
            $placeholders = $this->addPlaceholder($placeholders);
        }

        return $placeholders;
    }

    /**
     * @param Placeholder[] $placeholder
     * @return array<string, string>
     */
    abstract public function addPlaceholder(array $placeholder): array;
}
