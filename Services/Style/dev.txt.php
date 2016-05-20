<?php exit; ?>

=================================
Reorganisation
=================================

idea for new structure:

System/classes
System/templates
Content/...
classes/class.ilStyleExporter.php

copg: Candidate for COPage Service (General Problem "SCORM RTE features", abandon?) (or content subdirectory)
sys: system style related

- basic_style/...: copg
- classes/...InputGUI.php: copg
- classes/class.ilContentStylesTableGUI.php: copg
- classes/class.ilObjStyleSettings.php: sys
- classes/class.ilObjStyleSettingsGUI.php: sys/copg (copg related settings, incl. page layouts should be moved to other class) (micro router?)
- classes/class.ilObjStyleSettingsAccess.php: sys
- classes/class.ilObjStyleSheet.php: copg
- classes/class.ilObjStyleSheetGUI.php: copg
- classes/class.ilPageLayout....php: copg
- classes/class.ilPasteStyleCharacteristicTableGUI.php: copg
- classes/class.ilStyleColorTableGUI.php: copg
- classes/class.ilStyleDataset.php: copg
- classes/class.ilStyleDefinition.php: sys
- classes/class.ilStyleExporter.php: copg (how to migrate/move?)
- classes/class.ilStyleImageTableGUI.php: copg
- classes/class.ilStyleImporter.php: copg (how to migrate/move?)
- classes/class.ilStyleImportParser.php: copg
- classes/class.ilStyleMediaQueryTableGUI.php: copg
- classes/class.ilStyleMigration.php: copg
- classes/class.ilStyleTableGUI.php: copg
- classes/class.ilSysStyleCatAssignmentTableGUI.php: copg
- classes/class.ilSystemStyleHTMLExport.php: sys
- classes/class.ilSystemStylesTableGUI.php: sys
- classes/class.ilTableTemplatesTableGUI: copg
- templates/... sys/copg
- xml/... copg
