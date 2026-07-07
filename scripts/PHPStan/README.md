PHPStan Custom Rules
====================

This directory holds ILIAS' custom PHPStan rules. They come in two independent
rulesets with different purposes and lifecycles:

| Ruleset | Config | Purpose | CI |
|---|---|---|---|
| Legacy-UI report | `legacy_ui.neon` | Track migration of legacy UI components | `legacy-ui.yml` — nightly, non-gating, uploads a CSV report |
| Code rules | `code_rules.neon` | Enforce code policies | `code-rules.yml` — per pull request, **hard gate** |

The rule classes for both live under `Rules/`, grouped into one subdirectory (and
matching namespace) per topic, and are autoloaded via composer PSR-4
(`ILIAS\Scripts` → `./scripts`):

```
Rules/
  LegacyUI/       ILIAS\Scripts\PHPStan\Rules\LegacyUI       — legacy UI component rules
Attributes/       ILIAS\Scripts\PHPStan\Attributes           — the AllowRuleViolation exemption attribute + checker
```

Add a new topic as a new subdirectory/namespace under `Rules/`.

Code Rules (policy gate)
------------------------

The code-rules ruleset enforces mandatory coding policies. It is a hard gate: the
CI job `code-rules.yml` fails a pull request on any violation. The policies are
enforced everywhere; genuinely unavoidable cases are exempted explicitly in the code
(see [Exempting a violation](#exempting-a-violation) below), not silently
grandfathered.

Run the full gate locally:

```bash
./scripts/PHPStan/run_code_rules.sh
```

Run it for a single directory (e.g. `components/ILIAS/File`):

```bash
./scripts/PHPStan/run_code_rules.sh components/ILIAS/File
```

CI runs the same script with `ERROR_FORMAT=github` so violations show up as inline
annotations on the pull request; any PHPStan `--error-format` can be set via that
environment variable. `ERROR_FORMAT=stepSummary` renders the Markdown summary that CI
appends to the GitHub step summary.

### Active policies

| Rule classes | Identifier | Policy |
|---|---|---|

### Adding a policy rule

1. Create a subdirectory under `Rules/` for the topic (namespace
   `ILIAS\Scripts\PHPStan\Rules\<Topic>`) and add a rule class implementing
   `PHPStan\Rules\Rule` (or extending a shared base). Expose two constants on it:
   - `public const IDENTIFIER = 'ilias.<policy>';` — the stable error identifier,
     passed to `->identifier(self::IDENTIFIER)` and referenced by exemptions.
   - `public const LABEL = '<human name>';` — the human-readable name. Pass it into
     the error metadata (`->metadata(['rule' => self::LABEL, ...])`); the step-summary
     error formatter reads the label from there, so the name has a single source.

   For a rule family that shares one identifier across several node-type subclasses
   (e.g. the superglobal / `$GLOBALS` rules), put both constants on the abstract base.
2. Register the class under `rules:` in `code_rules.neon`. There is nothing else to
   wire up: the gate runs it from that list, and the step-summary formatter
   (`ErrorFormatter/StepSummaryFormatter`, registered as the `stepSummary` error
   format) picks up its label straight from the error metadata.
3. Run the gate and either fix the code it flags or exempt the unavoidable cases
   (next section). The gate must be green before the rule is merged.

### Exempting a violation

There is no blanket baseline. Genuinely unavoidable violations are exempted
explicitly and locally, so every exception is visible and carries a reason:

- **On a class, method or function** — annotate it with the
  `ILIAS\Scripts\PHPStan\Attributes\AllowRuleViolation` attribute, passing a reason
  and one or more rule identifiers:

  ```php
  use ILIAS\Scripts\PHPStan\Attributes\AllowRuleViolation;

  #[AllowRuleViolation('populates $_GET before the HTTP service exists', 'ilias.superglobalWrite')]
  public static function sanitizeRequest(): void { /* … */ }
  ```

  Some rule sets additionally ship a convenience subclass of `AllowRuleViolation`
  that already carries the identifier, so only the reason is needed. The checker
  matches those via `ReflectionAttribute::IS_INSTANCEOF`.

- **On a single free-standing statement** (e.g. in a resource script without an
  enclosing function, where an attribute cannot be placed) — use an inline comment
  with the identifier and a reason:

  ```php
  $_COOKIE['ilClientId'] = $client_id; // @phpstan-ignore ilias.superglobalWrite (bootstrap before request wrapper)
  ```

There is no baseline file: the gate must stay green through in-code exemptions, not
by grandfathering. (If a mass migration ever needs one, `--generate-baseline` can
create it and add its `includes:` entry back to `code_rules.neon`.)

Legacy-UI report
----------------

With the ["Removing of Legacy-UIComponents-Service and Table" project](https://docu.ilias.de/goto_docu_grp_12110.html), a large number of UI elements that are not available in the UI service will be replaced by ILIAS 10. With the rules collected here, violations of the deprecations are found and collected in reports.

The entire report comprises a CSV file for each component and a summarised file for the entire code base, this form of the report can be generated as follows:

```bash
./scripts/PHPStan/run_legacy_ui_report.sh
```

All results will be written to the directory `./Reports`. 

To run the rules individually (e.g. for the directory ILIAS/File), the following command can be used:

```bash
./scripts/PHPStan/run_legacy_ui_report.sh components/ILIAS/File
```

If you want to just check and show violations directly (without csv-report), you can use the following command (for components/ILIAS/File):

```bash
./vendor/composer/vendor/bin/phpstan analyse -c ./scripts/PHPStan/legacy_ui.neon -a ./vendor/composer/vendor/autoload.php --no-interaction --no-progress  components/ILIAS/File 
```
