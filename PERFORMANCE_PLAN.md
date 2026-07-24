# Performance: Test "Add from pool" — Fragenbrowser

## Problem

`ilObjTestGUI::showQuestions` → Button "Add from pool" →
`ilTestQuestionBrowserTableGUI::browseQuestionsCmd` baut eine
`ilAssQuestionList` und ruft `load()` mit gesetzter Range/Order auf.

`ilAssQuestionList::buildQuery()` (`class.ilAssQuestionList.php:577`)
setzt über `getSelectFieldsExpression()` (`:444`) **korrelierte
EXISTS-Subqueries** als SELECT-Felder:

- `generateFeedbackSubquery()` (`:476`) — 4 abhängige Subqueries
  (qpl_fb_generic, qpl_fb_specific, jeweils + page_object)
- `generateHintSubquery()` (`:500`) — qpl_hints (EXPLAIN: `ALL`-Scan)
- `generateTaxonomySubquery()` (`:506`) — tax_node_assignment

Diese werden für **jede Kandidatenzeile** berechnet, **bevor**
`ORDER BY`/`LIMIT` greifen (EXPLAIN: `Using temporary; Using filesort`
über ~199k Zeilen). Auf großen Instanzen → 30–40 min Laufzeit,
Blockieren anderer Queries. Lokal (1 Pool, 2 Fragen) nicht reproduzierbar.

Bug-Report schlägt vor: erst paginierte question_id-Menge bestimmen,
dann Flags nur für diese IDs berechnen.

## Lösungsansatz: Zweiphasige Abfrage

**Aktiv nur wenn `$this->range !== null`** (Pagination aktiv — der
Langsamm-Pfad). Alle anderen Caller setzen keine Range → unverändert.

### Phase A — ID-Auswahl (`buildPaginatedIdsQuery()`)

```
SELECT DISTINCT qpl_questions.question_id
FROM qpl_questions
  {getTableJoinExpression()}          -- inkl. Filter-Joins (handleFeedbackJoin/handleHintJoin)
WHERE qpl_questions.tstamp > 0
  {getConditionalFilterExpression()}  -- alle WHERE-Filter
  {buildOrderQueryExpression()}       -- Spalten qualifiziert!
  {buildLimitQueryExpression()}       -- Pagination hier
```

- **kein** `getSelectFieldsExpression()` (keine EXISTS-Subqueries)
- **kein** `getHavingFilterExpression()`
- liefert geordnete Liste von `question_id`s

### Phase B — Anreicherung (`buildEnrichmentQuery(array $ids)`)

```
{getSelectFieldsExpression()}         -- inkl. feedback/hints/taxonomies (nur f. wenige IDs!)
FROM qpl_questions
  {getBaseTableJoinExpression()}      -- qpl_qst_type, object_data, ggf. tst_test_result
                                      -- OHNE handleFeedbackJoin/handleHintJoin
WHERE qpl_questions.tstamp > 0
  AND qpl_questions.question_id IN (<Phase-A-IDs>)
```

- Ergebnis in PHP nach Phase-A-Reihenfolge ordnen.

### Fallback auf alte Einzelabfrage

Wenn **eine** Bedingung zutrifft:

1. `getHavingFilterExpression() !== ''`
   (Filter `feedback=false`/`hints=false` nutzt HAVING)
2. Order-Feld ∈ `{feedback, hints, taxonomies}`
   (Sortierung nach berechneter Spalte → Phase A kann nicht danach sortieren)

→ alte `buildQuery()` (unverändert).

## Detailänderungen in `class.ilAssQuestionList.php`

### 1. `load()` (`:588`)

```php
public function load(): void
{
    $this->checkFilters();

    if ($this->canUseTwoPhaseQuery()) {
        $this->loadTwoPhase();
        return;
    }

    $this->loadSinglePhase();   // bisherige Implementierung
}
```

### 2. Neu: `canUseTwoPhaseQuery(): bool`

```php
private function canUseTwoPhaseQuery(): bool
{
    if ($this->range === null) {
        return false;
    }
    if ($this->getHavingFilterExpression() !== '') {
        return false;
    }
    if ($this->order !== null
        && $this->isOrderByComputedField()
    ) {
        return false;
    }
    return true;
}
```

### 3. Neu: `buildPaginatedIdsQuery(): string`

SELECT DISTINCT question_id + Joins + Filter + Order(qualifiziert) + Limit.

### 4. Neu: `buildEnrichmentQuery(array $ids): string`

getSelectFieldsExpression() + getBaseTableJoinExpression() + WHERE IN.

### 5. Neu: `getBaseTableJoinExpression(): string`

Wie `getTableJoinExpression()`, aber **ohne** `handleFeedbackJoin`/
`handleHintJoin` (die waren nur Filter-Joins, in Phase A bereits
angewendet; in Phase B stören sie nur).

### 6. `buildOrderQueryExpression()` (`:544`) erweitern

Neuer Parameter `bool $qualify = false`. Bei `$qualify=true`:
Spalten qualifizieren, da `qpl_questions.*` in Phase A fehlt → sonst
"Column 'title' is ambiguous":

| Order-Feld    | qualifiziert zu           |
|---------------|---------------------------|
| title         | qpl_questions.title       |
| description   | qpl_questions.description |
| author        | qpl_questions.author      |
| lifecycle     | qpl_questions.lifecycle   |
| points        | qpl_questions.points      |
| max_points    | qpl_questions.points      |
| created       | qpl_questions.created     |
| tstamp        | qpl_questions.tstamp      |
| type_tag      | qpl_qst_type.type_tag     |
| parent_title  | object_data.title         |
| feedback/hints/taxonomies | (Fallback — kommt hier nie an) |

### 7. Neu: `isOrderByComputedField(): bool`

Prüft ob Order-Feld ∈ {feedback, hints, taxonomies}.

### 8. `getTotalRowCount()` (`:622`)

Unverändert (bereits ohne EXISTS-Subqueries → schnell).

**Vorhandener Bug (nur dokumentieren):** wendet `getHavingFilterExpression()`
nicht an → Count bei `feedback=false`-Filter falsch. Nicht Teil dieses
Tickets.

## DB-Index — `class.ilTestQuestionPool10DBUpdateSteps.php`

Neue `step_3()`: Composite-Index `(obj_fi, original_id, title)` auf
`qpl_questions` als `i6` (ILIAS' `checkIndexName` limitiert Indexnamen
auf 3 Zeichen; die bestehenden `i1_idx`…`i5_idx` predaten diese Regel).

- Deckt Phase A für beide Hauptpfade:
  - Pool-intern: `obj_fi = X` + `original_id IS NULL`
  - Browse-from-pools: `obj_fi IN (...)` + `original_id IS NULL`
- `title` als dritte Spalte unterstützt Default-Sortierung.
- `addIndex()` ist idempotent (vorhandene Single-Column-Indexes
  `i3_idx`(obj_fi), `i2_idx`(original_id), `i4_idx`(title) bleiben).
- Trade-off: höhere Schreibkosten auf `qpl_questions` bei INSERT/UPDATE
  (große Tabelle) — akzeptiert zugunsten der Lese-Performance.

## Kompatibilität

- `QuestionTable extends ilAssQuestionList`: überschreibt `load()` nicht;
  `getData()` setzt **kein** `setRange` auf der Liste (paginiert in PHP)
  → `canUseTwoPhaseQuery()` liefert `false` → unverändert.
- `getTotalRowCount()` dort ruft `load()`+`count()` auf (lädt alles —
  separates Problem, nicht dieses Ticket).
- Alle anderen Caller (`ilTestResultsGUI`, `ilTestSkillAdministrationGUI`,
  `ilLMTracker`, `ilAsqFactory`, `ilCopySelfAssQuestionTableGUI`,
  `ilObjQuestionPoolTaxonomyEditingCommandForwarder`,
  `ilQuestionPoolSkillAdministrationGUI`, `ilTestPlayerAbstractGUI`)
  setzen keine Range → unverändert.

## Validierung

1. `ilAssQuestionListTest` ergänzen:
   - Phase-A/B-Pfad mit Mock-DB
   - Fallback bei Sortierung nach `feedback`
   - Fallback bei Filter `feedback=false` (HAVING)
   - Fallback bei fehlender Range
2. Docker: Setup-Step ausführen, Index prüfen
   (`SHOW INDEX FROM qpl_questions`).
3. Docker: Query-Log vor/nachher (ILIAS-Debug-Toolbar) —
   2 Queries statt 1, dafür ohne korrelierte Subqueries über die
   Kandidatenmenge.
4. Edge Cases manuell:
   - Sortierung nach `feedback`/`hints`/`taxonomies` → Fallback
   - Filter `feedback=false` → Fallback
   - Filter `feedback=true` → Fallback (INNER JOIN + HAVING)
   - Sortierung nach `title`/`parent_title`/`type_tag` → Phase A
5. Auf großer Instanz: EXPLAIN der Phase-A-Query (sollte nur noch
   `qpl_questions` + billige JOINs, keine `DEPENDENT SUBQUERY`),
   Laufzeit messen.

## Out of scope

- `QuestionTable` (PHP-Pagination, lädt alle) — separates Ticket.
- `getTotalRowCount` HAVING-Korrektur — vorhandener Bug, nur dokumentieren.
