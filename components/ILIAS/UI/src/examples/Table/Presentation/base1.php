<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Presentation;

/**
 * You can also leave out "further fields" and use alignments instead,
 * add one or more Blocks and Layouts to the content of the row and add an leading image.
 */
function base1()
{
    global $DIC;
    $ui_factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $tpl = $DIC['tpl'];
    $tpl->addCss('components/ILIAS/UI/src/examples/Table/Presentation/presentation_alignment_example.css');

    $actions = array("Alle" => "#", "Mehr als 5 Antworten" => "#");
    $aria_label = "filter entries";
    $view_controls = array(
        $ui_factory->viewControl()->mode($actions, $aria_label)->withActive("Alle")
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
            $ui_factory->layout()->alignment()->horizontal()->dynamicallyDistributed(
                $ui_factory->layout()->alignment()->vertical(
                    $ui_factory->listing()->descriptive([
                        'Werte' => $environment['totals']($record['answers'])
                    ]),
                    $ui_factory->listing()->descriptive([
                        'Chart' => $environment['chart']($record['answers'])
                    ])
                ),
                $ui_factory->listing()->descriptive([
                    '' => $environment['stats']($record)
                ])
            )
        )
        ->withAction($ui_factory->button()->standard('zur Frage', '#'));
    };

    $ptable = $ui_factory->table()->presentation(
        'Presentation Table with Alignments', //title
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
        $ret = '<div class="example_block content"><table>';
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

        $ret .= '</table></div>';
        return $ret;
    };

    $chart = function ($answers) {
        $ret = '<div class="example_block content"><table style="width:100%">';
        foreach ($answers as $answer) {
            $ret .= '<tr style="border-bottom: 1px solid black;">'
                . '<td style="width: 200px;">'
                . $answer['title']
                . '</td><td>'
                . '<div style="background-color:grey; height:20px; width:' . $answer['proportion'] . '%;"></div>'
                . '</td></tr>';
        }
        $ret .= '</table></div>';
        return $ret;
    };

    $stats = function ($answers) {
        global $DIC;
        $ui_factory = $DIC->ui()->factory();
        $ui_renderer = $DIC->ui()->renderer();

        $icon = $ui_factory->symbol()->icon()->custom('templates/default/images/standard/icon_ques.svg', '');

        $ret = '<div class="example_block stats">';
        $ret .= '<h5>' . $ui_renderer->render($icon) . ' ' . $answers['type'] . '</h5>';
        $ret .= '<span class="c-stats--title">Beantwortet:</span> '
            . $answers['stats']['total'] . '<br>'
            . '<span class="c-stats--title">Übersprungen:</span> '
            . $answers['stats']['skipped'] . '<br>'
            . '<span class="c-stats--title">Häufigste Antwort:</span> '
            . $answers['answers'][$answers['stats']['most_common']]['title'] . '<br>'
            . '<span class="c-stats--title">Anzahl Häufigste:</span> '
            . $answers['stats']['most_common_total'] . '<br>'
            . '<span class="c-stats--title">Median:</span> '
            . $answers['answers'][$answers['stats']['median']]['title'];
        $ret .= '</div>';
        return $ret;
    };

    return array(
        'totals' => $totals,
        'chart' => $chart,
        'stats' => $stats
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
