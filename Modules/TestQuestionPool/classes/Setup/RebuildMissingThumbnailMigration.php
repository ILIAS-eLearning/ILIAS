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

namespace ILIAS\TestQuestionPool\Setup;

use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;
use ILIAS\Setup\AdminInteraction;

class RebuildMissingThumbnailMigration implements Migration
{
    private \ilDBInterface $db;
    private AdminInteraction $io;
    private string $webdir;

    public function getLabel(): string
    {
        return 'Rebuild Missing Thumbnail Images';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 100;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);

        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
        $this->webdir = "{$ini->readVariable('server', 'absolute_path')}/{$ini->readVariable('clients', 'path')}/{$ini->readVariable('clients', 'default')}";
    }

    public function step(Environment $environment): void
    {
        $result = $this->db->query(
            '((SELECT sa.imagefile, q.obj_fi, q.question_type_fi, q.question_id' . PHP_EOL
            . 'FROM qpl_questions q JOIN qpl_qst_sc s on q.question_id = s.question_fi JOIN qpl_a_sc sa on q.question_id = sa.question_fi' . PHP_EOL
            . 'WHERE q.question_type_fi = 1 AND s.thumb_size IS NULL AND NOT q.obj_fi = 0 and not sa.imagefile = "" AND NOT ISNULL(sa.imagefile) ORDER BY q.question_id)' . PHP_EOL
            . 'UNION' . PHP_EOL
            . '(SELECT ma.imagefile, q.obj_fi, q.question_type_fi, q.question_id ' . PHP_EOL
            . 'FROM qpl_questions q JOIN qpl_qst_mc m on q.question_id = m.question_fi JOIN qpl_a_mc ma on q.question_id = ma.question_fi' . PHP_EOL
            . 'WHERE q.question_type_fi = 2 AND m.thumb_size IS NULL AND NOT q.obj_fi = 0 AND NOT ma.imagefile = "" AND NOT ISNULL(ma.imagefile) ORDER BY q.question_id)' . PHP_EOL
            . 'UNION' . PHP_EOL
            . '(SELECT ka.imagefile, q.obj_fi, q.question_type_fi, q.question_id' . PHP_EOL
            . 'FROM qpl_questions q JOIN qpl_qst_kprim k on q.question_id = k.question_fi JOIN qpl_a_kprim ka on q.question_id = ka.question_fi' . PHP_EOL
            . 'WHERE q.question_type_fi = 17 AND k.thumb_size IS NULL AND NOT q.obj_fi = 0 AND NOT ka.imagefile = "" AND NOT ISNULL(ka.imagefile) ORDER BY q.question_id))'
        );

        $previous_question_id = null;
        while (($row = $this->db->fetchObject($result)) !== null) {
            if ($previous_question_id !== null && $previous_question_id !== $row->question_id) {
                $this->updateThumbSize($row->question_id, $row->question_type_fi, $image_width);
            }
            $image_width = $this->copyImageToThumb($row->obj_fi, $row->question_id, $row->imagefile);
            $previous_question_id = $row->question_id;
            $previous_question_type_id = $row->question_type_fi;
        }
        $this->updateThumbSize($previous_question_id, $previous_question_type_id, $image_width);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $result = $this->db->query(
            'SELECT COUNT(*) cnt FROM' . PHP_EOL
            . '(SELECT q.question_id' . PHP_EOL
            . 'FROM qpl_questions q JOIN qpl_qst_sc s on q.question_id = s.question_fi JOIN qpl_a_sc sa on q.question_id = sa.question_fi' . PHP_EOL
            . 'WHERE q.question_type_fi = 1 AND s.thumb_size IS NULL AND NOT q.obj_fi = 0 and not sa.imagefile = "" AND NOT ISNULL(sa.imagefile)' . PHP_EOL
            . 'UNION' . PHP_EOL
            . 'SELECT q.question_id' . PHP_EOL
            . 'FROM qpl_questions q JOIN qpl_qst_mc m on q.question_id = m.question_fi JOIN qpl_a_mc ma on q.question_id = ma.question_fi' . PHP_EOL
            . 'WHERE q.question_type_fi = 2 AND m.thumb_size IS NULL AND NOT q.obj_fi = 0 AND NOT ma.imagefile = "" AND NOT ISNULL(ma.imagefile)' . PHP_EOL
            . 'UNION' . PHP_EOL
            . 'SELECT q.question_id' . PHP_EOL
            . 'FROM qpl_questions q JOIN qpl_qst_kprim k on q.question_id = k.question_fi JOIN qpl_a_kprim ka on q.question_id = ka.question_fi' . PHP_EOL
            . 'WHERE q.question_type_fi = 17 AND k.thumb_size IS NULL AND NOT q.obj_fi = 0 AND NOT ka.imagefile = "" AND NOT ISNULL(ka.imagefile)) cnt'
        );
        $row = $this->db->fetchAssoc($result);

        return (int) ($row['cnt'] ?? 0);
    }

    private function copyImageToThumb(
        int $object_id,
        int $question_id,
        string $image_file_name
    ): ?int {
        $filepath = "{$this->webdir}/assessment/{$object_id}/{$question_id}/images/{$image_file_name}";
        $thumbpath = "{$this->webdir}/assessment/{$object_id}/{$question_id}/images/thumb.{$image_file_name}";
        if (!file_exists($filepath)
            || filesize($filepath) <= 0
            || !($image_info = getimagesize($filepath))
            || !is_writable(dirname($thumbpath))) {
            $this->io->inform("\nWARNING: Could not create thumbnail for image {$filepath} of question {$question_id}");
            return 0;
        }

        copy($filepath, $thumbpath);
        return $image_info[0];
    }

    private function updateThumbSize(
        int $question_id,
        int $question_type_id,
        int $image_width
    ): void {
        $this->db->replace(
            match ($question_type_id) {
                1 => 'qpl_qst_sc',
                2 => 'qpl_qst_mc',
                17 => 'qpl_qst_kprim'
            },
            [
                'thumb_size' => ['integer', $image_width],
            ],
            [
                'question_fi' => ['integer', $question_id]
            ]
        );
    }
}
