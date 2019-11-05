<?php
function base1()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //build viewcontrols
    $actions = array("Alle" => "#", "Mehr als 5 Antworten" => "#");
    $aria_label = "filter entries";
    $view_controls = array(
        $f->viewControl()->mode($actions, $aria_label)->withActive("Alle")
    );

    $mapping_closure = function ($row, $record, $ui_factory, $environment) {
        return $row
        ->withHeadline($record['question_title'])
        ->withSubheadline($record['question_txt'])
        ->withImportantFields(
            array(
                $record['type'],
                'Beantwortet: ' => $record['stats']['total'],
                'Häufigste Antwort: ' => $record['answers'][$record['stats']['most_common']]['title']
            )
        )
        ->withContent(
            $ui_factory->listing()->descriptive(
                array(
                    'Werte' => $environment['totals']($record['answers']),
                    'Chart' => $environment['chart']($record['answers']),
                )
            )
        )
        ->withFurtherFieldsHeadline($record['type'])
        ->withFurtherFields(
            array(
                'Beantwortet: ' => $record['stats']['total'],
                'Übersprungen' => $record['stats']['skipped'],
                'Häufigste Antwort: ' => $record['answers'][$record['stats']['most_common']]['title'],
                'Anzahl Häufigste: ' => $record['stats']['most_common_total'],
                'Median: ' => $record['answers'][$record['stats']['median']]['title']
            )
        )
        ->withAction($ui_factory->button()->standard('zur Frage', '#'));
    };


    //build table
    require('environment.php');

    $ptable = $f->table()->presentation(
        'Presentation Table', //title
        $view_controls,
        $mapping_closure
    )
    ->withEnvironment(environment());

    //example data
    require('included_data1.php');
    $data = included_data1();

    //apply data to table and render
    return $renderer->render($ptable->withData($data));
}
