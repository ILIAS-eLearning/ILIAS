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

use ILIAS\Setup;
use ILIAS\Setup\Environment;

/**
 * The curriculum page content element has been dropped in favour of the
 * learning map. Every Curriculum element on an intro-/extro-page of a learning
 * sequence is replaced by a LearningMap element, so the pages keep working and
 * show the map at the very same spot.
 *
 * Only the pages of a learning sequence are touched (parent_type lsoi/lsoe);
 * the element could not be inserted anywhere else.
 */
class LSOMigrateCurriculumToLearningMap implements Setup\Migration
{
    private const DEFAULT_AMOUNT_OF_STEPS = 1000;

    private const PARENT_TYPES = "('lsoi', 'lsoe')";
    private const CONDITION = "WHERE parent_type IN " . self::PARENT_TYPES . PHP_EOL
        . "AND content LIKE '%<Curriculum%'";

    /**
     * The dom serializes the element as <Curriculum/>, but the pattern also
     * covers attributes and a written out closing tag, so that no page can be
     * left behind (which would make the migration run forever).
     */
    private const SEARCH = '#<Curriculum\b[^>]*/>|<Curriculum\b[^>]*>.*?</Curriculum>#s';
    private const REPLACE = '<LearningMap/>';

    private ilDBInterface $db;

    public function getLabel(): string
    {
        return "Replace the curriculum on learning sequence pages by the learning map";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::DEFAULT_AMOUNT_OF_STEPS;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective(),
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $result = $this->db->query(
            "SELECT page_id, parent_type, lang, content FROM page_object" . PHP_EOL
            . self::CONDITION . PHP_EOL
            . "LIMIT 1"
        );
        $row = $this->db->fetchAssoc($result);
        if ($row === null) {
            return;
        }

        $content = preg_replace(self::SEARCH, self::REPLACE, (string) $row['content']);

        $this->db->manipulateF(
            "UPDATE page_object SET content = %s, render_md5 = %s, rendered_content = %s" . PHP_EOL
            . "WHERE page_id = %s AND parent_type = %s AND lang = %s",
            ['clob', 'text', 'clob', 'integer', 'text', 'text'],
            [$content, '', '', (int) $row['page_id'], $row['parent_type'], $row['lang']]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $result = $this->db->query(
            "SELECT count(page_id) AS cnt FROM page_object" . PHP_EOL . self::CONDITION
        );
        $row = $this->db->fetchAssoc($result);

        return (int) $row['cnt'];
    }
}
