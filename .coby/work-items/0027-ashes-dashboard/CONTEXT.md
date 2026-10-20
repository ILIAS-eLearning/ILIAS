# Context: ashes-dashboard

> `0027-ashes-dashboard` — discussed 2026-10-20

## Approach

Add a dedicated dashboard screen to the AshES administration flow and build its deck from the existing four-tool catalog. The screen renders UI-framework cards, while a separate main-bar parent and child expose the dashboard without becoming one of those tools.

## Decisions

| Area | Decision | Rationale |
| --- | --- | --- |
| Card catalog | Build the deck from the current main-bar tool catalog: Log, Versions, Background Tasks, and Storage. Leave the dashboard out of that catalog. | The approved catalog is the four main-bar tools, so the deck and those entries cannot drift. |
| Card action | Point each card at the catalog entry’s existing GUI class and default command. | The card opens the tool’s current default view. |
| Main bar | In the main-bar provider, create one dashboard parent item and one dashboard link child. Do not set a position. | Administrators arrange both items in the global Main Bar administration. |
| Configuration entry | Change only the empty configuration entry, where no tool class is selected, to the dashboard GUI. | Tool requests that already name a class keep their routes. |
| Card content | Each card carries the tool’s translated title, a new short description, and one SVG stored in that tool’s directory. | The card must identify and summarize the tool in text and image. |
| Missing SVG | The SVG reader raises when the expected file is absent. The dashboard does not catch that exception and supplies no substitute image. | A missing file must bubble rather than degrade the card. |
| Rendering | Compose the screen from the UI-framework deck and card, using the card image, title action, and a section for the description. | These components cover the required screen. |
| Construction | Resolve the dashboard application service from the existing service catalog and pass collaborators as instance dependencies. | This matches the plugin’s dependency style and avoids static methods. |

## Architecture Notes

The empty configuration command redirects to the dashboard GUI. The dashboard GUI is an ilCtrl target of the configuration host and is absent from the tool catalog that feeds both the existing tool links and the deck.

The main-bar provider keeps the current AshES parent and its four tool children. It adds a second parent whose single child links to the dashboard GUI. Both use the current administration-access visibility rule. Neither call assigns menu order.

A dashboard application service reads the four catalog entries and produces card values. Each value holds the title text, the new description text, the SVG location inside the owning tool directory, and the entry’s GUI class and default command. A file reader loads the SVG and raises when the file is absent. The GUI maps the resulting card values to UI-framework components and does not decide catalog membership or file existence.

Titles use the existing tool language entries. Descriptions use new language entries. The SVG is a compact symbolic summary of the tool and its features, drawn to the Delos icon rules confirmed during research.

## Constraints

- The dashboard GUI must collaborate through the administration screen and service-catalog patterns already used by AshES tools.
- New dashboard code must not add static methods.
- UI-framework deck and card components are the rendering path where they can express the required content.
- SVG files live in the directories of the tools they represent.
- Missing SVG files are exceptional failures and must propagate.
- The provider must not encode a main-bar position.

## Patterns to Follow

- AshES configuration host and per-tool presentation GUI classes.
- AshES service catalog for instance dependencies.
- AshES main-bar provider with a parent item and link children, including its administration-access visibility rule.
- UI-framework `Card` and `Deck`.
- Domain separation among the tool catalog, the card, the deck, and the presentation GUI.

## Risks & Mitigations

| Risk | Mitigation |
| --- | --- |
| The deck could diverge from the four main-bar tools. | Build cards from that catalog and keep the dashboard out of it. |
| A missing SVG could be swallowed and replaced by a placeholder. | Raise at the file reader and do not catch the exception in the dashboard flow. |
| The Delos icon rules could be inferred loosely. | Research confirms viewBox, color, and stroke from the current skin before the SVGs are drawn. |
| A second main-bar parent could be ordered ahead of the existing AshES parent by hard-coded position. | Omit position assignment and leave arrangement to the global Main Bar administration. |

## Canonical References

- `public/Customizing/global/plugins/Services/EventHandling/EventHook/AshES/src/Administration/Application/Tools.php` — current four-tool catalog.
- `public/Customizing/global/plugins/Services/EventHandling/EventHook/AshES/src/GlobalScreen/Presentation/MainBarProvider.php` — parent and child main-bar construction.
- `public/Customizing/global/plugins/Services/EventHandling/EventHook/AshES/classes/class.ilAshESConfigGUI.php` — empty configuration entry.
- `components/ILIAS/UI/src/Component/Card/Card.php` and `components/ILIAS/UI/src/Component/Deck/Deck.php` — UI-framework card and deck.

## Deferred

- Extending the card catalog beyond Log, Versions, Background Tasks, and Storage.
- Adding a Models card or cards for tool sub-views.
- Encoding dashboard position in the main-bar provider.
