<?php

declare(strict_types=1);

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


    //minimize all scripts
    foreach ($mandatory_scripts as $file) {
        $inp = file_get_contents($location . "/" . $file);
//        jsMin should be renewed
//        $jsMin = new JSMin($inp, false);
//        $jsMin->minify();
//        $a_outjsmin[] = $jsMin->out;
        $outAr[] = $inp;
    }
    $timestamp = time();
    $f_time = date("YndHis", $timestamp);
    $comment = "// Build: $f_time \n";
//    $outjsmin = implode("", $a_outjsmin);
    $out = implode("", $outAr);
//    $outjsmin = $comment . $outjsmin;
    $out = $comment . $out;
    $filenamemin = "../scripts/buildrte/rte-min.js";
    $filename = "../scripts/buildrte/rte.js";
//    file_put_contents($filenamemin, $outjsmin);
    file_put_contents($filenamemin, $out);
    file_put_contents($filename, $out);
