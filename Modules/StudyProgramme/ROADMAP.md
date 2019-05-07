# ilObjStudyProgrammeSettingsGUI
## Settings form input values transformations
The method call ILIAS\UI\Implementation\Component\Input\Container\Form\Form::withAdditionalTransformation
contains side effects via use($prg). These should be avoided.