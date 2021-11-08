<?php exit; ?>

## Main changes 5.4

- Introduction of repo objects (wiki) as submission.
- Introduction of assignment type classes under AssignmentTypes

Current situation in ilExSubmission/exc_returned table
- PROBLEM: - exc_returned entries are used for text and blog/portfolios submissions, too!
           - filetitle is the wsp_id for blog/portfolios, the ref_id for wikis now!
           - getFiles() also returns entries for text
           -> This is confusing.
- FUTURE: exc_returned entries should be refactored in a more general concept "Submission Items" (files, text,
  wsp objects, repo objects, ...)


## Main changes 5.3

New DB table exc_ass_file_order with columns id,assignment_id,filename,order_nr

### File organisation 5.3
#### data/*client* directory

ilExercise/X/exc_*EXC_ID*/feedb_*ASS_ID*/0/									holds sample solution file (with original name)
ilExercise/X/exc_*EXC_ID*/feedb_*ASS_ID*/*USER_ID*/							holds evaluation/feedback files from tutors for learner *USER_ID*
ilExercise/X/exc_*EXC_ID*/subm_*ASS_ID*/*USER_ID*/*TIMESTAMP*_filename.pdf	holds file submissions (also blogs and porfilios, filename = obj_id)
ilExercise/X/exc_*EXC_ID*/peer_up_*ASS_ID*/*TAKER_ID*/*GIVER_ID*/*CRIT_ID*/	holds peer feedback file (original name)
ilExercise/X/exc_*EXC_ID*/mfb_up_*ASS_ID*/*UPLOADER_ID*/					hold multi-feedback zip file/structure from tutor *UPLOADER_ID*
ilExercise/X/exc_*EXC_ID*/tmp_*ASS_ID*/										temp dir for "download all assignments" process (creates random subdir before starting)

#### webdata/*client* directory

ilExercise/X/exc_*EXC_ID*/ass_*ASS_ID*/										directory holds all instruction files (with original names) !!! CHANGED in 5.3


### File organisation 5.2

#### data/*client* directory

ilExercise/X/exc_*EXC_ID*/ass_*ASS_ID*/										directory holds all instruction files (with original names)
ilExercise/X/exc_*EXC_ID*/feedb_*ASS_ID*/0/									holds sample solution file (with original name)
ilExercise/X/exc_*EXC_ID*/feedb_*ASS_ID*/*USER_ID*/							holds evaluation/feedback files from tutors for learner *USER_ID*
ilExercise/X/exc_*EXC_ID*/subm_*ASS_ID*/*USER_ID*/*TIMESTAMP*_filename.pdf	holds file submissions (also blogs and porfilios, filename = obj_id)
ilExercise/X/exc_*EXC_ID*/peer_up_*ASS_ID*/*TAKER_ID*/*GIVER_ID*/*CRIT_ID*/	holds peer feedback file (original name)
ilExercise/X/exc_*EXC_ID*/mfb_up_*ASS_ID*/*UPLOADER_ID*/					hold multi-feedback zip file/structure from tutor *UPLOADER_ID*
ilExercise/X/exc_*EXC_ID*/tmp_*ASS_ID*/										temp dir for "download all assignments" process (creates random subdir before starting)

#### webdata/*client* directory

not used in 5.2