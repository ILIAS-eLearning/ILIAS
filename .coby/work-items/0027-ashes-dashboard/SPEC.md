# Spec: ashes-dashboard

> `0027-ashes-dashboard` — specified 2026-10-20

## Goal

Provide an AshES configuration dashboard that opens by default and lets administrators recognize and enter the four main-bar tools through Delos-styled cards.

## Problem

Opening the AshES configuration currently lands on the log. The suite tools are reachable through administration tabs and main-bar children, but the configuration has no overview that introduces those tools and opens their existing default views.

## Success Criteria

- [ ] An administrator who opens the AshES configuration without selecting a tool sees the dashboard.
- [ ] The dashboard presents Log, Versions, Background Tasks, and Storage as cards, and each card opens that tool’s current default view.
- [ ] The main bar offers a dashboard parent item and a dashboard sub-item, which platform administrators can arrange in the global Main Bar administration.
- [ ] Each card identifies its tool with a title, a short description, and a tool-specific SVG. A missing SVG fails visibly by propagating an exception.

## Requirements

| ID | Requirement | Rationale |
| --- | --- | --- |
| R1 | Opening the AshES configuration with no tool class selected shows the dashboard. | The dashboard is the default configuration view. |
| R2 | A request that already selects Log, Versions, Background Tasks, or Storage opens that tool’s current default view. | Existing tool entry points remain valid. |
| R3 | The dashboard is presented by a dedicated GUI class and is not one of the four suite tools. | The dashboard is an entry surface, not another tool. |
| R4 | The main-bar provider exposes one dashboard parent item and one dashboard sub-item. The sub-item opens the dashboard. The provider assigns no position; platform administrators arrange both items in the global Main Bar administration. | The suite must offer a main item and a sub-item, while placement stays an administrative choice. |
| R5 | The dashboard shows a deck of exactly four cards: Log, Versions, Background Tasks, and Storage. | A tool card corresponds to a current main-bar tool. |
| R6 | Each card shows that tool’s translated title, a new short description, and one SVG. | A card must identify the tool and summarize it in text and image. |
| R7 | Each SVG is stored in its tool’s directory, depicts that tool and its features in one summarized graphic, and follows the default Delos icon style. | Artwork belongs to the tool it represents and must match the platform icon language. |
| R8 | Activating a card opens the same default view as that tool’s existing main-bar entry. | The deck and the current tool links lead to the same places. |
| R9 | When an expected SVG file is absent, building the dashboard raises an exception and that exception propagates. The dashboard supplies no placeholder image and does not hide the failure. | Missing artwork is a broken dashboard, not a degraded card. |
| R10 | The dashboard screen uses UI-framework components wherever those components can express the deck, cards, image, title, description, and navigation. | The feature must use the platform UI framework. |
| R11 | New dashboard code obtains its collaborators by dependency injection and introduces no static methods. | Construction must stay explicit and testable. |
| R12 | Dashboard structure, naming, and collaboration follow the paradigms of the existing AshES tools, using SOLID, object-oriented design, and domain language for the tool, the card, and the deck. | The feature must fit the plugin rather than introduce a parallel style. |

## Boundaries

### In scope

- Default configuration entry showing the dashboard.
- Dedicated dashboard GUI and its main-bar parent and sub-item.
- Four cards for Log, Versions, Background Tasks, and Storage.
- Titles, new short descriptions, navigation to existing tool defaults, and one Delos-styled SVG per tool.
- Exception propagation when an expected SVG is missing.

### Out of scope

- A card for Models.
- Cards for sub-views such as error analysis, log settings, or object analysis.
- New landing pages inside the four tools.
- A plugin-defined position for the dashboard main-bar items.
- A fallback or placeholder image.

### Deferred

- Cards for tools that are not among the four current main-bar tools.
- Changes to the arrangement controls of the global Main Bar administration.

## Acceptance Criteria

- [ ] The configuration entry with no tool class selected renders the dashboard.
- [ ] Each existing entry for Log, Versions, Background Tasks, and Storage still renders that tool’s current default view.
- [ ] The main bar contains a dashboard parent and one dashboard child whose action opens the dashboard.
- [ ] The dashboard provider code does not assign a fixed position to those items.
- [ ] The deck contains four cards, named for Log, Versions, Background Tasks, and Storage, and no Models card.
- [ ] Each card shows a translated title, a short description, and an SVG from that tool’s directory.
- [ ] Activating each card opens the same default view as that tool’s current main-bar entry.
- [ ] Removing any of the four SVG files causes an exception to propagate when the dashboard is built.
- [ ] The rendered deck and cards are UI-framework components.
- [ ] The new dashboard code has no static methods.

## Assumptions

- The four current main-bar tools are the complete card catalog, even though Models is also an administration tab.
- Short descriptions are new translated copy written for the dashboard.
- Platform administrators will place the new parent and sub-item through the existing global Main Bar administration.
- The precise Delos icon measurements still need confirmation from the current skin during research.

## Open Questions

- Which viewBox, color, and stroke rules define the default Delos icon style in this installation?
- Which UI-framework card construction carries an image, a title action, and a description section together?
- How should the dashboard service be registered so it follows the existing tool service catalog without static access?
