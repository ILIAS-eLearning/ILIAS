# ROADMAP

This service is deprecated.

## Usages

Event (util.Event)
- Form -> HierarchyForm, flashAddParam
- Scorm2004 (not used?)
- TestQuestionPool


Dom
- DataCollection
- GlobalTemplate
- PropertyForm
- ...

DragDrop
- Survey
- Calendar
- COPage
- HierarchyForm

DomEvent
- Session
- Test
- Calendar


Panel
- Test
- TermsOfService


Connection
- Cloud
- DataCollection
- LearningModule
- Accordion
- Block
- COPage
- Help
- Link
- MainMenu
- MediaObjects
- Table
- AdvancedSelectionList
- Explorer
- Explorer2

ConnectionWithAnimation
- Test
- Form

Overlay
- Test
- TermsOfService
- AdvancedSelectionList
- il.Overlay used in
    - Test: Adjustment Stats Container
    - Search.js (overlay on search screen for "Search Area")
    - COPage: Interactive images
    - Object Statistics: "lpdt"
    - AdvancedSelectionList: (only to hide all legacy il.Overlays)
- ilOverlayGUI used in
    - DataCollection (obsolete!?)
    - SCORM2004: Glossary Overlays
    - Rating
    - Table2GUI

- Overlay
-> http://sandywalker.github.io/webui-popover/demo/
- il.Overlay
    - add (id, cfg)
        - new Yahoo.widget.Overlay (mit cfg.yuicfg)
        - cfg
            close_el (closing element, will trigger hide)
            auto_hide
        - cfg.yuicfg
            visible: true/false
            fixedcenter: true/false
        - calls render on Yahoo.widget.Overlay
    - addTrigger
        id, event, anchor, fixed_center, ov_corner (tl), anch_corner (bl)
    - togglePerTrigger
    - render
    - hide
        - calls hide on Yahoo.widget
    - show
        - calls show on Yahoo.widget
        - calls cfg.setProperty("context" on Yahoo.widget
        - calls cfg.setProperty("fixedcenter" on Yahoo.widget
    - loadAsynch
    - subscribe (not needed anymore?)
        - calls subscribe on Yahoo.widget
    - fixPosition
        - sets el.style.height, setX)
    - setX, setY
        - all with jQuery



Cookie
- Authentication (SessionReminder)