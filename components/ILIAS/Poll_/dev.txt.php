<?php exit; ?>

=================================
Poll
=================================

Implements an ILIAS object type:
- class ilObjPoll
- class ilObjPollGUI

Implements a repository object type:
- ilObjPoll is instantiated with a ref_id
- class ilObjPollAccess
- class ilObjPollListGUI

Implements a repository object "as a side block":
- class ilPollBlock
- class ilPollBlockGUI

Provides export/import:
- class ilPollDataSet
- class ilPollExporter
- class ilPollImporter

Implements to UI-Tables:
- class ilPollUserTableGUI
- class ilPollAnswerTableGUI

Stores files to the ILIAS data directory:
- class ilFSStoragePoll

=================================
DB Tables
=================================

