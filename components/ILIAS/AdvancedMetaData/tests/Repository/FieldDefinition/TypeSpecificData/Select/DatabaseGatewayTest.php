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

namespace ILIAS\AdvancedMetaData\Repository\FieldDefinition\TypeSpecificData\Select;

use PHPUnit\Framework\TestCase;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\OptionTranslation;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\SelectSpecificData;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\NullSelectSpecificData;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\Option;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\NullOption;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\NullOptionTranslation;

class DatabaseGatewayTest extends TestCase
{
    protected const OPTION_CHANGED = 'option_changed';
    protected const OPTION_NOT_PERSISTED = 'option_not_persisted';
    protected const TRANSLATION_CHANGED = 'translation_changed';
    protected const TRANSLATION_NOT_PERSISTED = 'translation_not_persisted';

    /**
     * The state of the database is mocked using this array. Each entry is a row, each entry in
     * the rows corresponds to a column in adv_mdf_enum. Since mocks for data objects to test
     * manipulating the database are also created from modifications of this array, each entry
     * can also optionally have a 'status', which contains the flags defined in consts above
     * (to emulate the functionality of persistence-tracking-data).
     */
    protected const ORIGINAL_DATA = [
        ['field_id' => 1, 'lang_code' => 'de', 'idx' => 0, 'value' => '1val0de', 'position' => 1],
        ['field_id' => 1, 'lang_code' => 'en', 'idx' => 0, 'value' => '1val0en', 'position' => 1],
        ['field_id' => 1, 'lang_code' => 'de', 'idx' => 1, 'value' => '1val1de', 'position' => 0],
        ['field_id' => 1, 'lang_code' => 'en', 'idx' => 1, 'value' => '1val1en', 'position' => 0],
        ['field_id' => 1, 'lang_code' => 'de', 'idx' => 2, 'value' => '1val2de', 'position' => 2],
        ['field_id' => 1, 'lang_code' => 'en', 'idx' => 2, 'value' => '1val2en', 'position' => 2],
        ['field_id' => 2, 'lang_code' => 'de', 'idx' => 0, 'value' => '2val0de', 'position' => 1],
        ['field_id' => 2, 'lang_code' => 'en', 'idx' => 0, 'value' => '2val0en', 'position' => 1],
        ['field_id' => 2, 'lang_code' => 'de', 'idx' => 1, 'value' => '2val1de', 'position' => 0],
        ['field_id' => 2, 'lang_code' => 'en', 'idx' => 1, 'value' => '2val1en', 'position' => 0],
        ['field_id' => 2, 'lang_code' => 'de', 'idx' => 2, 'value' => '2val2de', 'position' => 2],
        ['field_id' => 2, 'lang_code' => 'en', 'idx' => 2, 'value' => '2val2en', 'position' => 2]
    ];

    protected function doArraysHaveSameEntriesIgnoreStatus(array $a, array $b): bool
    {
        foreach ($a as $key => $item) {
            if (isset($item['status'])) {
                unset($a[$key]['status']);
            }
        }
        foreach ($b as $key => $item) {
            if (isset($item['status'])) {
                unset($b[$key]['status']);
            }
        }

        if (count($a) !== count($b)) {
            return false;
        }

        foreach ($a as $item) {
            if (!in_array($item, $b)) {
                return false;
            }
        }

        return true;
    }

    protected function getDBGateway()
    {
        return new class (self::ORIGINAL_DATA) extends DatabaseGatewayImplementation {
            public function __construct(protected array $data)
            {
            }

            public function exposeData(): array
            {
                return $this->data;
            }

            protected function deleteOptionsExcept(int $field_id, int ...$keep_option_ids): void
            {
                foreach ($this->data as $key => $datum) {
                    if ($datum['field_id'] !== $field_id) {
                        continue;
                    }
                    if (!in_array($datum['idx'], $keep_option_ids)) {
                        unset($this->data[$key]);
                    }
                }
            }

            protected function deleteTranslationsOfOptionExcept(
                int $field_id,
                int $option_id,
                string ...$keep_languages
            ): void {
                foreach ($this->data as $key => $datum) {
                    if ($datum['idx'] !== $option_id || $datum['field_id'] !== $field_id) {
                        continue;
                    }
                    if (!in_array($datum['lang_code'], $keep_languages)) {
                        unset($this->data[$key]);
                    }
                }
            }

            protected function createTranslation(
                int $field_id,
                int $option_id,
                int $position,
                OptionTranslation $translation
            ): void {
                $this->data[] = [
                    'field_id' => $field_id,
                    'lang_code' => $translation->language(),
                    'idx' => $option_id,
                    'value' => $translation->getValue(),
                    'position' => $position
                ];
            }

            protected function updateTranslation(
                int $field_id,
                int $option_id,
                int $position,
                OptionTranslation $translation
            ): void {
                foreach ($this->data as $key => $datum) {
                    if (
                        $datum['idx'] !== $option_id ||
                        $datum['field_id'] !== $field_id ||
                        $datum['lang_code'] !== $translation->language()
                    ) {
                        continue;
                    }
                    $this->data[$key]['value'] = $translation->getValue();
                    $this->data[$key]['position'] = $position;
                }
            }

            protected function getNextOptionIDInField(int $field_id): int
            {
                $max_id = 0;
                foreach ($this->data as $datum) {
                    if ($datum['field_id'] !== $field_id) {
                        continue;
                    }
                    $max_id = max($max_id, $datum['idx']);
                }
                return $max_id + 1;
            }
        };
    }

    protected function getData(
        int $field_id,
        bool $is_persisted,
        bool $contains_changes,
        array $rows
    ): SelectSpecificData {
        $rows_by_option_id = [];
        foreach ($rows as $row) {
            if ($row['field_id'] !== $field_id) {
                continue;
            }
            $rows_by_option_id[$row['idx']][] = $row;
        }

        $options = [];
        foreach ($rows_by_option_id as $rows) {
            $options[] = $this->getOption($rows);
        }

        $field_id = $is_persisted ? $field_id : null;

        return new class ($field_id, $contains_changes, $options) extends NullSelectSpecificData {
            public function __construct(
                protected ?int $field_id,
                protected bool $contains_changes,
                protected array $options
            ) {
            }

            public function isPersisted(): bool
            {
                return !is_null($this->field_id);
            }

            public function containsChanges(): bool
            {
                return $this->contains_changes;
            }

            public function fieldID(): ?int
            {
                return $this->field_id;
            }

            public function getOptions(): \Generator
            {
                yield from $this->options;
            }
        };
    }

    protected function getOption(array $rows): Option
    {
        $first_row = $rows[0];
        $option_id = in_array(self::OPTION_NOT_PERSISTED, $first_row['status'] ?? []) ?
            null :
            $first_row['idx'];
        $position = $first_row['position'];
        $contains_changes = in_array(self::OPTION_CHANGED, $first_row['status'] ?? []);

        $translations = [];
        foreach ($rows as $row) {
            $translations[] = $this->getTranslation($row);
        }

        return new class ($option_id, $position, $contains_changes, $translations) extends NullOption {
            public function __construct(
                protected ?int $option_id,
                protected int $position,
                protected bool $contains_changes,
                protected array $translations
            ) {
            }

            public function isPersisted(): bool
            {
                return !is_null($this->option_id);
            }

            public function containsChanges(): bool
            {
                return $this->contains_changes;
            }

            public function optionID(): ?int
            {
                return $this->option_id;
            }

            public function getPosition(): int
            {
                return $this->position;
            }

            public function getTranslations(): \Generator
            {
                yield from $this->translations;
            }
        };
    }

    protected function getTranslation(array $row): OptionTranslation
    {
        $language = $row['lang_code'];
        $value = $row['value'];
        $is_persisted = !in_array(self::TRANSLATION_NOT_PERSISTED, $row['status'] ?? []);
        $contains_changes = in_array(self::TRANSLATION_CHANGED, $row['status'] ?? []);

        return new class ($language, $value, $is_persisted, $contains_changes) extends NullOptionTranslation {
            public function __construct(
                protected string $language,
                protected string $value,
                protected bool $is_persisted,
                protected bool $contains_changes
            ) {
            }

            public function isPersisted(): bool
            {
                return $this->is_persisted;
            }

            public function containsChanges(): bool
            {
                return $this->contains_changes;
            }

            public function language(): string
            {
                return $this->language;
            }

            public function getValue(): string
            {
                return $this->value;
            }
        };
    }

    public function testCreate(): void
    {
        $gateway = $this->getDBGateway();
        $status = [
            self::OPTION_CHANGED,
            self::OPTION_NOT_PERSISTED,
            self::TRANSLATION_CHANGED,
            self::TRANSLATION_NOT_PERSISTED
        ];
        $added_data_array = [
            ['field_id' => 78, 'lang_code' => 'de', 'idx' => 1, 'value' => '3val0de', 'position' => 1, 'status' => $status],
            ['field_id' => 78, 'lang_code' => 'en', 'idx' => 1, 'value' => '3val0en', 'position' => 1, 'status' => $status],
            ['field_id' => 78, 'lang_code' => 'de', 'idx' => 2, 'value' => '3val1de', 'position' => 0, 'status' => $status]
        ];
        $new_data_array = array_merge(self::ORIGINAL_DATA, $added_data_array);
        $new_data = $this->getData(
            78,
            false,
            true,
            $new_data_array
        );

        $gateway->create(78, $new_data);
        $this->assertTrue($this->doArraysHaveSameEntriesIgnoreStatus(
            $new_data_array,
            $gateway->exposeData()
        ));
    }

    public function testUpdateRemoveOption(): void
    {
        $gateway = $this->getDBGateway();
        $new_data_array = self::ORIGINAL_DATA;
        foreach ($new_data_array as $key => $item) {
            if ($item['field_id'] === 1 && $item['idx'] === 0) {
                unset($new_data_array[$key]);
            }
        }
        $new_data = $this->getData(
            1,
            true,
            true,
            $new_data_array
        );

        $gateway->update($new_data);
        $this->assertTrue($this->doArraysHaveSameEntriesIgnoreStatus(
            $new_data_array,
            $gateway->exposeData()
        ));
    }

    public function testUpdateAddOption(): void
    {
        $gateway = $this->getDBGateway();
        $status = [
            self::OPTION_CHANGED,
            self::OPTION_NOT_PERSISTED,
            self::TRANSLATION_CHANGED,
            self::TRANSLATION_NOT_PERSISTED
        ];
        $added_data_array = [
            ['field_id' => 1, 'lang_code' => 'de', 'idx' => 3, 'value' => '1val3de', 'position' => 3, 'status' => $status],
            ['field_id' => 1, 'lang_code' => 'en', 'idx' => 3, 'value' => '1val3en', 'position' => 3, 'status' => $status]
        ];
        $new_data_array = array_merge(self::ORIGINAL_DATA, $added_data_array);
        $new_data = $this->getData(
            1,
            true,
            true,
            $new_data_array
        );

        $gateway->update($new_data);
        $this->assertTrue($this->doArraysHaveSameEntriesIgnoreStatus(
            $new_data_array,
            $gateway->exposeData()
        ));
    }

    public function testUpdateChangeOptionPosition(): void
    {
        $gateway = $this->getDBGateway();
        $new_data_array = self::ORIGINAL_DATA;
        $status = [self::OPTION_CHANGED];
        $new_data_array[0]['position'] = 54;
        $new_data_array[0]['status'] = $status;
        $new_data_array[1]['position'] = 54;
        $new_data_array[1]['status'] = $status;
        $new_data = $this->getData(
            1,
            true,
            true,
            $new_data_array
        );

        $gateway->update($new_data);
        $this->assertTrue($this->doArraysHaveSameEntriesIgnoreStatus(
            $new_data_array,
            $gateway->exposeData()
        ));
    }

    public function testUpdateRemoveTranslation(): void
    {
        $gateway = $this->getDBGateway();
        $new_data_array = self::ORIGINAL_DATA;
        $status = [self::OPTION_CHANGED];
        foreach ($new_data_array as $key => $item) {
            if ($item['field_id'] !== 1 || $item['idx'] !== 0) {
                continue;
            }
            if ($item['lang_code'] === 'en') {
                unset($new_data_array[$key]);
                continue;
            }
            $new_data_array[$key]['status'] = $status;
        }
        $new_data = $this->getData(
            1,
            true,
            true,
            $new_data_array
        );

        $gateway->update($new_data);
        $this->assertTrue($this->doArraysHaveSameEntriesIgnoreStatus(
            $new_data_array,
            $gateway->exposeData()
        ));
    }

    public function testUpdateAddTranslation(): void
    {
        $gateway = $this->getDBGateway();
        $status = [
            self::OPTION_CHANGED,
            self::TRANSLATION_CHANGED,
            self::TRANSLATION_NOT_PERSISTED
        ];
        $new_data_array = self::ORIGINAL_DATA;
        foreach ($new_data_array as $key => $item) {
            if ($item['field_id'] === 1 || $item['idx'] === 1) {
                $new_data_array[$key]['status'] = [self::OPTION_CHANGED];
            }
        }
        $new_data_array[] =
            ['field_id' => 1, 'lang_code' => 'fr', 'idx' => 1, 'value' => '1val1fr', 'position' => 0, 'status' => $status];
        $new_data = $this->getData(
            1,
            true,
            true,
            $new_data_array
        );

        $gateway->update($new_data);
        $this->assertTrue($this->doArraysHaveSameEntriesIgnoreStatus(
            $new_data_array,
            $gateway->exposeData()
        ));
    }

    public function testUpdateChangeTranslationValue(): void
    {
        $gateway = $this->getDBGateway();
        $new_data_array = self::ORIGINAL_DATA;
        foreach ($new_data_array as $key => $item) {
            if ($item['field_id'] === 2 || $item['idx'] === 1) {
                $new_data_array[$key]['status'] = [self::OPTION_CHANGED];
            }
        }
        $new_data_array[4]['value'] = 'different value';
        $new_data_array[4]['status'] = [self::OPTION_CHANGED, self::TRANSLATION_CHANGED];
        $new_data = $this->getData(
            1,
            true,
            true,
            $new_data_array
        );

        $gateway->update($new_data);
        $this->assertTrue($this->doArraysHaveSameEntriesIgnoreStatus(
            $new_data_array,
            $gateway->exposeData()
        ));
    }
}
