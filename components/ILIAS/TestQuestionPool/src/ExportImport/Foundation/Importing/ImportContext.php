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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Importing;

/**
 * Immutable JSON-capable import context shared by question pool and test wizards.
 *
 * @phpstan-type SkillAssignmentResult array{failed: list<array{skill_id: int, tref_id: int, title: string, path: string}>, success: list<array{skill_id: int, tref_id: int, title: string, path: string}>}
 * @phpstan-type SkillThresholdResult array{failed: list<array{skill_base_id: int, skill_tref_id: int, skill_level_id: int, threshold: int}>, success: list<array{skill_base_id: int, skill_tref_id: int, skill_level_id: int, threshold: int}>}
 */
final class ImportContext
{
    private ?string $file_to_import = null;
    private ?string $component_import_file = null;
    private ?string $import_base_dir = null;
    private ?int $install_id = null;
    private ?string $legacy_qti_file = null;
    private ?string $legacy_xml_file = null;
    /** @var list<int> */
    private array $selectable_question_ids = [];
    /** @var list<int> */
    private array $selected_question_ids = [];
    private ?int $pool_obj_id = null;
    private ?int $test_obj_id = null;
    private ?int $test_ref_id = null;
    /** @var array<array-key, mixed>|null */
    private ?array $user_mappings = null;
    /** @var list<array<array-key, mixed>> */
    private array $resource_mappings = [];
    /** @var SkillAssignmentResult|null */
    private ?array $skill_assignments = null;
    /** @var SkillThresholdResult|null */
    private ?array $skill_thresholds = null;

    public function withFileToImport(string $path): self
    {
        $clone = clone $this;
        $clone->file_to_import = $path;
        return $clone;
    }

    public function hasFileToImport(): bool
    {
        return $this->file_to_import !== null;
    }

    public function fileToImport(): string
    {
        if($this->file_to_import === null) {
            throw new \LogicException('File to import is not set');
        }

        return $this->file_to_import;
    }

    public function withComponentImportFile(string $path): self
    {
        $clone = clone $this;
        $clone->component_import_file = $path;
        return $clone;
    }

    public function componentImportFile(): string
    {
        if($this->component_import_file === null) {
            throw new \LogicException('Component import file is not set');
        }

        return $this->component_import_file;
    }

    public function withImportBaseDir(string $path): self
    {
        $clone = clone $this;
        $clone->import_base_dir = $path;
        return $clone;
    }

    public function hasImportBaseDir(): bool
    {
        return $this->import_base_dir !== null;
    }

    public function importBaseDir(): string
    {
        if($this->import_base_dir === null) {
            throw new \LogicException('Import base dir is not set');
        }

        return $this->import_base_dir;
    }

    public function withInstallId(int $install_id): self
    {
        $clone = clone $this;
        $clone->install_id = $install_id;
        return $clone;
    }

    public function installId(): int
    {
        if($this->install_id === null) {
            throw new \LogicException('Install id is not set');
        }

        return $this->install_id;
    }

    public function withLegacyQtiFile(string $path): self
    {
        $clone = clone $this;
        $clone->legacy_qti_file = $path;
        return $clone;
    }

    public function legacyQtiFile(): string
    {
        if($this->legacy_qti_file === null) {
            throw new \LogicException('Legacy QTI file is not set');
        }

        return $this->legacy_qti_file;
    }

    public function withLegacyXmlFile(string $path): self
    {
        $clone = clone $this;
        $clone->legacy_xml_file = $path;
        return $clone;
    }

    public function legacyXmlFile(): string
    {
        if($this->legacy_xml_file === null) {
            throw new \LogicException('Legacy XML file is not set');
        }

        return $this->legacy_xml_file;
    }

    /**
     * @param list<int> $ids
     */
    public function withSelectableQuestionIds(array $ids): self
    {
        $clone = clone $this;
        $clone->selectable_question_ids = $ids;
        return $clone;
    }

    /**
     * @return list<int>
     */
    public function selectableQuestionIds(): array
    {
        return $this->selectable_question_ids;
    }

    /**
     * @param list<int> $ids
     */
    public function withSelectedQuestionIds(array $ids): self
    {
        $clone = clone $this;
        $clone->selected_question_ids = $ids;
        return $clone;
    }

    /**
     * @return list<int>
     */
    public function selectedQuestionIds(): array
    {
        return $this->selected_question_ids;
    }

    public function withPoolObjId(int $pool_obj_id): self
    {
        $clone = clone $this;
        $clone->pool_obj_id = $pool_obj_id;
        return $clone;
    }

    public function poolObjId(): int
    {
        if($this->pool_obj_id === null) {
            throw new \LogicException('Pool object id is not set');
        }

        return $this->pool_obj_id;
    }

    public function withTestObjId(int $test_obj_id): self
    {
        $clone = clone $this;
        $clone->test_obj_id = $test_obj_id;
        return $clone;
    }

    public function testObjId(): ?int
    {
        if($this->test_obj_id === null) {
            throw new \LogicException('Test object id is not set');
        }

        return $this->test_obj_id;
    }

    public function withTestRefId(int $test_ref_id): self
    {
        $clone = clone $this;
        $clone->test_ref_id = $test_ref_id;
        return $clone;
    }

    public function testRefId(): int
    {
        if($this->test_ref_id === null) {
            throw new \LogicException('Test reference id is not set');
        }

        return $this->test_ref_id;
    }

    /**
     * @param array<array-key, mixed> $mappings
     */
    public function withUserMappings(array $mappings): self
    {
        $clone = clone $this;
        $clone->user_mappings = $mappings;
        return $clone;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function userMappings(): array
    {
        if($this->user_mappings === null) {
            throw new \LogicException('User mappings are not set');
        }

        return $this->user_mappings;
    }

    /**
     * @param list<array<array-key, mixed>>|array<array-key, mixed> $mappings
     */
    public function withResourceMappings(array $mappings): self
    {
        $clone = clone $this;
        $clone->resource_mappings = $mappings;
        return $clone;
    }

    /**
     * @return list<array<array-key, mixed>>
     */
    public function resourceMappings(): array
    {
        return $this->resource_mappings;
    }

    /**
     * @param SkillAssignmentResult $result
     */
    public function withSkillAssignments(array $result): self
    {
        $clone = clone $this;
        $clone->skill_assignments = $result;
        return $clone;
    }

    /**
     * @return SkillAssignmentResult
     */
    public function skillAssignments(): array
    {
        if($this->skill_assignments === null) {
            throw new \LogicException('Skill assignments are not set');
        }

        return $this->skill_assignments;
    }

    /**
     * @param SkillThresholdResult $result
     */
    public function withSkillThresholds(array $result): self
    {
        $clone = clone $this;
        $clone->skill_thresholds = $result;
        return $clone;
    }

    /**
     * @return SkillThresholdResult
     */
    public function skillThresholds(): array
    {
        if($this->skill_thresholds === null) {
            throw new \LogicException('Skill thresholds are not set');
        }

        return $this->skill_thresholds;
    }

    public function isLegacyImport(): bool
    {
        return $this->legacy_qti_file !== null && $this->legacy_xml_file !== null;
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $context = new self();
        $context->file_to_import = $data['file_to_import'] ?? null;
        $context->component_import_file = $data['component_import_file'] ?? null;
        $context->import_base_dir = $data['import_base_dir'] ?? null;
        $context->install_id = $data['install_id'] ?? null;
        $context->legacy_qti_file = $data['legacy_qti_file'] ?? null;
        $context->legacy_xml_file = $data['legacy_xml_file'] ?? null;
        $context->selectable_question_ids = $data['selectable_question_ids'] ?? [];
        $context->selected_question_ids = $data['selected_question_ids'] ?? [];
        $context->pool_obj_id = $data['pool_obj_id'] ?? null;
        $context->test_obj_id = $data['test_obj_id'] ?? null;
        $context->test_ref_id = $data['test_ref_id'] ?? null;
        $context->user_mappings = $data['user_mappings'] ?? null;
        $context->resource_mappings = $data['resource_mappings'] ?? [];
        $context->skill_assignments = $data['skill_assignments'] ?? null;
        $context->skill_thresholds = $data['skill_thresholds'] ?? null;
        return $context;
    }

    public function toJson(): string
    {
        $payload = [
            'file_to_import' => $this->file_to_import,
            'component_import_file' => $this->component_import_file,
            'import_base_dir' => $this->import_base_dir,
            'install_id' => $this->install_id,
            'legacy_qti_file' => $this->legacy_qti_file,
            'legacy_xml_file' => $this->legacy_xml_file,
            'selectable_question_ids' => $this->selectable_question_ids,
            'selected_question_ids' => $this->selected_question_ids,
            'pool_obj_id' => $this->pool_obj_id,
            'test_obj_id' => $this->test_obj_id,
            'test_ref_id' => $this->test_ref_id,
            'user_mappings' => $this->user_mappings,
            'resource_mappings' => $this->resource_mappings,
            'skill_assignments' => $this->skill_assignments,
            'skill_thresholds' => $this->skill_thresholds,
        ];

        return json_encode($payload, JSON_THROW_ON_ERROR);
    }
}
