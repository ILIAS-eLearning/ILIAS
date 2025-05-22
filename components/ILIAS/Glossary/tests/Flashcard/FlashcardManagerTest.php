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

namespace ILIAS\Glossary\Flashcard;

use PHPUnit\Framework\TestCase;
use ILIAS\Glossary;
use ILIAS\Glossary\InternalDomainService;
use ILIAS\Glossary\InternalRepoServiceInterface;


class FlashcardManagerTest extends TestCase
{
    protected function getManagerMock(): FlashcardManager
    {
        $domain = $this->getMockBuilder(InternalDomainService::class)->disableOriginalConstructor()->getMock();
        $repo = $this->getMockBuilder(InternalRepoServiceInterface::class)->disableOriginalConstructor()->getMock();

        return new class ($domain, $repo, 11, 99) extends FlashcardManager {
            public function __construct(
                InternalDomainService $domain_service,
                Glossary\InternalRepoServiceInterface $repo,
                int $glo_ref_id,
                int $user_id
            )
            {
                $this->domain = $domain_service;
                $this->glo_id = $glo_ref_id;
                $this->user_id = $user_id;
                $this->session_repo = new FlashcardSessionArrayRepository();
            }
        };
    }

    public function testSetSessionInitialTerms(): void
    {
        $manager = $this->getManagerMock();
        $terms = [123, 456, 789];

        $manager->setSessionInitialTerms(55, $terms);

        $this->assertSame($terms, $manager->getSessionInitialTerms(55));
    }

    public function testSetSessionTerms(): void
    {
        $manager = $this->getManagerMock();
        $terms = [321, 654, 987];

        $manager->setSessionTerms(77, $terms);

        $this->assertSame($terms, $manager->getSessionTerms(77));
    }
}
