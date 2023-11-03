<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Presentation;

/**
 * You can also leave out "further fields", add one or more Blocks and Layouts
 * to the content of the row and add an leading image.
 */
function base1()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $actions = array("Alle" => "#", "Mehr als 5 Antworten" => "#");
    $aria_label = "filter entries";
    $view_controls = array(
        $f->viewControl()->mode($actions, $aria_label)->withActive("Alle")
    );

    $mapping_closure = function ($row, $record, $ui_factory, $environment) {
        return $row
        ->withHeadline($record['question_title'])
        ->withLeadingSymbol(
            $ui_factory->symbol()->icon()->custom('templates/default/images/standard/icon_ques.svg', '')
        )
        ->withSubheadline($record['question_txt'])
        ->withImportantFields(
            array(
                $record['type'],
                'Beantwortet: ' => $record['stats']['total'],
                'Häufigste Antwort: ' => $record['answers'][$record['stats']['most_common']]['title']
            )
        )
        ->withContent(
            $ui_factory->listing()->descriptive([
                'Werte' => $environment['totals']($record['answers']),
                'Beantwortet: ' => (string)$record['stats']['total'],
                'Übersprungen' => (string)$record['stats']['skipped'],
                'Häufigste Antwort: ' => $record['answers'][$record['stats']['most_common']]['title'],
                'Anzahl Häufigste: ' => (string)$record['stats']['most_common_total'],
                'Median: ' => $record['answers'][$record['stats']['median']]['title']
            ])
        )
        ->withAction($ui_factory->button()->standard('zur Frage', '#'));
    };

    $ptable = $f->table()->presentation(
        'Presentation Table', //title
        $view_controls,
        $mapping_closure
    )
    ->withEnvironment(environment());

    //example data
    $data = included_data1();

    //apply data to table and render
    return $renderer->render($ptable->withData($data));
}

function environment()
{
    $totals = function ($answers) {
        $ret = '<table>';
        $ret .= '<tr><td></td>'
            . '<td>Amount</td>'
            . '<td style="padding-left: 10px;">Proportion</td></tr>';

        foreach ($answers as $answer) {
            $ret .= '<tr>'
                . '<td style="padding-right: 10px;">' . $answer['title'] . '</td>'
                . '<td align="right">' . $answer['amount'] . '</td>'
                . '<td align="right">' . $answer['proportion'] . '%</td>'
                . '</tr>';
        }

        $ret .= '</table><br>';
        return $ret;
    };


    $chart = function ($answers) {
        $ret = '<table style="width:350px">';
        foreach ($answers as $answer) {
            $ret .= '<tr style="border-bottom: 1px solid black;">'
                . '<td style="width: 200px;">'
                . $answer['title']
                . '</td><td>'
                . '<div style="background-color:grey; height:20px; width:' . $answer['proportion'] . '%;"></div>'
                . '</td></tr>';
        }
        $ret .= '</table>';
        return $ret;
    };

    return  array(
        'totals' => $totals,
        'chart' => $chart
    );
}

function included_data1()
{
    return array(
        array(
            'type' => 'Single Choice Frage',
            'question_title' => 'Belastbarkeit',
            'question_txt' => 'Wie ausgeprägt ist die Belastbarkeit des / der Auszubildenden?',
            'answers' => array(
                array('title' => 'weniger ausgeprägt', 'amount' => 2, 'proportion' => 20),
                array('title' => 'teilweise ausgeprägt', 'amount' => 0, 'proportion' => 0),
                array('title' => 'ausgeprägt', 'amount' => 6, 'proportion' => 60),
                array('title' => 'deutlich ausgeprägt', 'amount' => 1, 'proportion' => 10),
                array('title' => 'stark ausgeprägt', 'amount' => 0, 'proportion' => 0),
                array('title' => 'sehr stark ausgeprägt', 'amount' => 0, 'proportion' => 0),
                array('title' => 'übermäßig ausgeprägt', 'amount' => 1, 'proportion' => 10)
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
                array('title' => 'weniger ausgeprägt', 'amount' => 0, 'proportion' => 0),
                array('title' => 'teilweise ausgeprägt', 'amount' => 1, 'proportion' => 100),
                array('title' => 'ausgeprägt', 'amount' => 0, 'proportion' => 0),
                array('title' => 'deutlich ausgeprägt', 'amount' => 0, 'proportion' => 0),
                array('title' => 'stark ausgeprägt', 'amount' => 0, 'proportion' => 0),
                array('title' => 'sehr stark ausgeprägt', 'amount' => 0, 'proportion' => 0),
                array('title' => 'übermäßig ausgeprägt', 'amount' => 0, 'proportion' => 0)
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
