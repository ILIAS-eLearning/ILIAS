# Roadmap

## Short Term

- Merge the fixes for LTI 1.3 Dynamic Registration into ILIAS 10 and ILIAS 11.
- Merge the fixes for LTI Deep Linking into ILIAS 10 and ILIAS 11.
- Merge the fixes for the LTI 1.3 Assignment and Grade Service into ILIAS 10 and ILIAS 11.
- Keep monitoring Learning Progress synchronization, which is expected to work correctly with the current fixes.

## Mid Term

- Rework the LTI implementation into a single module that contains both sides of the LTI integration.
- Align the internal structure and naming with the current LTI Tool and Platform roles.
- Remove outdated and obsolete code paths that are no longer required for the supported LTI workflows.
- Rebuild the user interface with current ILIAS UI components from the Kitchen Sink and avoid deprecated ILIAS modules.

## Long Term

- Apply for official LTI certification to ensure that ILIAS fully complies with IMS Global standards and guarantees interoperability with certified external tools.
- Ensure that the reworked LTI module follows the correct LTI flows consistently.
- Improve maintainability by reducing duplicated responsibilities between the current Consumer and Provider implementations.
- Make future maintenance easier and faster through a cleaner architecture, current UI components, and removal of deprecated dependencies.
