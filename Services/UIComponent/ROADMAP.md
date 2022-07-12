# Roadmap

## Removing deprecated UI implementations

The priorities for deprecation and removing the components are discussed frequently in the Page Layout Revision meetings.

PLR Meeting, 13 Aug 2020: We currently would like to focus on the following elements to be removed completely: Checklist, Glyph, GroupedList, Lightbox, Modal.

### AdvancedSelectionListGUI

Please migrate to KS Dropdowns. If this is not possible, please contact the PLR group.

**Usages**
- Oct 2021: More than 100 usages in various components.

### Checklist (ilChecklistGUI)

- Should be easy to remove with ILIAS 7 setup refactorings.

**Usages**

- setup: Checklist in setup, seems to be obsolete/dead code. -> RK

### Glyph (ilGlyphGUI)

- Missing in KS: Filter, Drag
- Other usages should be replaced by KS glyphs.

**Usages**

- Filter Glyph
  - Services/Awareness -> AK
- Add Glyph
  - Services/COPage (Page Editor) -> AK
- Drag Glyph
  - Services/COPage (Page Editor) -> AK
  - Services/Form (HierarchyForm) -> AK
- Up/Down Glyph
  - Services/Form (FileWizardInput) -> FS
  - Services/Form (FormProperty) -> AK
  - Services/Form (MultipleImagesInput, MultipleTextsInput, TextWizardInput) -> BH/MB
  - Services/Form (SelectBuilderInput) -> SM
- Add/Remove Glyph
  - Services/Form (FileWizardInput) -> FS
  - Services/Form (FormProperty, HierarchyForm) -> AK
  - Services/Form (MultipleImagesInput, MultipleTextsInput, TextWizardInput) -> BH/MB
  - Services/Form (SelectBuilderInput) -> SM
- Search Glyph
  - Services/Help -> AK
- Close Glyph
  - Services/Help, Services/MediaObjects, Services/Notes -> AK
  - Services/Notification (OSD) -> MJ
- Caret Glyph
  - Services/Search -> SM
- Previous/Next Glyph
  - Services/Tabs -> AK

### GroupedList (ilGroupedListGUI)

- KS replacement needed. Most prominent use is the "Add New Item Dropdown". Grouped lists are lists with subheadings and a possible multi-column layout.

**Usages**

- **Services/Object**: Used for "Add New Item" Dropdown. Replacing this instance needs a broader discussion in the PLR group. Currently there does not seem to exist anything in the KS that could be a simple replacement. -> ALL
- **Services/Help**: Used for listings of help pages. -> AK
- **Services/MainMenu**: Used for rendering of help topics. Most probable deprecated. -> AK
- **Modules/Test**: Used for list of questions? -> BH/MB
- **Services/UIComponent/Checklist**: Old check list presentation (see above). Obsolete. -> AK after RK

### Lightbox (ilLightboxGUI)

- Should be replaced with KS element. Missing: ilLightboxGUI does not only support special content types, but any media object. Maybe this is not a necessary requirement in the mediacast with ILIAS 7 anymore.

**Usages**

- **Services/MediaObjects**: Used for lightbox in media casts. -> AK

### Modal (ilModalGUI)

- These might not all be easily transferrable to the KS elements. Sometimes the JS API is being used by consumers, e.g. in the chatroom.

**Usages**

- Modules/Chatroom -> MJ
- Modules/Excercise -> AK
- Modules/Forum -> MJ
- Modules/MediaPool -> AK
- Modules/StudyProgramme -> RK
- Modules/Survey -> AK
- Modules/Test -> BH/MB
- Modules/TestQuestionPool -> BH/MB
- Services/COPage -> AK
- Services/Link (Internal link modal) -> AK

### Other components

WIP