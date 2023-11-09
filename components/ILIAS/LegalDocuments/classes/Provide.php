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

namespace ILIAS\LegalDocuments;

use Exception;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\Provide\ProvidePublicPage;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use ILIAS\LegalDocuments\Provide\ProvideHistory;
use ILIAS\LegalDocuments\Provide\ProvideWithdrawal;

class Provide
{
    public function __construct(
        private readonly string $id,
        private readonly Internal $internal,
        private readonly Container $container,
        private readonly string $document_key = 'document'
    ) {
    }

    public function withdrawal(): ProvideWithdrawal
    {
        if (null === $this->internal->get('withdraw', $this->id)) {
            throw new Exception('No withdrawal process defined for: ' . $this->id);
        }
        return new ProvideWithdrawal($this->id, $this->container->ctrl(), $this->container['ilAuthSession']);
    }

    public function publicPage(): ProvidePublicPage
    {
        if (null === $this->internal->get('public-page', $this->id)) {
            throw new Exception('No agreement defined for: ' . $this->id);
        }
        return new ProvidePublicPage($this->id, $this->container->ctrl());
    }

    public function document(): ProvideDocument
    {
        return $this->internal->get($this->document_key, $this->id) ?? $this->error('No documents defined for: ' . $this->id);
    }

    public function history(): ProvideHistory
    {
        return $this->internal->get('history', $this->id) ?? $this->error('No history defined for: ' . $this->id);
    }

    public function allowEditing(): self
    {
        return new self($this->id, $this->internal, $this->container, 'writable-document');
    }

    public function id(): string
    {
        return $this->id;
    }

    private function error(string $message): void
    {
        throw new Exception($message);
    }
}
