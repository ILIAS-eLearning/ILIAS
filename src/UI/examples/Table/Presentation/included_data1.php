<?php
function included_data1()
{
    return  array(
        array(
            'type' => 'Single Choice Frage',
            'question_title' => 'Belastbarkeit',
            'question_txt' => 'Wie ausgeprägt ist die Belastbarkeit des / der Auszubildenden?',
            'answers' => array(
                array('title' => 'weniger ausgeprägt', 'amount' => 2,	'proportion' => 20),
                array('title' => 'teilweise ausgeprägt', 'amount' => 0,	'proportion' => 0),
                array('title' => 'ausgeprägt', 'amount' => 6,	'proportion' => 60),
                array('title' => 'deutlich ausgeprägt', 'amount' => 1,	'proportion' => 10),
                array('title' => 'stark ausgeprägt', 'amount' => 0,	'proportion' => 0),
                array('title' => 'sehr stark ausgeprägt', 'amount' => 0,	'proportion' => 0),
                array('title' => 'übermäßig ausgeprägt', 'amount' => 1,	'proportion' => 10)
            ),
            'stats' => array(
                'total' => 10,
                'skipped' => 2,
                'most_common' => 2,
                'most_common_total' => 6,
                'median' => 2,
            )
        ),

        array(
            'type' => 'Single Choice Frage',
            'question_title' => 'Dialogfähigkeit, Kundenorientierung, Beratungsfähigkeit',
            'question_txt' => 'Wie ausgeprägt ist die Dialogfähigkeit, Kundenorientierung und Beratungsfähigkeit des / der Auszubildenden?',
            'answers' => array(
                array('title' => 'weniger ausgeprägt', 'amount' => 0,	'proportion' => 0),
                array('title' => 'teilweise ausgeprägt', 'amount' => 1,	'proportion' => 100),
                array('title' => 'ausgeprägt', 'amount' => 0,	'proportion' => 0),
                array('title' => 'deutlich ausgeprägt', 'amount' => 0,	'proportion' => 0),
                array('title' => 'stark ausgeprägt', 'amount' => 0,	'proportion' => 0),
                array('title' => 'sehr stark ausgeprägt', 'amount' => 0,	'proportion' => 0),
                array('title' => 'übermäßig ausgeprägt', 'amount' => 0,	'proportion' => 0)
            ),
            'stats' => array(
                'total' => 1,
                'skipped' => 0,
                'most_common' => 1,
                'most_common_total' => 1,
                'median' => 1,
            )
        ),
    );
}
