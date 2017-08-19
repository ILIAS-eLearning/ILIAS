<?php exit; ?>

## MANTIS BUG :0019795
It is not possible to remove files from a peer feedback from a exercise.

The problem seems the file path creation and affects both feedback with and without criteria.

Example:
User ID who did the exercise: 310
User ID who provide feedback: 6
Feedback file: feedback.txt
Criteria ID = 10

Without criteria the uploaded files are stored outside the final path. The name of the file is also affected.

data/client/ilExercise/3/exc_343/peer_up_15/310/6/ [empty directory]
data/client/ilExercise/3/exc_343/peer_up_15/310/6feedback.txt

After patch:

data/client/ilExercise/3/exc_343/peer_up_15/310/6/feedback.txt


With criteria, the final directory name is userid+criteriaid instead of criteria id.

data/client/ilExercise/3/exc_343/peer_up_15/310/610/feedback.txt

After patch:

data/client/ilExercise/3/exc_343/peer_up_15/310/6/10/feedback.txt

## We need to take a look at how to proceed with the migration of the old directories/files.
