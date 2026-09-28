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

namespace ILIAS\WOPI\Discovery;

use PHPUnit\Framework\TestCase;

/**
 * The content tab of a file object is a viewer without a way into edit mode, so the
 * embedded viewer of the WOPI client is preferred over its full one.
 *
 * @see https://mantis.ilias.de/view.php?id=48246
 */
final class ViewActionPreferenceTest extends TestCase
{
    /**
     * hasActionForSuffix() keeps a static cache keyed by suffix, so every test uses its
     * own suffix to stay independent of the execution order.
     */
    private function repositoryFor(string $suffix, ActionTarget ...$available): ActionDBRepository
    {
        $available_names = array_map(static fn(ActionTarget $t): string => $t->value, $available);

        $db = $this->createMock(\ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($v): string => '"' . $v . '"');

        $db->method('queryF')->willReturnCallback(
            function (string $query, array $types, array $values) use ($available_names) {
                $statement = $this->createMock(\ilDBStatement::class);

                // hasActionForSuffix() asks for a set of names and only counts the rows
                preg_match_all('/"([a-z]+)"/', $query, $matches);
                $asked_for = array_intersect($matches[1] ?? [], $available_names);
                $statement->method('numRows')->willReturn(count($asked_for));

                return $statement;
            }
        );

        $db->method('fetchAssoc')->willReturnCallback(
            function () use ($suffix, &$requested_name): ?array {
                return [
                    'id' => 1,
                    'name' => $requested_name,
                    'ext' => $suffix,
                    'urlsrc' => 'https://office.example.org/' . $requested_name,
                    'url_appendix' => null,
                    'target_text' => null,
                ];
            }
        );

        return new class ($db, $requested_name) extends ActionDBRepository {
            public function __construct(\ilDBInterface $db, private ?string &$requested_name)
            {
                parent::__construct($db);
            }

            public function getActionForSuffix(string $suffix, ActionTarget $action_target): ?Action
            {
                $this->requested_name = $action_target->value;
                return parent::getActionForSuffix($suffix, $action_target);
            }
        };
    }

    public function testEmbeddedViewerIsPreferred(): void
    {
        $repo = $this->repositoryFor('odt', ActionTarget::VIEW, ActionTarget::EMBED_VIEW);

        $action = $repo->getViewActionForSuffix('odt');

        $this->assertNotNull($action);
        $this->assertSame(ActionTarget::EMBED_VIEW->value, $action->getName());
    }

    public function testFallsBackToTheRegularViewAction(): void
    {
        $repo = $this->repositoryFor('docx', ActionTarget::VIEW);

        $action = $repo->getViewActionForSuffix('docx');

        $this->assertNotNull($action);
        $this->assertSame(ActionTarget::VIEW->value, $action->getName());
    }

    public function testNoViewActionAtAll(): void
    {
        $repo = $this->repositoryFor('xyz');

        $this->assertNull($repo->getViewActionForSuffix('xyz'));
    }
}
