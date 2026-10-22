# Learning Sequence Conditions

This directory contains the condition system used by Learning Sequences.

A **condition** is a PHP class that is contributed to the Learning Sequence component and attached to a Learning Sequence item:

- an **input condition** decides whether a learner may enter an item
- an **output condition** decides whether an item contributes some state after it has been visited or completed

If you are new to this code base, the most important thing to understand is that a condition is **self-contained**:

1. the class declares its own persistence needs
2. the class declares its own configuration UI
3. the class evaluates itself at runtime
4. input conditions also own their own static misconfiguration reporting

Real examples in this component:

- simple output condition: `OutputCondition/AlwaysOutputCondition/AlwaysOutputCondition.php`
- configured output condition: `OutputCondition/PointsOutputCondition/PointsOutputCondition.php`
- simple input condition: `InputCondition/LearningProgressInputCondition/LearningProgressInputAwareCondition.php`
- complex input condition: `InputCondition/PointsInputCondition/PointsInputCondition.php`
- complex subtype-based input condition: `InputCondition/LogicGateInputCondition/LogicGateInputAwareCondition.php`

## Table of contents

- [1. How registration works](#1-how-registration-works)
- [2. The base class contract](#2-the-base-class-contract)
- [3. Naming conventions](#3-naming-conventions)
- [4. Minimal output condition](#4-minimal-output-condition)
- [5. Configured output condition](#5-configured-output-condition)
- [6. How the configuration GUI actually works](#6-how-the-configuration-gui-actually-works)
- [7. Creating an input condition](#7-creating-an-input-condition)
- [8. Navigation-aware input conditions](#8-navigation-aware-input-conditions)
- [9. Subtypes](#9-subtypes)
- [10. Static misconfiguration: tag and popover support](#10-static-misconfiguration-tag-and-popover-support)
- [11. Accrued-value conditions](#11-accrued-value-conditions)
- [12. Import/export behavior](#12-importexport-behavior)
- [13. Recommended implementation checklist](#13-recommended-implementation-checklist)
- [14. Which built-in class should I copy from?](#14-which-built-in-class-should-i-copy-from)

## 1. How registration works

Condition classes are contributed through the component bootstrap mechanism. Every condition type is a
`ConditionType` contribution, usually a `ConditionTypeDefinition` naming the condition class:

```php
// in the init() method of your component (e.g. LearningSequence.php)
$contribute[\ILIAS\LearningSequence\Content\Condition\ConditionType::class] = static fn() =>
    new \ILIAS\LearningSequence\Content\Condition\ConditionTypeDefinition(
        \Your\Namespace\YourCondition::class
    );
```

The `ConditionTypeRegistry` seeks all contributions and implements `ConditionTypes`, which is available as
`$DIC[ConditionTypes::class]` in the legacy environment.

- A contributed class that implements `InputConditionInterface` is treated as an input condition.
- A contributed class that implements `OutputConditionInterface` is treated as an output condition.

The setup objective `Setup/class.ilLearningSequenceConditionsSyncedObjective.php` receives the same
`ConditionTypes` and does three things:

1. ensures the generic tables `lso_condition_types` and `lso_conditions` exist
2. registers all contributed condition classes in `lso_condition_types`
3. creates the condition-specific `lso_c_*` tables returned by `YourCondition::migrate()`

The generic table layout is:

- `lso_condition_types`: maps contributed condition names to a `type_id`
- `lso_conditions`: one generic row per attached condition with `condition_id`, `lso_ref_id`, `obj_ref_id`, `type_id`

Your custom payload goes into one or more dedicated `lso_c_*` tables keyed by `condition_id`.

## 2. The base class contract

Every condition extends `AbstractCondition` and must implement:

```php
abstract public function check(): bool;
```

Important base hooks:

- `public static function migrate(): array`  
  Return `TableDefinition[]` for your own tables.
- `public function getAdditionalForm(): ?FormStandard`  
  Return a configuration form, or `null` if no configuration is needed.
- `public function applyAdditionalFormData(array $data): void`  
  Persist validated form data into object properties before `create()` / `edit()`.
- `protected function createConditionData(int $condition_id): void`
- `protected function editConditionData(int $condition_id): void`
- `protected function deleteConditionData(int $condition_id): void`
- `protected function requiresConfiguration(): bool`  
  Return `true` if creation must go through the configuration GUI first.
- `public function allowMultipleConditionsOfSameType(): bool`  
  Return `false` if one instance per item is enough.
- `public function supportsDuplicateCheck(): bool` and `public function isDuplicateOf(AbstractCondition $other): bool`  
  Return `true` from `supportsDuplicateCheck()` to enable the check, and from `isDuplicateOf()` if `$other` has the same configuration. `create()` and `edit()` then refuse to save
  and add the `condition_already_exists` validation message. Only conditions of the same type on the
  same item in the same Learning Sequence are compared; the condition being edited is skipped.

Useful optional hooks:

- `public function setupSteps(): array` for subtype menus or custom add flows
- `public function getAdditionalDisplayInformation(): string`
- `public function getAdditionalDisplayObjectTitles(): array`

Those last two are shown in the **Manage Conditions** table.

## 3. Naming conventions

There are three different names involved:

1. **Class name**  
   Example: `PointsInputCondition`
2. **Condition type name**  
   Derived from the class name by removing the `Condition` suffix, e.g. `PointsInput`  
   This is what gets stored in `lso_condition_types.condition_name`.
3. **UI/language key name**  
   The `protected const NAME` from your class, e.g. `points_input`  
   This is used for labels like `$lng->txt($condition->getName())`.

So make sure you add language entries for:

- your `NAME`
- field labels / bylines
- validation or exception messages
- static misconfiguration texts if you provide them

## 4. Minimal output condition

If your output condition has no custom payload and no configuration form, it can be very small.

```php
<?php

declare(strict_types=1);

namespace ILIAS\LearningSequence\Content\Condition\OutputCondition\ReadyOutputCondition;

use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\OutputConditionInterface;

final class ReadyOutputCondition extends AbstractCondition implements OutputConditionInterface
{
    protected const string NAME = 'ready_output';

    public function check(): bool
    {
        return true;
    }

    public static function migrate(): array
    {
        return [];
    }

    public function allowMultipleConditionsOfSameType(): bool
    {
        return false;
    }
}
```

Behavior:

- the add-condition drilldown shows the condition automatically
- clicking it calls `createCondition()` directly
- `AbstractCondition::create()` writes the generic row to `lso_conditions`
- no extra table is needed
- the condition is not editable, because `getAdditionalForm()` returns `null`

This is the right model for conditions like `AlwaysOutputCondition`.

## 5. Configured output condition

If your output condition needs its own payload, declare its table(s) in `migrate()` and implement the persistence hooks.

This is the exact pattern used by `PointsOutputCondition`.

```php
<?php

declare(strict_types=1);

namespace ILIAS\LearningSequence\Content\Condition\OutputCondition\ScoreOutputCondition;

use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\OutputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\TableDefinition;
use ILIAS\UI\Component\Input\Container\Form\Standard as FormStandard;
use LogicException;

final class ScoreOutputCondition extends AbstractCondition implements OutputConditionInterface
{
    protected const string NAME = 'score_output';
    private const string SETTINGS_TABLE = 'lso_c_score_output';
    private const string SCORE_FIELD = 'score';

    private ?int $score = null;

    public static function migrate(): array
    {
        return [
            new TableDefinition(
                tableName: self::SETTINGS_TABLE,
                fields: [
                    'condition_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                    self::SCORE_FIELD => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                ],
                primaryKeys: ['condition_id']
            )
        ];
    }

    public function check(): bool
    {
        return true;
    }

    protected function requiresConfiguration(): bool
    {
        return true;
    }

    public function getAdditionalForm(): FormStandard
    {
        $input = $this->ui_factory->input()->field()->numeric(
            $this->lang->txt('score_output'),
            $this->lang->txt('score_output_byline')
        )->withRequired(true);

        if ($this->condition_id !== null) {
            $input = $input->withValue($this->getScore());
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->buildUrl(self::CREATE_COMMAND, true)->__toString(),
            [$input]
        );
    }

    public function applyAdditionalFormData(array $data): void
    {
        $score = array_shift($data);
        if (is_array($score) || !is_numeric($score)) {
            throw new LogicException($this->lang->txt('lso_exception_score_invalid'));
        }

        $this->score = (int) $score;
    }

    protected function createConditionData(int $condition_id): void
    {
        $this->getDatabase()->insert(self::SETTINGS_TABLE, [
            'condition_id' => ['integer', $condition_id],
            self::SCORE_FIELD => ['integer', $this->requireScore()],
        ]);
    }

    protected function editConditionData(int $condition_id): void
    {
        $this->getDatabase()->update(
            self::SETTINGS_TABLE,
            [self::SCORE_FIELD => ['integer', $this->requireScore()]],
            ['condition_id' => ['integer', $condition_id]]
        );
    }

    protected function deleteConditionData(int $condition_id): void
    {
        $this->getDatabase()->manipulateF(
            'DELETE FROM ' . self::SETTINGS_TABLE . ' WHERE condition_id = %s',
            ['integer'],
            [$condition_id]
        );
    }

    private function getScore(): int
    {
        // load from DB lazily when editing or checking
    }

    private function requireScore(): int
    {
        if ($this->score === null) {
            throw new LogicException($this->lang->txt('lso_exception_score_not_set'));
        }

        return $this->score;
    }
}
```

Important details:

- `condition_id` is the foreign key from `lso_conditions` into your own table.
- `create()` always inserts the generic row first, then calls `createConditionData($condition_id)`.
- `edit()` updates the generic row, then calls `editConditionData($condition_id)`.
- `delete()` calls `deleteConditionData($condition_id)` before removing the generic row.

## 6. How the configuration GUI actually works

The GUI flow is:

1. `ilObjLearningSequenceConditionsGUI` builds the add-condition drilldown
2. each contributed condition adds one or more links via `setupSteps()`
3. `AbstractCondition::buildStep()` routes either to:
   - `createCondition` directly, or
   - `configure` first, if `requiresConfiguration()` is `true`
4. `ilObjLearningSequenceConditionConfigurationGUI` renders `getAdditionalForm()`
5. on submit it calls:
   - `withRequest($request)`
   - `getData()`
   - `applyAdditionalFormData($data)`
   - `create()` or `edit()`

Two practical rules follow from this:

### Rule A: use `requiresConfiguration()` for any non-trivial condition

If you need user input, return `true`.

### Rule B: preload values when `$this->condition_id !== null`

The same form is used for edit mode, so your getters must be able to read existing values from the database.

The current implementations usually parse form data by array position because the form is created from a sequential field list:

```php
return $this->ui_factory->input()->container()->form()->standard(
    $this->buildUrl(self::CREATE_COMMAND, true)->__toString(),
    [$fieldA, $fieldB]
);
```

That means `applyAdditionalFormData()` commonly reads `$data[0]`, `$data[1]`, or `array_shift($data)`.

## 7. Creating an input condition

An input condition usually does more than an output condition because it may need to:

- reference other LSO items
- affect adaptive navigation
- contribute static misconfiguration warnings

A typical referenced-item input condition implements:

- `InputConditionInterface`
- optionally `InputConditionNavigationAwareInterface`
- optionally `SubtypeAwareInterface`
- optionally `AccruedValueInputConditionInterface`

### Example: single-target input condition

This example follows the same pattern as `LearningProgressInputAwareCondition`.

```php
<?php

declare(strict_types=1);

namespace ILIAS\LearningSequence\Content\Condition\InputCondition\CompletedItemInputCondition;

use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionNavigationAwareInterface;
use ILIAS\LearningSequence\Content\Condition\LSOObjectPicker;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssue;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssueDetail;
use ILIAS\LearningSequence\Content\Condition\TableDefinition;
use ILIAS\UI\Component\Input\Container\Form\Standard as FormStandard;
use LogicException;

final class CompletedItemInputCondition extends AbstractCondition implements
    InputConditionInterface,
    InputConditionNavigationAwareInterface
{
    protected const string NAME = 'completed_item_input';
    private const string SETTINGS_TABLE = 'lso_c_completed_item_input';

    private ?int $target_ref_id = null;

    public static function migrate(): array
    {
        return [
            new TableDefinition(
                tableName: self::SETTINGS_TABLE,
                fields: [
                    'condition_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                    'item_ref_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                ],
                primaryKeys: ['condition_id']
            )
        ];
    }

    protected function requiresConfiguration(): bool
    {
        return true;
    }

    public function getAdditionalForm(): FormStandard
    {
        $picker = new LSOObjectPicker((int) $this->lso_ref_id, (int) $this->getObjRefId())->getPicker(
            $this->lang->txt('lso_condition_simple_choice_target'),
            false
        );

        if ($this->condition_id !== null) {
            $picker = $picker->withValue((string) $this->getTargetRefId());
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->buildUrl(self::CREATE_COMMAND, true)->__toString(),
            [$picker]
        );
    }

    public function applyAdditionalFormData(array $data): void
    {
        $target_ref_ids = array_shift($data);
        if (
            !is_array($target_ref_ids)
            || count($target_ref_ids) !== 1
            || !isset($target_ref_ids[0])
            || $target_ref_ids[0] === ''
        ) {
            throw new LogicException($this->lang->txt('lso_exception_target_ref_id_invalid'));
        }

        $this->target_ref_id = (int) $target_ref_ids[0];
    }

    public function check(): bool
    {
        // implement the learner-specific runtime check here
        return true;
    }

    public function getNavigationMode(): string
    {
        return InputConditionNavigationAwareInterface::NAVIGATION_MODE_EDGE;
    }

    public function getNavigationSourceRefIds(): array
    {
        return [$this->getTargetRefId()];
    }

    protected function createConditionData(int $condition_id): void
    {
        $this->getDatabase()->insert(self::SETTINGS_TABLE, [
            'condition_id' => ['integer', $condition_id],
            'item_ref_id' => ['integer', $this->requireTargetRefId()],
        ]);
    }

    protected function editConditionData(int $condition_id): void
    {
        $this->getDatabase()->update(
            self::SETTINGS_TABLE,
            ['item_ref_id' => ['integer', $this->requireTargetRefId()]],
            ['condition_id' => ['integer', $condition_id]]
        );
    }

    protected function deleteConditionData(int $condition_id): void
    {
        $this->getDatabase()->manipulateF(
            'DELETE FROM ' . self::SETTINGS_TABLE . ' WHERE condition_id = %s',
            ['integer'],
            [$condition_id]
        );
    }

    private function getTargetRefId(): int
    {
        // lazily read item_ref_id from self::SETTINGS_TABLE
    }

    private function requireTargetRefId(): int
    {
        if ($this->target_ref_id === null || $this->target_ref_id <= 0) {
            throw new LogicException($this->lang->txt('lso_exception_target_ref_id_not_set'));
        }

        return $this->target_ref_id;
    }
}
```

## 8. Navigation-aware input conditions

Implement `InputConditionNavigationAwareInterface` if your condition should influence adaptive navigation graphs.

```php
interface InputConditionNavigationAwareInterface
{
    public const string NAVIGATION_MODE_EDGE = 'edge';
    public const string NAVIGATION_MODE_DEPENDENCY = 'dependency';
    public const string NAVIGATION_MODE_GLOBAL = 'global';

    public function getNavigationMode(): string;

    /** @return int[] */
    public function getNavigationSourceRefIds(): array;
}
```

Built-in examples:

- `LearningProgressInputAwareCondition`: one direct referenced object, `EDGE`
- `PointsInputCondition`: dependency on several source objects, `DEPENDENCY`
- `LogicGateInputAwareCondition`: several source objects, `DEPENDENCY`

If your condition does not shape navigation, you do not need this interface.

## 9. Subtypes

Implement `SubtypeAwareInterface` if the condition has multiple variants that should appear as submenu entries in the add-condition drilldown.

The built-in pattern is:

```php
public function setupSteps(): array
{
    $this->assertContextSet();

    return [
        $this->ui_factory->menu()->sub($this->lang->txt($this->getName()), [
            $this->buildSubtypeStep(self::SUBTYPE_AND),
            $this->buildSubtypeStep(self::SUBTYPE_OR),
            $this->buildSubtypeStep(self::SUBTYPE_NOT),
        ])
    ];
}

public function buildSubtypeStep(string $subtype): Bulky
{
    return $this->buildStep(
        ['subtype' => $subtype],
        $this->getSubtypeLabel($subtype),
        self::CREATE_COMMAND
    );
}
```

You usually persist the subtype in your settings table and let `AbstractCondition::read()` restore it if the table is named `lso_c_{NAME}` and contains both `condition_id` and `subtype`.

See:

- `InputCondition/LogicGateInputCondition/LogicGateInputAwareCondition.php`
- `OutputCondition/LearningProgressOutputConditions/LearningProgressOutputAwareCondition.php`

## 10. Static misconfiguration: tag and popover support

This part applies to **input conditions**.

The adaptive content table can show a **Misconfigured** tag that opens an issue popover. The logic is condition-owned.

The relevant API is:

```php
public function getStaticInputConditionConstraints(): array
public function hasStaticInputConfigurationConflict(array $context = []): bool
public function getStaticInputConfigurationIssues(array $context = []): array
```

### Which method should you use?

Use them like this:

1. **`getStaticInputConditionConstraints()`**  
   Only for machine-readable cross-condition logic like:
   - "all of these must be completed"
   - "none of these may be completed"
   - "any of these may be completed"  
     `StaticInputConfigurationAnalyzer` uses this to detect contradictions between several input conditions on the same object.

2. **`hasStaticInputConfigurationConflict()`**  
   A simple boolean hook for conditions that only need "misconfigured or not".

3. **`getStaticInputConfigurationIssues()`**  
   The preferred API for anything non-trivial:
   - custom issue kinds
   - detailed popover text
   - issues that affect objects other than the condition owner

### The simplest possible misconfiguration

If your condition only needs a boolean and a generic issue on its own object, overriding `hasStaticInputConfigurationConflict()` is enough because `AbstractCondition` already provides a fallback implementation of `getStaticInputConfigurationIssues()`.

```php
public function hasStaticInputConfigurationConflict(array $context = []): bool
{
    return $this->referencesMissingLsoItems([$this->getTargetRefId()], $context);
}
```

That fallback creates a generic issue on the owning object.

### Preferred pattern: return explicit issues and details

For better tag/popover output, implement `getStaticInputConfigurationIssues()` yourself.

```php
public function getStaticInputConfigurationIssues(array $context = []): array
{
    $missing_ref_ids = $this->getMissingReferencedLsoRefIds([$this->getTargetRefId()], $context);
    if ($missing_ref_ids === []) {
        return [];
    }

    $affected_ref_ids = $this->getStaticInputConfigurationConflictAffectedRefIds();
    if ($affected_ref_ids === []) {
        return [];
    }

    return [new StaticInputConfigurationIssue(
        'completed_item_input_missing_reference',
        $affected_ref_ids,
        details: array_map(
            static fn(int $affected_ref_id): StaticInputConfigurationIssueDetail => new StaticInputConfigurationIssueDetail(
                $affected_ref_id,
                'lso_static_input_configuration_missing_references',
                properties_by_language_var: [
                    'lso_static_input_configuration_referenced_objects' => $missing_ref_ids
                ]
            ),
            $affected_ref_ids
        )
    )];
}
```

`StaticInputConfigurationIssueDetail` drives the popover:

- `title_language_var`: item title in the popover
- `description_language_var`: optional description
- `properties_by_language_var`: labeled values shown below the title

### Reporting an issue on another object

This is the important part for cross-object problems.

An input condition is allowed to report a misconfiguration on **another** item, not just on its owner.

That is how `PointsInputCondition` marks a referenced source object as misconfigured when the source is missing a `PointsOutputCondition`.

Pattern:

```php
return [new StaticInputConfigurationIssue(
    'points_input_source_without_points_output',
    $source_ref_ids_without_points_output,
    'lso_points_input_source_without_points_output_table',
    details: array_map(
        fn(int $ref_id): StaticInputConfigurationIssueDetail => new StaticInputConfigurationIssueDetail(
            $ref_id,
            'lso_points_input_missing_output_on_object',
            properties_by_language_var: [
                'lso_static_input_configuration_referenced_by_objects' => $this->getRefIdsReferencingMissingPointsOutput($ref_id)
            ]
        ),
        $source_ref_ids_without_points_output
    )
)];
```

So if your condition discovers that **item A** is broken because **item B** references it incorrectly, return `affected_ref_ids = [A]`, not `[B]`.

### What the analyzer does

`StaticInputConfigurationAnalyzer`:

- collects `getStaticInputConfigurationIssues()` from all input conditions
- merges popover details by affected object
- derives the set of misconfigured ref_ids for tag rendering
- detects contradictory static constraints from `getStaticInputConditionConstraints()`

### Useful helper methods from `AbstractCondition`

These helpers were added so condition classes can keep ownership of misconfiguration logic:

- `getValidLsoRefIds(array $context = [])`
- `getConfiguredStartRefId(array $context = [])`
- `getMissingReferencedLsoRefIds(array $referenced_ref_ids, array $context = [])`
- `referencesMissingLsoItems(array $referenced_ref_ids, array $context = [])`
- `getStaticInputConfigurationConflictAffectedRefIds()`

In most conditions, the default `getStaticInputConfigurationConflictAffectedRefIds()` is enough because it returns the owning `obj_ref_id`.

## 11. Accrued-value conditions

Use these interfaces when an input condition depends on a value that output conditions contribute.

```php
interface AccruedValueOutputConditionInterface
{
    public function getAccumulationIdentifier(): string;
    public function getAccumulatedValue(): int;
}

interface AccruedValueInputConditionInterface
{
    public function getAccumulationIdentifier(): string;
    public function getRequiredAccumulatedValue(): int;
}
```

The built-in points conditions are the reference implementation:

- `PointsOutputCondition` contributes `"points"`
- `PointsInputCondition` requires `"points"`

## 12. Import/export behavior

`AbstractCondition` already implements generic import/export support based on your `migrate()` table definitions.

That means:

- tables returned by `migrate()` are exported automatically
- rows are re-imported automatically for the new `condition_id`
- `item_ref_id` columns are remapped during import if the importer provides a ref-id mapping

If you can model your payload cleanly through `migrate()` tables, you get import/export support for free.

## 13. Recommended implementation checklist

When adding a new condition, go through this checklist:

1. Create the class in the correct `InputCondition/...` or `OutputCondition/...` namespace.
2. Extend `AbstractCondition`.
3. Implement the correct marker interface:
   - `InputConditionInterface`, or
   - `OutputConditionInterface`
4. Contribute the class as `ConditionType` in the `init()` method of your component (see section 1).
5. Add `protected const NAME = '...'`.
6. Implement `check()`.
7. If you need payload tables, return them from `migrate()`.
8. If you need configuration:
   - return `true` from `requiresConfiguration()`
   - implement `getAdditionalForm()`
   - implement `applyAdditionalFormData()`
   - implement `createConditionData()`, `editConditionData()`, `deleteConditionData()`
9. If editing needs current values, make sure your getters can lazy-load them from the database.
10. If duplicates should be blocked, return `false` from `allowMultipleConditionsOfSameType()`.
11. If the condition references other items, consider `InputConditionNavigationAwareInterface`.
12. If the condition can be statically broken, implement `getStaticInputConfigurationIssues()`.
13. Add language variables for labels, errors, and issue texts.
14. Add or update PHPUnit coverage next to the existing Learning Sequence condition tests.

## 14. Which built-in class should I copy from?

Use this rule of thumb:

| Need                                                                   | Best example                          |
| ---------------------------------------------------------------------- | ------------------------------------- |
| No config, no payload                                                  | `AlwaysOutputCondition`               |
| One numeric value in one table                                         | `PointsOutputCondition`               |
| One referenced object plus subtype                                     | `LearningProgressInputAwareCondition` |
| Several referenced objects plus numeric threshold                      | `PointsInputCondition`                |
| Several referenced objects plus subtype and static contradiction rules | `LogicGateInputAwareCondition`        |

If you start from one of those examples and keep the logic inside the condition class, you will match the current architecture well.
