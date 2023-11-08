<?php

declare(strict_types=1);

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

namespace ILIAS\Glossary\Presentation;

use ILIAS\Glossary\InternalDomainService;

/**
 * Manages presentation of glossary content
 * @author Thomas Famula <famula@leifos.de>
 */
class PresentationManager
{
    protected InternalDomainService $domain;
    protected PresentationSessionRepository $session_repo;
    protected \ilObjGlossary $glossary;
    protected int $user_id;

    public function __construct(
        InternalDomainService $domain_service,
        PresentationSessionRepository $session_repo,
        \ilObjGlossary $glossary,
        int $user_id
    ) {
        $this->session_repo = $session_repo;
        $this->glossary = $glossary;
        $this->user_id = $user_id;
        $this->domain = $domain_service;
    }

    public function setSessionPageLength(int $page_length): void
    {
        $this->session_repo->setPageLength($this->glossary->getRefId(), $page_length);
    }

    public function getSessionPageLength(): int
    {
        return $this->session_repo->getPageLength($this->glossary->getRefId());
    }

    public function setSessionLetter(string $letter): void
    {
        $this->session_repo->setLetter($this->glossary->getRefId(), $letter);
    }

    public function getSessionLetter(): string
    {
        return $this->session_repo->getLetter($this->glossary->getRefId());
    }
}
