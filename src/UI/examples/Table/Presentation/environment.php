<?php

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
        $ret = '<table style="width:100%">';
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
