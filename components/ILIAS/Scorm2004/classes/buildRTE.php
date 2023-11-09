<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

//location of the RTE-script-files
$location = "../scripts";
$a_outjsmin = [];
$out = "";

//list all scripts that are needed for the RTE
$mandatory_scripts = array( "sequencer/ADLAuxiliaryResource.js",
                            "sequencer/ADLDuration.js",
                            "sequencer/ADLLaunch.js",
                            "sequencer/ADLObjStatus.js",
                            "sequencer/ADLSeqUtilities.js",
                            "sequencer/ADLSequencer.js",
                            "sequencer/ADLTOC.js",
                            "sequencer/ADLTracking.js",
                            "sequencer/ADLValidRequests.js",
                            "sequencer/Basics.js",
                            "sequencer/SeqActivity.js",
                            "sequencer/SeqActivityTree.js",
                            "sequencer/SeqCondition.js",
                            "sequencer/SeqConditionSet.js",
                            "sequencer/SeqNavRequest.js",
                            "sequencer/SeqObjective.js",
                            "sequencer/SeqObjectiveMap.js",
                            "sequencer/SeqObjectiveTracking.js",
                            "sequencer/SeqRollupRule.js",
                            "sequencer/SeqRollupRuleset.js",
                            "sequencer/SeqRule.js",
                            "sequencer/SeqRuleset.js",
                            "rtemain/main.js",
                            "rtemain/rte.js");
function minimizeJavascriptSimple(string $javascript): string
{
    return preg_replace(
        array("/\s+\n/", "/\n\s+/", "/ +/"),
        array("\n", "\n ", " "),
        $javascript
    );
}

//minimize all scripts
foreach ($mandatory_scripts as $file) {
    $inp = file_get_contents($location . "/" . $file);
    //        jsMin should be renewed
    //        $jsMin = new JSMin($inp, false);
    //        $jsMin->minify();
    //        $a_outjsmin[] = $jsMin->out;
    $a_outjsmin[] = minimizeJavascriptSimple($inp);
    $outAr[] = $inp;
}
$timestamp = time();
$f_time = date("YndHis", $timestamp);
$comment = "// Build: $f_time \n";
$outjsmin = implode("", $a_outjsmin);
$out = implode("", $outAr);
$outjsmin = $comment . $outjsmin;
$out = $comment . $out;
$filenamemin = "../scripts/buildrte/rte-min.js";
$filename = "../scripts/buildrte/rte.js";

echo "write " . $filename;
$check = file_put_contents($filename, $out);
if (!$check) {
    echo(" not successful");
}
echo(" with " . $check . " bytes");

echo "\n</br>\nwrite " . $filenamemin;
$check = file_put_contents($filenamemin, $outjsmin);
if (!$check) {
    echo(" not successful");
}
echo(" with " . $check . " bytes");
