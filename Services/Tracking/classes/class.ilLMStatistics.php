<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Roland K�stermann
 * @date	20.02.2006
 */
class ilLMStatistics {

    var $AppletData;
    var $AppletDataInfoString;
    var $Seitenanz;
    var $KapitelZuSeite2;
    var $SessionVonNach2;
    var $Kapitelanz;
    var $KapitelVonNach2;
    var $OberkapitelZuKapitel2;
    var $Seitenname2;
    var $Kapitelname2;
    var $appStats;
    var $from;
    var $to;
    var $user_IDs;
    var $lm_id;
    var $user_selection;

    function ilLMStatistics($obj_id) {
        $this->lm_id = $obj_id;
    }

    function outputHTML() {
        $this->calcStats (0);
    }

    function calcStats($mode) {

        global $tpl, $lng, $ilias, $db, $ilDB;

        if ($mode == 1) { //wenn Aufruf aus Applet
            $from = $this->from;
            $to = $this->to;
            $user_IDs = $this->user_IDs;
            $LehrModulID = $this->lm_ID;
            $user_selection = $this->user_selection;
            $this->appStats = 0;
        } else {
            $_SESSION["il_track_yearf"] = $_POST["yearf"];
            $_SESSION["il_track_yeart"] = $_POST["yeart"];
            $_SESSION["lmID"] = $_POST["lmID"];
            $_SESSION["il_track_monthf"] = $_POST["monthf"];
            $_SESSION["il_track_montht"] = $_POST["montht"];
            $_SESSION["il_track_dayf"] = $_POST["dayf"];
            $_SESSION["il_track_dayt"] = $_POST["dayt"];
            $_SESSION["il_track_stat"] = $_POST["stat"];
            $_SESSION["il_track_language"] = $_POST["language"];
            $_SESSION["il_track_author"] = $_POST["author"];
            $_SESSION["il_track_author1"] = $_POST["author1"];
            $_SESSION["il_track_lm"] = $_POST["lmID"];
            $_SESSION["il_track_tst"] = $_POST["tst"];
            $_SESSION["il_object_type"] = $_POST["object_type"];
            $_SESSION["il_track_stat2"] = $_POST["stat2"];

            $user_selection = $_POST["stat2"];
            $LehrModulID = $_SESSION["lmID"];
            $yearf = $_POST["yearf"];
            $monthf = $_POST["monthf"];
            $dayf = $_POST["dayf"];
            $yeart = $_POST["yeart"];
            $montht = $_POST["montht"];
            $dayt = $_POST["dayt"];
            $from = $yearf."-".$monthf."-".$dayf." 00:00:00";
            $to = $yeart."-".$montht."-".$dayt." 23:59:59";
            $user_IDs = $_SESSION["userSelected_stat"];
        }

        //nur falls mind. ein Benutzer ausgew�hlt wurde starten

        if (count($user_IDs) > 0 || ($user_selection == "all")) {

            //Benutzer-String f�r SQL erzeugen

            //STATS-EINSTELLUNGSOPTIONEN

            //$UTanz = 1000000; //Anzahl der max auslesenden Tracking Daten
            $SessionMaxVerweildauer = 1800; //sekunden
            $IntervallMax[1] = 10;
            $IntervallMax[2] = 30;
            $IntervallMax[3] = 60;
            $IntervallMax[4] = 120;
            $IntervallMax[5] = 600;
            $IntervallMax[6] = $SessionMaxVerweildauer;
            $IntervallSeitenMax[0] = -1;
            $IntervallSeitenMax[1] = 0;
            $IntervallSeitenMax[2] = 1;
            $IntervallSeitenMax[3] = 5;
            $IntervallSeitenMax[4] = 20;
            $IntervallSeitenMax[5] = 50;
            $RankBenoetigteZugriffe = 1; //ben�tigte Seitenzugriffe damit eine Seite ins Ranking kommt
            $RankAnzahl = 10; //Gr��e der Rankings
            //$umlauteAendern = 1; //1=ja
            $KapitelAbdeckungsKennzahlFaktor2 = 0.5;
            $KapitelAbdeckungsKennzahlFaktor3 = 1.0;
            $KapitelAbdeckungsKennzahlFaktor4 = 1.5;
            $KapitelAbdeckungsKennzahlFaktor5 = 2.0;

            //ALLGEMEINE VARIABLEN

            //aus DB:lm_tree

            $q = "SELECT child, parent,depth FROM lm_tree";
            $result = $ilias->db->query($q);
            while ($row = $result->fetchRow()) {
                $vatizumkind[$row[0]] = $row[1];
                $seitenTiefe[$row[0]] = $row[2];
            } //Ende While

            //aus DB:lm_data

            $q = "SELECT obj_id,title,type,lm_id FROM lm_data WHERE lm_id='".$LehrModulID."'";

            $result = $ilias->db->query($q);
            while ($row = $result->fetchRow()) {

                //Ben�tigte Daten zu den Kapitel und Seiten aus der DB auslesen

                if ($row[2] == 'st') {
                    $Kapitelanz ++;
                    $KapitelID[$Kapitelanz] = $row[0];
                    $KapitelName[$Kapitelanz] = $row[1];
                    $KapitelLernModul[$Kapitelanz] = $row[3];
                    $rKapitelID[$row[0]] = $Kapitelanz;
                }
                if ($row[2] == 'pg') {
                    $Seitenanz ++;
                    $SeitenID[$Seitenanz] = $row[0];
                    $SeitenName[$Seitenanz] = $row[1];
                    $SeitenLernModul[$Seitenanz] = $row[3];
                    $rSeitenID[$row[0]] = $Seitenanz;
                }
            }

            //2.SESSIONS bestimmen

            if ($_POST["stat2"] == 'all') {
                $q = "SELECT id, user_id,acc_obj_id,acc_sub_id,session_id, acc_time ".
					"FROM ut_access WHERE acc_obj_id=".$ilDB->quote($LehrModulID, "integer").
					" AND acc_time > ".$ilDB->quote($from, "timestamp")." AND acc_time < ".
					$ilDB->quote($to, "timestamp")." ";
            } else {
                $q = "SELECT id, user_id, acc_obj_id, acc_sub_id, session_id, acc_time ".
				"FROM ut_access WHERE acc_obj_id= ".$ilDB->quote($LehrModulID, "integer").
					" AND acc_time > ".$ilDB->quote($from, "timestamp")." AND acc_time < ".
					$ilDB->quote($to, "timestamp")." AND ".$ilDB->in("user_id", $user_IDs, false, "integer");
            }
            $result = $ilias->db->query($q);

            while ($row = $result->fetchRow()) {

                if (($rSeitenID[$row[3]] != 0) && ($rKapitelID[$vatizumkind[$row[3]]] != 0)) { //�berpr�ft ob die Seite des UT-Eintrages noch in der DB steht

                    if ($row[1] > $UserTopID) {
                        $UserTopID = $row[1];
                    } //Es wird die h�chste User-ID bestimmt

                    $UserZugriffe[$row[1]]++;
                    $SeitenZugriffe[$rSeitenID[$row[3]]]++;
                    $GesamtSeitenZugriffe ++;
                    $KapitelSeitenZugriffe[$rKapitelID[$vatizumkind[$row[3]]]]++;
                    $checkS = false;

                    //�berpr�fen ob Eintrag zu einer Session geh�rt

                    for ($i = 0; $i <= count($SessionCheck[$row[1]]); $i ++) {
                        if ($row[4] == $SessionCheck[$row[1]][$i][0]) {
                            $pos = $SessionCheck[$row[1]][$i][1]; //liefert die session-id
                            $checkS = true;
                        }
                    }

                    //jetzt falls erneuter Seitenzugriff w�hrend einer Session

                    if ($checkS) {

                        //Untersuchen wie lange der Zeitraum zwischen den zwei Besuchen in der Sessions war

                        $SessionGesamtZugriffe ++;
                        $SessionEndSekundeDummy[$pos] = (substr($row[5], -2));
                        $SessionEndMinuteDummy[$pos] = (substr($row[5], -5, 2));
                        $SessionEndStundeDummy[$pos] = (substr($row[5], -8, 2));
                        $SessionEndGesamt = $SessionEndSekundeDummy[$pos] + $SessionEndMinuteDummy[$pos] * 60 + $SessionEndStundeDummy[$pos] * 60 * 60;

                        if (($SessionEndGesamt - $SessionStartGesamt[$pos]) > 0) {
                            $SessionZugriffDifferenz = $SessionEndGesamt - $SessionStartGesamt[$pos];
                        }

                        if ($SessionZugriffDifferenz < $SessionMaxVerweildauer) { //Falls Zeitdiff zwischen 2 Zugriffen kleiner der Vorgabe ist

                            //Statistik: Die Verweildauer zwischen den Klicks wird in in einer Klasse eingeteilt

                            if ($SessionZugriffDifferenz <= $IntervallMax[1]) {
                                $IntervallAnz[1]++;
                            } else
                            if ($SessionZugriffDifferenz <= $IntervallMax[2]) {
                                $IntervallAnz[2]++;
                            } else
                            if ($SessionZugriffDifferenz <= $IntervallMax[3]) {
                                $IntervallAnz[3]++;
                            } else
                            if ($SessionZugriffDifferenz <= $IntervallMax[4]) {
                                $IntervallAnz[4]++;
                            } else
                            if ($SessionZugriffDifferenz <= $IntervallMax[5]) {
                                $IntervallAnz[5]++;
                            } else {
                                $IntervallAnz[6]++;
                            }

                            $MessbareSessionZugriffe ++;

                            $SessionsVerweildauer[$MessbareSessionZugriffe] = $SessionZugriffDifferenz; //Differenz abspeichern

                            if ($SessionZugriffe[$pos] == 1) {
                                $MessbareSessions ++;
                                $UserSessionAnz[$row[1]]++;
                            }

                            $SessionZugriffe[$pos]++;
                            $SeitenVerweildauerListe[$rSeitenID[$row[3]]][$SeitenMessbareZugriffe[$rSeitenID[$row[3]]]] = $SessionZugriffDifferenz;
                            $SeitenMessbareZugriffe[$rSeitenID[$row[3]]]++;
                            $SessionEndTime[$pos] = $row[5];
                            $SessionStartGesamt[$pos] = $SessionEndGesamt;
                            $SessionGesamtDauerAll += $SessionZugriffDifferenz;
                            $SessionGesamtDauer[$pos] += $SessionZugriffDifferenz;
                            $UserGesamtSessionsDauer[$row[1]] += $SessionZugriffDifferenz;
                            $UserSessionZugriffe[$row[1]]++;
                            $SeiteGesamtVerweilZeit[$rSeitenID[$row[3]]] += $SessionZugriffDifferenz;

                            $SessionVonNach[$SessionQuellSeite[$pos]][$rSeitenID[$row[3]]]++;
                            $KapitelVonNach[$rKapitelID[$vatizumkind[$SeitenID[$SessionQuellSeite[$pos]]]]][$rKapitelID[$vatizumkind[$row[3]]]]++;
                            $SessionQuellSeite[$pos] = $rSeitenID[$row[3]];

                        } else {
                            $checkS = false;
                        }

                    } //Ende  if($checkS)

                    //falls erster Seitenzugriff einer Session

                    if ($checkS == false) {

                        $Sessionanz ++;

                        $aktSessionAnzahlUser = count($SessionCheck[$row[1]]) + 1; //Sessionanzahl des Users erh�hen
                        $SessionCheck[$row[1]][$aktSessionAnzahlUser][0] = $row[4];
                        $SessionCheck[$row[1]][$aktSessionAnzahlUser][1] = $Sessionanz;

                        $SessionZugriffe[$Sessionanz] = 1;
                        $SessionID[$Sessionanz] = $row[4];
                        $SessionUserID[$Sessionanz] = $row[1];
                        $SessionStartTime[$Sessionanz] = $row[5];

                        $SessionQuellSeite[$Sessionanz] = $rSeitenID[$row[3]];
                        $SessionStartSekunde[$Sessionanz] = (substr($SessionStartTime[$Sessionanz], -2));
                        $SessionStartMinute[$Sessionanz] = (substr($SessionStartTime[$Sessionanz], -5, 2));
                        $SessionStartStunde[$Sessionanz] = (substr($SessionStartTime[$Sessionanz], -8, 2));
                        $SessionStartGesamt[$Sessionanz] = $SessionStartSekunde[$Sessionanz] + $SessionStartMinute[$Sessionanz] * 60 + $SessionStartStunde[$Sessionanz] * 60 * 60;

                    }
                }
            } //Ende While


            //STATISTIKEN

            //SEITENSTATS

            //meist-wenigst besuchteste Seiten abfragen

            for ($i = 1; $i <= $Seitenanz; $i ++) {

                if ($SeitenZugriffe[$i] <= $IntervallSeitenMax[1]) {
                    $IntervallSeitenAnz[1]++;
                } else
                if ($SeitenZugriffe[$i] <= $IntervallSeitenMax[2]) {
                    $IntervallSeitenAnz[2]++;
                } else
                if ($SeitenZugriffe[$i] <= $IntervallSeitenMax[3]) {
                    $IntervallSeitenAnz[3]++;
                } else
                if ($SeitenZugriffe[$i] <= $IntervallSeitenMax[4]) {
                    $IntervallSeitenAnz[4]++;
                } else
                if ($SeitenZugriffe[$i] <= $IntervallSeitenMax[5]) {
                    $IntervallSeitenAnz[5]++;
                } else {
                    $IntervallSeitenAnz[6]++;
                }
            }

            //VERWEILDAUER-STATS

            //SessionsVerweildauer orden

            if (count($SessionsVerweildauer) > 0) {
                sort($SessionsVerweildauer);
            }

            //SessionDurchschnittsDauer bestimmen
            if ($MessbareSessions > 0) {
                $SessionDurchschnittsDauer = $SessionGesamtDauerAll / $MessbareSessions;
            }

            //SeitenVerweildauerDurchschnitt bestimmen
            if ($MessbareSessionZugriffe > 0) {
                $SeitenVerweildauerDurchschnitt = $SessionGesamtDauerAll / $MessbareSessionZugriffe;
            }

            //SeitenVerweildauerSpannweite bestimmen

            $SeitenVerweildauerSpannweite = $SessionsVerweildauer[$MessbareSessionZugriffe -1] - $SessionsVerweildauer[0];

            //Verweildauer Median bestimmen

            if ($MessbareSessionZugriffe % 2 == 0) {

                $VerweildauerMedianPosA = ($MessbareSessionZugriffe) / 2;
                $VerweildauerMedianPosB = ($MessbareSessionZugriffe +1) / 2 + 1;
                $VerweildauerMedian = ($SessionsVerweildauer[$VerweildauerMedianPosA -1] + $SessionsVerweildauer[$VerweildauerMedianPosB -1]) / 2;
            } else {
                $VerweildauerMedianPos = ($MessbareSessionZugriffe +1) / 2;
                $VerweildauerMedian = $SessionsVerweildauer[$VerweildauerMedianPos -1];
            }

            $SeitenVerweildauerVarianz = ilLMStatistics::varianzSV($SessionsVerweildauer, $SeitenVerweildauerDurchschnitt);

            $SeitenVerweildauerStandartAbw = sqrt($SeitenVerweildauerVarianz);

            if ($SeitenVerweildauerStandartAbw > 0) {
                $SeitenVerweildauerVarKoef = $SeitenVerweildauerDurchschnitt / $SeitenVerweildauerStandartAbw;
            }

            //GesamtVerweilzeit f�r Seiten maxordnen

            for ($i = 1; $i <= $Seitenanz; $i ++) {
                if ($SeitenMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                    $SeitenGesamtVerweilMax[$i] = $SeiteGesamtVerweilZeit[$i];
                    $SeitenGesamtVerweilMaxID[$i] = $i;
                }
            }

            if (count($SeitenGesamtVerweilMax) > 0) {
                array_multisort($SeitenGesamtVerweilMax, SORT_DESC, $SeitenGesamtVerweilMaxID);
            }

            //GesamtVerweilzeit f�r Seiten minordnen

            for ($i = 1; $i <= $Seitenanz; $i ++) {
                if ($SeitenMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                    $SeitenGesamtVerweilMin[$i] = $SeiteGesamtVerweilZeit[$i];
                    $SeitenGesamtVerweilMinID[$i] = $i;
                }
            }

            if (count($SeitenGesamtVerweilMin) > 0) {
                array_multisort($SeitenGesamtVerweilMin, SORT_ASC, $SeitenGesamtVerweilMinID);
            }

            //durchsch. Verweildauer f�r Seiten max ordnen

            for ($i = 1; $i <= $Seitenanz; $i ++) {
                if ($SeitenMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                    $SeitenDurchschnittVerweilMax[$i] = $SeiteGesamtVerweilZeit[$i] / $SeitenMessbareZugriffe[$i];
                    $SeitenDurchschnittVerweilMaxID[$i] = $i;
                }
            }
            if (count($SeitenDurchschnittVerweilMax) > 0) {
                array_multisort($SeitenDurchschnittVerweilMax, SORT_DESC, $SeitenDurchschnittVerweilMaxID);
            }

            //durchsch. Verweildauer f�r Seiten min  ordnen

            for ($i = 1; $i <= $Seitenanz; $i ++) {
                if ($SeitenMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                    $SeitenDurchschnittVerweilMin[$i] = $SeiteGesamtVerweilZeit[$i] / $SeitenMessbareZugriffe[$i];
                    $SeitenDurchschnittVerweilMinID[$i] = $i;
                }
            }
            if (count($SeitenDurchschnittVerweilMin) > 0) {
                array_multisort($SeitenDurchschnittVerweilMin, SORT_ASC, $SeitenDurchschnittVerweilMinID);
            }

            //USER-STATS:

            //Gesamte Sessiondauer der Einzelnen User ordnen

            for ($i = 1; $i <= $UserTopID; $i ++) {
                if ($UserGesamtSessionsDauer[$i] > 0) {
                    $UserGesamtSessionsDauerMax[$i] = $UserGesamtSessionsDauer[$i];
                    $UserGesamtSessionsDauerMaxID[$i] = $i;
                } else {
                    $UserGesamtSessionsDauerMax[$i] = "";
                    $UserGesamtSessionsDauerMaxID[$i] = "";
                }
            }
            if (count($UserGesamtSessionsDauerMax) > 0) {
                array_multisort($UserGesamtSessionsDauerMax, SORT_DESC, $UserGesamtSessionsDauerMaxID);
            }

            //SessionAnz der Einzelnen User ordnen

            for ($i = 1; $i <= $UserTopID; $i ++) {
                if ($UserSessionAnz[$i] > 0) {
                    $UserSessionAnzMax[$i] = $UserSessionAnz[$i];
                    $UserSessionAnzMaxID[$i] = $i;
                } else {
                    $UserSessionAnzMax[$i] = "";
                    $UserSessionAnzMaxID[$i] = "";
                }
            }
            if (count($UserSessionAnzMax) > 0) {
                array_multisort($UserSessionAnzMax, SORT_DESC, $UserSessionAnzMaxID);
            }

            //beteiligte User

            for ($i = 0; $i <= $UserTopID; $i ++) {
                if ($UserZugriffe[$i] > 0) {
                    $UserAnz ++;
                }
            }
            //Auswertbare User (Zeit)
            for ($i = 0; $i <= $UserTopID; $i ++) {
                if ($UserSessionAnz[$i] > 0) {
                    $UserMessbarAnz ++;
                }
            }

            //SEITENZUGRIFF-STATS

            //Zugriffe f�r Seiten maxordnen

            for ($i = 1; $i <= $Seitenanz; $i ++) {

                if ($SeitenZugriffe[$i] > 0) {
                    $SeitenZugriffMax[$i] = $SeitenZugriffe[$i];
                    $SeitenZugriffMaxID[$i] = $i;
                } else {
                    $SeitenZugriffMax[$i] = "";
                    $SeitenZugriffMaxID[$i] = "";
                }
            }

            if (count($SeitenZugriffMax) > 0) {
                array_multisort($SeitenZugriffMax, SORT_DESC, $SeitenZugriffMaxID);
            }

            //Zugriffe f�r Seiten minordnen

            for ($i = 1; $i <= $Seitenanz; $i ++) {

                if ($SeitenZugriffe[$i] > 0) {
                    $SeitenZugriffMin[$i] = $SeitenZugriffe[$i];
                    $SeitenZugriffMinID[$i] = $i;
                } else {
                    $SeitenZugriffMin[$i] = "";
                    $SeitenZugriffMinID[$i] = $i;
                }
            }

            if (count($SeitenZugriffMin) > 0) {
                array_multisort($SeitenZugriffMin, SORT_ASC, $SeitenZugriffMinID);
            }

            //Seitenzugriffe Median bestimmen

            if (count($SeitenZugriffMax) % 2 == 0) {

                $SeitenZugriffeMedianPosA = (count($SeitenZugriffMax)) / 2;
                $SeitenZugriffeMedianPosB = (count($SeitenZugriffMax) + 1) / 2 + 1;
                $SeitenZugriffeMedian = ($SeitenZugriffMax[$SeitenZugriffeMedianPosA -1] + $SeitenZugriffMax[$SeitenZugriffeMedianPosB -1]) / 2;
            } else {
                $SeitenZugriffeMedianPos = (count($SeitenZugriffMax) + 1) / 2;
                $SeitenZugriffeMedian = $SeitenZugriffMax[$SeitenZugriffeMedianPos -1];
            }

            //SeitenZugriffe Spannweite bestimmen

            $SeitenZugriffeSpannweite = $SeitenZugriffMax[0] - $SeitenZugriffMax[count($SeitenZugriffMax) - 1];

            //SeitenZugriffe Modus bestimmen
            $MaxZW = 0;
            for ($i = 1; $i <= $Seitenanz; $i ++) {
                $ZugriffsWert[$SeitenZugriffe[$i]]++;
                if ($SeitenZugriffe[$i] > $MaxZW) {
                    $MaxZW = $SeitenZugriffe[$i];
                }
            }
            for ($i = 0; $i <= $MaxZW; $i ++) {
                if ($ZugriffsWert[$i] > $ZugriffsWertMaxAus) {
                    $ZugriffsWertMaxAus = $ZugriffsWert[$i];
                    $ZugriffsWertMax = $i;
                }
            }

            //SeitenZugriffe Var, Stdabw, Korr

            if ($Seitenanz > 0) {
                $SeitenZugriffeVarianz = ilLMStatistics::varianzSV($SeitenZugriffMax, $GesamtSeitenZugriffe / $Seitenanz);
            }
            $SeitenZugriffeStandartAbw = sqrt($SeitenZugriffeVarianz);

            if (($Seitenanz > 0) && ($GesamtSeitenZugriffe / $Seitenanz) > 0) {
                $SeitenZugriffeVarKoef = $SeitenZugriffeStandartAbw / ($GesamtSeitenZugriffe / $Seitenanz);
            }

            //SESSIONSTATS

            //Zusatz2: l�ngste Session bestimmen

            for ($i = 1; $i <= $Sessionanz; $i ++) {
                $SessionGesamtDauerMax[$i] = $SessionGesamtDauer[$i];
                $SessionGesamtDauerMaxID[$i] = $i;
            }

            if (count($SessionGesamtDauerMax) > 0) {
                array_multisort($SessionGesamtDauerMax, SORT_DESC, $SessionGesamtDauerMaxID);
            }

            if ($Sessionanz > 0) {
                $SessionDurschnittsZeit = $GesamtSeitenZugriffe / $Sessionanz;
            }

            //EINZELSEITEN STATS

            for ($i = 1; $i <= $Seitenanz; $i ++) {
                if ($SeitenMessbareZugriffe[$i] > 0) {
                    $EinzelSeitenVarianz[$i] = ilLMStatistics::varianzSV($SeitenVerweildauerListe[$i], $SeiteGesamtVerweilZeit[$i] / $SeitenMessbareZugriffe[$i]);
                    if ($EinzelSeitenVarianz[$i] > 0) {
                        $EinzelSeitenVarKoef[$i] = ($SeiteGesamtVerweilZeit[$i] / $SeitenMessbareZugriffe[$i]) / sqrt($EinzelSeitenVarianz[$i]);
                    }
                }
            }
            //EinzelSeitenvarianzen max
            for ($i = 1; $i <= $Seitenanz; $i ++) {
                if ($SeitenMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                    $EinzelSeitenVarMax[$i] = $EinzelSeitenVarianz[$i];
                    $EinzelSeitenVarMaxID[$i] = $i;
                }
            }
            if (count($EinzelSeitenVarMax) > 0) {
                array_multisort($EinzelSeitenVarMax, SORT_DESC, $EinzelSeitenVarMaxID);
            }

            //EinzelSeitenvarianzen min
            for ($i = 1; $i <= $Seitenanz; $i ++) {
                if ($SeitenMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                    $EinzelSeitenVarMin[$i] = $EinzelSeitenVarianz[$i];
                    $EinzelSeitenVarMinID[$i] = $i;
                }
            }
            if (count($EinzelSeitenVarMin) > 0) {
                array_multisort($EinzelSeitenVarMin, SORT_ASC, $EinzelSeitenVarMinID);
            }

            //EinzelSeitenVarKoef max
            for ($i = 1; $i <= $Seitenanz; $i ++) {
                if ($SeitenMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                    $EinzelSeitenVarKoefMax[$i] = $EinzelSeitenVarKoef[$i];
                    $EinzelSeitenVarKoefMaxID[$i] = $i;
                }
            }
            if (count($EinzelSeitenVarKoefMax) > 0) {
                array_multisort($EinzelSeitenVarKoefMax, SORT_DESC, $EinzelSeitenVarKoefMaxID);
            }

            //EinzelSeitenVarKoef min
            for ($i = 1; $i <= $Seitenanz; $i ++) {
                if ($SeitenMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                    $EinzelSeitenVarKoefMin[$i] = $EinzelSeitenVarKoef[$i];
                    $EinzelSeitenVarKoefMinID[$i] = $i;
                }
            }
            if (count($EinzelSeitenVarKoefMin) > 0) {
                array_multisort($EinzelSeitenVarKoefMin, SORT_ASC, $EinzelSeitenVarKoefMinID);
            }

            //KAPITEL STATS:

            //Seitenstats auf Kapitel �bertragen

            $knoten = 0;
            $tiefe = 0;
            $KapitelAbdeckungsKennzahl1 = 1; //untere Klassengrenze;

            for ($i = 1; $i <= $Seitenanz; $i ++) {

                $knoten = $SeitenID[$i];
                $tiefe = $seitenTiefe[$SeitenID[$i]];

                while ($tiefe > 1) {

                    $knoten = $vatizumkind[$knoten];
                    $KapitelZugriffe[$rKapitelID[$knoten]] += $SeitenZugriffe[$i];
                    $KapitelGesamtVerweilZeit[$rKapitelID[$knoten]] += $SeiteGesamtVerweilZeit[$i];
                    $KapitelMessbareZugriffe[$rKapitelID[$knoten]] += $SeitenMessbareZugriffe[$i];
                    $KapitelSeitenSumme[$rKapitelID[$knoten]]++;
                    if ($tiefe == 2) {
                        $KapitelGesamtSeitenAnzEbene2 ++;
                    }
                    if ($tiefe == 3) {
                        $KapitelGesamtSeitenAnzEbene3 ++;
                    }
                    if ($tiefe == 4) {
                        $KapitelGesamtSeitenAnzEbene4 ++;
                    }
                    if ($tiefe == 5) {
                        $KapitelGesamtSeitenAnzEbene5 ++;
                    }
                    $tiefe = $seitenTiefe[$knoten];
                }
            }

            //Zugriffe f�r Kapitel maxordnen

            for ($i = 1; $i <= $Kapitelanz; $i ++) {

                if ($seitenTiefe[$KapitelID[$i]] == 2) {

                    $KapitelTiefe2Anzahl ++;

                    if ($KapitelZugriffe[$i] > 0) {
                        $KapitelZugriffMax2[$i] = $KapitelZugriffe[$i];
                        $KapitelZugriffMaxID2[$i] = $i;
                    } else {
                        $KapitelZugriffMax2[$i] = "";
                        $KapitelZugriffMaxID2[$i] = $i;
                    }
                }
                if ($seitenTiefe[$KapitelID[$i]] == 3) {
                    $KapitelTiefe3Anzahl ++;
                    if ($KapitelZugriffe[$i] > 0) {
                        $KapitelZugriffMax3[$i] = $KapitelZugriffe[$i];
                        $KapitelZugriffMaxID3[$i] = $i;
                    } else {
                        $KapitelZugriffMax3[$i] = "";
                        $KapitelZugriffMaxID3[$i] = $i;
                    }
                }
                if ($seitenTiefe[$KapitelID[$i]] == 4) {
                    $KapitelTiefe4Anzahl ++;
                    if ($KapitelZugriffe[$i] > 0) {
                        $KapitelZugriffMax4[$i] = $KapitelZugriffe[$i];
                        $KapitelZugriffMaxID4[$i] = $i;
                    } else {
                        $KapitelZugriffMax4[$i] = "";
                        $KapitelZugriffMaxID4[$i] = $i;
                    }
                }
                if ($seitenTiefe[$KapitelID[$i]] >= 5) {
                    $KapitelTiefe5Anzahl ++;
                    if ($KapitelZugriffe[$i] > 0) {
                        $KapitelZugriffMax5[$i] = $KapitelZugriffe[$i];
                        $KapitelZugriffMaxID5[$i] = $i;
                    } else {
                        $KapitelZugriffMax5[$i] = "";
                        $KapitelZugriffMaxID5[$i] = $i;
                    }
                }
            }

            if (count($KapitelZugriffMax2) > 0) {
                array_multisort($KapitelZugriffMax2, SORT_DESC, $KapitelZugriffMaxID2);
            }
            if (count($KapitelZugriffMax3) > 0) {
                array_multisort($KapitelZugriffMax3, SORT_DESC, $KapitelZugriffMaxID3);
            }
            if (count($KapitelZugriffMax4) > 0) {
                array_multisort($KapitelZugriffMax4, SORT_DESC, $KapitelZugriffMaxID4);
            }
            if (count($KapitelZugriffMax5) > 0) {
                array_multisort($KapitelZugriffMax5, SORT_DESC, $KapitelZugriffMaxID5);
            }

            //Zugriffe f�r Kapitel minordnen

            for ($i = 1; $i <= $Kapitelanz; $i ++) {

                if ($KapitelZugriffe[$i] > 0) {
                    $KapitelZugriffMin[$i] = $KapitelZugriffe[$i];
                    $KapitelZugriffMinID[$i] = $i;
                } else {
                    $KapitelZugriffMin[$i] = "";
                    $KapitelZugriffMinID[$i] = $i;
                }
            }

            if (count($KapitelZugriffMin) > 0) {
                array_multisort($KapitelZugriffMin, SORT_ASC, $KapitelZugriffMinID);
            }

            //GesamtVerweilzeit f�r Kapitel maxordnen

            for ($i = 1; $i <= $Kapitelanz; $i ++) {
                if ($seitenTiefe[$KapitelID[$i]] == 2) {
                    if ($KapitelMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                        $KapitelGesamtVerweilMax2[$i] = $KapitelGesamtVerweilZeit[$i];
                        $KapitelGesamtVerweilMaxID2[$i] = $i;
                    }
                }
                if ($seitenTiefe[$KapitelID[$i]] == 3) {
                    if ($KapitelMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                        $KapitelGesamtVerweilMax3[$i] = $KapitelGesamtVerweilZeit[$i];
                        $KapitelGesamtVerweilMaxID3[$i] = $i;
                    }
                }
                if ($seitenTiefe[$KapitelID[$i]] == 4) {
                    if ($KapitelMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                        $KapitelGesamtVerweilMax4[$i] = $KapitelGesamtVerweilZeit[$i];
                        $KapitelGesamtVerweilMaxID4[$i] = $i;
                    }
                }
                if ($seitenTiefe[$KapitelID[$i]] >= 5) {
                    if ($KapitelMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                        $KapitelGesamtVerweilMax5[$i] = $KapitelGesamtVerweilZeit[$i];
                        $KapitelGesamtVerweilMaxID5[$i] = $i;
                    }
                }
            }

            if (count($KapitelGesamtVerweilMax2) > 0) {
                array_multisort($KapitelGesamtVerweilMax2, SORT_DESC, $KapitelGesamtVerweilMaxID2);
            }
            if (count($KapitelGesamtVerweilMax3) > 0) {
                array_multisort($KapitelGesamtVerweilMax3, SORT_DESC, $KapitelGesamtVerweilMaxID3);
            }
            if (count($KapitelGesamtVerweilMax4) > 0) {
                array_multisort($KapitelGesamtVerweilMax4, SORT_DESC, $KapitelGesamtVerweilMaxID4);
            }
            if (count($KapitelGesamtVerweilMax5) > 0) {
                array_multisort($KapitelGesamtVerweilMax5, SORT_DESC, $KapitelGesamtVerweilMaxID5);
            }

            //GesamtVerweilzeit f�r Kapitel minordnen
            for ($i = 1; $i <= $Kapitelanz; $i ++) {
                if ($KapitelMessbareZugriffe[$i] >= $RankBenoetigteZugriffe) {
                    $KapitelGesamtVerweilMin[$i] = $KapitelGesamtVerweilZeit[$i];
                    $KapitelGesamtVerweilMinID[$i] = $i;
                }
            }

            if (count($KapitelGesamtVerweilMin) > 0) {
                array_multisort($KapitelGesamtVerweilMin, SORT_ASC, $KapitelGesamtVerweilMinID);
            }

            //SeitenSumme f�r Kapitel maxordnen

            for ($i = 1; $i <= $Kapitelanz; $i ++) {
                $KapitelSeitenSummeMax[$i] = $KapitelSeitenSumme[$i];
                $KapitelSeitenSummeMaxID[$i] = $i;
            }

            if (count($KapitelGesamtVerweilMax) > 0) {
                array_multisort($KapitelSeitenSummeMax, SORT_DESC, $KapitelSeitenSummeMaxID);
            }

            //durchschnittliche Seitenzugriffe je Kapiteltiefe
            $summe = 0;
            for ($i = 0; $i <= $Kapitelanz; $i ++) {
                $summe += $KapitelZugriffMax2[$i];
            }
            if ($KapitelGesamtSeitenAnzEbene2 > 0) {
                $KapitelDurchZugriffe2 = $summe / $KapitelGesamtSeitenAnzEbene2;
            }
            $summe = 0;
            for ($i = 0; $i <= $Kapitelanz; $i ++) {
                $summe += $KapitelZugriffMax3[$i];
            }
            if ($KapitelGesamtSeitenAnzEbene3 > 0) {
                $KapitelDurchZugriffe3 = $summe / $KapitelGesamtSeitenAnzEbene3;
            }
            $summe = 0;
            for ($i = 0; $i <= $Kapitelanz; $i ++) {
                $summe += $KapitelZugriffMax4[$i];
            }
            if ($KapitelGesamtSeitenAnzEbene4 > 0) {
                $KapitelDurchZugriffe4 = $summe / $KapitelGesamtSeitenAnzEbene4;
            }
            $summe = 0;
            for ($i = 0; $i <= $Kapitelanz; $i ++) {
                $summe += $KapitelZugriffMax5[$i];
            }
            if ($KapitelGesamtSeitenAnzEbene5 > 0) {
                $KapitelDurchZugriffe5 = $summe / $KapitelGesamtSeitenAnzEbene5;
            }

            for ($i = 1; $i <= $Seitenanz; $i ++) {
                $knoten = $SeitenID[$i];
                $tiefe = $seitenTiefe[$SeitenID[$i]];

                while ($tiefe > 1) {
                    $knoten = $vatizumkind[$knoten];
                    if (($seitenTiefe[$knoten] == 2) && ($KapitelDurchZugriffe2 > 0)) {
                        if ($SeitenZugriffe[$i] >= $KapitelAbdeckungsKennzahl1) {
                            $KapitelAbgedeckteSeiten1[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe2 * $KapitelAbdeckungsKennzahlFaktor2) {
                            $KapitelAbgedeckteSeiten2[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe2 * $KapitelAbdeckungsKennzahlFaktor3) {
                            $KapitelAbgedeckteSeiten3[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe2 * $KapitelAbdeckungsKennzahlFaktor4) {
                            $KapitelAbgedeckteSeiten4[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe2 * $KapitelAbdeckungsKennzahlFaktor5) {
                            $KapitelAbgedeckteSeiten5[$rKapitelID[$knoten]]++;
                        }
                    }
                    if (($seitenTiefe[$knoten] == 3) && ($KapitelDurchZugriffe3 > 0)) {
                        if ($SeitenZugriffe[$i] >= $KapitelAbdeckungsKennzahl1) {
                            $KapitelAbgedeckteSeiten1[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe3 * $KapitelAbdeckungsKennzahlFaktor2) {
                            $KapitelAbgedeckteSeiten2[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe3 * $KapitelAbdeckungsKennzahlFaktor3) {
                            $KapitelAbgedeckteSeiten3[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe3 * $KapitelAbdeckungsKennzahlFaktor4) {
                            $KapitelAbgedeckteSeiten4[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe3 * $KapitelAbdeckungsKennzahlFaktor5) {
                            $KapitelAbgedeckteSeiten5[$rKapitelID[$knoten]]++;
                        }
                    }
                    if (($seitenTiefe[$knoten] == 4) && ($KapitelDurchZugriffe4 > 0)) {
                        if ($SeitenZugriffe[$i] >= $KapitelAbdeckungsKennzahl1) {
                            $KapitelAbgedeckteSeiten1[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe4 * $KapitelAbdeckungsKennzahlFaktor2) {
                            $KapitelAbgedeckteSeiten2[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe4 * $KapitelAbdeckungsKennzahlFaktor3) {
                            $KapitelAbgedeckteSeiten3[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe4 * $KapitelAbdeckungsKennzahlFaktor4) {
                            $KapitelAbgedeckteSeiten4[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe4 * $KapitelAbdeckungsKennzahlFaktor5) {
                            $KapitelAbgedeckteSeiten5[$rKapitelID[$knoten]]++;
                        }
                    }
                    if (($seitenTiefe[$knoten] >= 5) && ($KapitelDurchZugriffe5 > 0)) {
                        if ($SeitenZugriffe[$i] >= $KapitelAbdeckungsKennzahl1) {
                            $KapitelAbgedeckteSeiten1[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe5 * $KapitelAbdeckungsKennzahlFaktor2) {
                            $KapitelAbgedeckteSeiten2[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe5 * $KapitelAbdeckungsKennzahlFaktor3) {
                            $KapitelAbgedeckteSeiten3[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe5 * $KapitelAbdeckungsKennzahlFaktor4) {
                            $KapitelAbgedeckteSeiten4[$rKapitelID[$knoten]]++;
                        }
                        if ($SeitenZugriffe[$i] >= $KapitelDurchZugriffe5 * $KapitelAbdeckungsKennzahlFaktor5) {
                            $KapitelAbgedeckteSeiten5[$rKapitelID[$knoten]]++;
                        }
                    }
                    $tiefe = $seitenTiefe[$knoten];
                }

            }

            //BEGINN DER AUSGABE
            /**
			 	*Im Feld $SeitenStatsName[] wird jeweils der Name der anzuzeigenden Option gespeichert der dann in der der linken Spalte ausgegeben wird.
			*In $SeitenStatsWert[] wird der dazugeh�rige Optionswert eingetragen.
			*Ein Slider ($slider) wird verwendet um die Optionen leichter zu entfernen bzw. zu verschieben.
			*	Soll zB eine neue Option hinzugef�gt werden kann man das mit den Eintr�gen...
			    *	    	$SeitenStatsName[$slider3]="Name der neuen Option";
			    *		$SeitenStatsWert[$slider3]="Wert der neuen Option";
			    *		$slider3++;
			    *	...bewerkstelligt werden.
			 	*/

            $TNA = 300; //Strings werden auf diesen Wert gek�rzt
            $hop = 3; //Dicke zwischen den Zeilen. Um zB Optionen in der Ausgabe voneinander abzugrenzen

            //SEITENSTATS

            //Funktion zum umrechnen von Sekunden in Stunden

            /*			//wenn die Option umlauteAendern eingeschaltet ist (wert=1) dann werden bei der Ausgabe die Umlaute in der Ausgabe ge�ndert

            if ($umlauteAendern == 1) {
            $d1 = array ("ä", "ö", "ü", "Ä", "Ö", "Ü");
            $d2 = array ("ae", "oe", "ue", "Ae", "Oe", "Ue");
            }
            */


            $slider = 1;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_number"); //$lng->txt("stats_page_number")
            $SeitenStatsWert[$slider] = round($Seitenanz);
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_number_access"); //"Anzahl Seitenzugriffe";
            $SeitenStatsWert[$slider] = round($GesamtSeitenZugriffe)."z";
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_total_dwell_time"); //"Gesamte-SeitenVerweilzeit";
            $SeitenStatsWert[$slider] =  ilLMStatistics::s_to_h($SessionGesamtDauerAll)." (".round($SessionGesamtDauerAll)."s)";
            $slider ++;
            if ($Seitenanz > 0) {
                $SeitenStatsName[$slider] = $lng->txt("stats_page_average_access"); //"Seitenzugriffe-Mittelwert";
                $SeitenStatsWert[$slider] = round(($GesamtSeitenZugriffe / $Seitenanz), 2)."z";
                $slider ++;
            }
            $SeitenStatsName[$slider] = $lng->txt("stats_page_median_access"); //"Seitenzugriffe-Median";
            $SeitenStatsWert[$slider] = round($SeitenZugriffeMedian)."z";
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_mode_access"); //"Seitenzugriffe-Modus:";
            $SeitenStatsWert[$slider] = $lng->txt("stats_occurrence").": ".round($ZugriffsWertMax)." (#: ".$ZugriffsWertMaxAus." )";
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_range_access"); //"Seitenzugriffe-Spannweite";
            $SeitenStatsWert[$slider] = round($SeitenZugriffeSpannweite)."z";
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_variance_access"); //"Seitenzugriffe-Varianz";
            $SeitenStatsWert[$slider] = round($SeitenZugriffeVarianz, 2);
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_deviation_access"); //"Seitenzugriffe-Standartabweichung";
            $SeitenStatsWert[$slider] = round($SeitenZugriffeStandartAbw, 2)."z";
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_varcoeff_access"); //"Seitenzugriffer-VarKoef";
            $SeitenStatsWert[$slider] = round($SeitenZugriffeVarKoef, 2);
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_average_dwell_time"); //"SeitenVerweildauer-Mittelwert";
            $SeitenStatsWert[$slider] = round($SeitenVerweildauerDurchschnitt, 2)."s";
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_median_dwell_time"); //"SeitenVerweildauer-Median";
            $SeitenStatsWert[$slider] = round($VerweildauerMedian);
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_range_dwell_time"); //"SeitenVerweildauer-Spannweite";
            $SeitenStatsWert[$slider] = round($SeitenVerweildauerSpannweite)."s";
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_variance_dwell_time"); //"SeitenVerweildauer-Varianz";
            $SeitenStatsWert[$slider] = round($SeitenVerweildauerVarianz, 2);
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_deviation_dwell_time"); //"SeitenVerweildauer-Standartabweichung";
            $SeitenStatsWert[$slider] = round($SeitenVerweildauerStandartAbw, 2)."s";
            $slider ++;
            $SeitenStatsName[$slider] = $lng->txt("stats_page_varcoeff_dwell_time"); //"SeitenVerweildauer-VarKoef";
            $SeitenStatsWert[$slider] = round($SeitenVerweildauerVarKoef, 2);
            $slider ++;
            $slider = count($SeitenStatsWert);

            $slider += 2;

            $SeitenStatsName[$slider] = "<b>".$lng->txt("stats_page_longest_total_dwell_time")."</b>"; //"<b>Laengste GesamtVerweildauer:</b>";
            $SeitenStatsWert[$slider] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SeitenStatsName[$i +1 + $slider] = ($i +1).". ".str_replace($d1, $d2, substr($SeitenName[$SeitenGesamtVerweilMaxID[$i]], 0, $TNA));
                $SeitenStatsWert[$i +1 + $slider] =  ilLMStatistics::s_to_h($SeitenGesamtVerweilMax[$i])." (".
                (is_numeric($SeitenGesamtVerweilMax[$i])?$SeitenGesamtVerweilMax[$i]:"0")."s, mZ:".(is_numeric($SeitenMessbareZugriffe[$SeitenGesamtVerweilMaxID[$i]])?$SeitenMessbareZugriffe[$SeitenGesamtVerweilMaxID[$i]]:"0").")";
            }
            $slider += $RankAnzahl + $hop;

            $SeitenStatsName[$slider] = "<b>".$lng->txt("stats_page_stubbiest_total_dwell_time")."</b>"; //"<b>Kuerzeste GesamtVerweildauer:</b>";
            $SeitenStatsWert[$slider] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SeitenStatsName[$i +1 + $slider] = ($i +1).". ".str_replace($d1, $d2, substr($SeitenName[$SeitenGesamtVerweilMinID[$i]], 0, $TNA));
                $SeitenStatsWert[$i +1 + $slider] =
                round(is_numeric($SeitenGesamtVerweilMin[$i])?$SeitenGesamtVerweilMin[$i]:"0")."s (mZ:".(is_numeric($SeitenMessbareZugriffe[$SeitenGesamtVerweilMinID[$i]])?$SeitenMessbareZugriffe[$SeitenGesamtVerweilMinID[$i]]:"0") .")";
            }
            $slider += $RankAnzahl + $hop;

            $SeitenStatsName[$slider] = "<b>".$lng->txt("stats_page_longest_average_dwell_time")."</b>"; //"<b>Laengste durschn.Verweildauer:</b>";
            $SeitenStatsWert[$slider] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SeitenStatsName[$i +1 + $slider] = ($i +1).". ".str_replace($d1, $d2, substr($SeitenName[$SeitenDurchschnittVerweilMaxID[$i]], 0, $TNA));
                $SeitenStatsWert[$i +1 + $slider] =
                round(is_numeric($SeitenDurchschnittVerweilMax[$i])?$SeitenDurchschnittVerweilMax[$i]:0)."s (mZ:".(is_numeric($SeitenMessbareZugriffe[$SeitenDurchschnittVerweilMaxID[$i]])?$SeitenMessbareZugriffe[$SeitenDurchschnittVerweilMaxID[$i]]:"0").")";
            }
            $slider += $RankAnzahl + $hop;

            $SeitenStatsName[$slider] = "<b>".$lng->txt("stats_page_stubbiest_average_dwell_time")."</b>"; //"<b>Kuerzeste durschn.Verweildauer:</b>";
            $SeitenStatsWert[$slider] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SeitenStatsName[$i +1 + $slider] = ($i +1).". ".str_replace($d1, $d2, substr($SeitenName[$SeitenDurchschnittVerweilMinID[$i]], 0, $TNA));
                $SeitenStatsWert[$i +1 + $slider] =
                round($SeitenDurchschnittVerweilMin[$i])."s (mZ:".(is_numeric($SeitenMessbareZugriffe[$SeitenDurchschnittVerweilMinID[$i]])?$SeitenMessbareZugriffe[$SeitenDurchschnittVerweilMinID[$i]]:"0").")";
            }
            $slider += $RankAnzahl + $hop;

            $SeitenStatsName[$slider] = "<b>".$lng->txt("stats_page_most_access")."</b>"; //"<b>Meisten Zugriffe:</b>";
            $SeitenStatsWert[$slider] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SeitenStatsName[($i +1) + $slider] = ($i +1).". ".str_replace($d1, $d2, substr($SeitenName[$SeitenZugriffMaxID[$i]], 0, $TNA));
                $SeitenStatsWert[($i +1) + $slider] = round($SeitenZugriffMax[$i])."z ";
            }
            $slider += $RankAnzahl + $hop;

            $SeitenStatsName[$slider] = "<b>".$lng->txt("stats_page_fewest_access")."</b>"; //"<b>Wenigsten Zugriffe:</b>";
            $SeitenStatsWert[$slider] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SeitenStatsName[($i +1) + $slider] = ($i +1).". ".str_replace($d1, $d2, substr($SeitenName[$SeitenZugriffMinID[$i]], 0, $TNA));
                $SeitenStatsWert[($i +1) + $slider] = round($SeitenZugriffMin[$i])."z ";
            }
            $slider += $RankAnzahl + $hop;

            $SeitenStatsName[$slider] = "<b>".$lng->txt("stats_page_highest_deviation")."</b>"; //"<b>Hoechste Standartabweichung:</b>";
            $SeitenStatsWert[$slider] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SeitenStatsName[$i +1 + $slider] = ($i +1).". ".str_replace($d1, $d2, substr($SeitenName[$EinzelSeitenVarMaxID[$i]], 0, $TNA));
                $SeitenStatsWert[$i +1 + $slider] = round(sqrt($EinzelSeitenVarMax[$i]), 2)." (mZ:"
                .(is_numeric($SeitenMessbareZugriffe[$EinzelSeitenVarMaxID[$i]])?$SeitenMessbareZugriffe[$EinzelSeitenVarMaxID[$i]]:0).")";
            }
            $slider += $RankAnzahl + $hop;

            $SeitenStatsName[$slider] = "<b>".$lng->txt("stats_page_least_deviation")."</b>"; //"<b>Geringste Standartabweichung:</b>";
            $SeitenStatsWert[$slider] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SeitenStatsName[$i +1 + $slider] = ($i +1).". ".str_replace($d1, $d2, substr($SeitenName[$EinzelSeitenVarMinID[$i]], 0, $TNA));
                $SeitenStatsWert[$i +1 + $slider] = round(sqrt($EinzelSeitenVarMin[$i]), 2)." (mZ:".
                (is_numeric($SeitenMessbareZugriffe[$EinzelSeitenVarMinID[$i]])?$SeitenMessbareZugriffe[$EinzelSeitenVarMinID[$i]]:0).")";
            }
            $slider += $RankAnzahl + $hop;

            $SeitenStatsName[$slider] = "<b>".$lng->txt("stats_page_highest_varcoeff")."</b>"; //"<b>Hoechster Variationskoeffizient:</b>";
            $SeitenStatsWert[$slider] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SeitenStatsName[$i +1 + $slider] = ($i +1).". ".str_replace($d1, $d2, substr($SeitenName[$EinzelSeitenVarKoefMaxID[$i]], 0, $TNA));
                $SeitenStatsWert[$i +1 + $slider] = round(sqrt($EinzelSeitenVarKoefMax[$i]), 2)." (mZ:".
                (is_numeric($SeitenMessbareZugriffe[$EinzelSeitenVarKoefMaxID[$i]])?$SeitenMessbareZugriffe[$EinzelSeitenVarKoefMaxID[$i]]:0).")";
            }
            $slider += $RankAnzahl + $hop;

            $SeitenStatsName[$slider] = "<b>".$lng->txt("stats_page_lowest_varcoeff")."</b>"; //"<b>Geringster Variationskoeffizient:</b>";
            $SeitenStatsWert[$slider] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SeitenStatsName[$i +1 + $slider] = ($i +1).". ".str_replace($d1, $d2, substr($SeitenName[$EinzelSeitenVarKoefMinID[$i]], 0, $TNA));
                $SeitenStatsWert[$i +1 + $slider] = round(sqrt($EinzelSeitenVarKoefMin[$i]), 2)." (mZ:".
                (is_numeric($SeitenMessbareZugriffe[$EinzelSeitenVarKoefMinID[$i]])?$SeitenMessbareZugriffe[$EinzelSeitenVarKoefMinID[$i]]:0).")";
            }
            $slider += $RankAnzahl + $hop;

            $SeitenStatsName[$slider] = "<b>".$lng->txt("stats_interval_dwell_time")."</b>"; //"<b>VerweilDauer-Intervalle:</b>";
            $SeitenStatsWert[$slider] = "";
            $summe = 0;
            for ($i = 1; $i <= count($IntervallMax); $i ++) {
                $SeitenStatsName[$i + $slider] = $IntervallMax[$i -1].$lng->txt("stats_sec")." ".$lng->txt("stats_until")." ".$IntervallMax[$i]." ".$lng->txt("stats_sec").":"; //$IntervallMax[$i-1]."s bis ".$IntervallMax[$i]."s:";
                $SeitenStatsWert[$i + $slider] = round($IntervallAnz[$i])."z";
                $summe += $IntervallAnz[$i];
            }
            $slider += count($IntervallMax) + 1;

            $SeitenStatsName[$slider] = $lng->txt("stats_summation").": "; //"Summe:";
            $SeitenStatsWert[$slider] = $summe." ".$lng->txt("stats_accesses"); //" Zugriffe";

            $slider += $hop;

            $SeitenStatsName[$slider] = "<b>".$lng->txt("stats_interval_page_access")."</b>"; //"<b>Seitenzugriffe-Intervalle:</b>";
            $SeitenStatsWert[$slider] = "";
            $summe = 0;
            for ($i = 1; $i <= count($IntervallSeitenMax); $i ++) {
                $SeitenStatsName[$i + $slider] = ($IntervallSeitenMax[$i -1] + 1)." ".$lng->txt("stats_until")." ".$IntervallSeitenMax[$i]." ".$lng->txt("stats_accesses"); //..." bis "..." Zugriffe";
                $SeitenStatsWert[$i + $slider] = round($IntervallSeitenAnz[$i])." ".$lng->txt("stats_pages"); //" Seiten";
                $summe += $IntervallSeitenAnz[$i];
            }
            $slider += count($IntervallSeitenMax) + 2;

            $SeitenStatsName[$slider] = $lng->txt("stats_summation").": "; //"Summe:";
            $SeitenStatsWert[$slider] = $summe." ".$lng->txt("stats_pages"); //" Seiten";

            $slider += $hop;

            //SESSIONSTATS
            $slider2 ++;
            $SessionStatsName[$slider2] = $lng->txt("stats_session_number"); //"Anzahl Sessions";
            $SessionStatsWert[$slider2] = round($Sessionanz);
            $slider2 ++;
            $SessionStatsName[$slider2] = $lng->txt("stats_session_evaluable"); //"Auswertbare Sessions (Zeit)";
            $SessionStatsWert[$slider2] = round($MessbareSessions);
            $slider2 ++;
            $SessionStatsName[$slider2] = $lng->txt("stats_session_involved_usr"); //"beteiligte User";
            $SessionStatsWert[$slider2] = round($UserAnz);
            $slider2 ++;
            $SessionStatsName[$slider2] = $lng->txt("stats_session_evaluable_usr"); //"Auswertbare User";
            $SessionStatsWert[$slider2] = round($UserMessbarAnz);
            $slider2 ++;
            $SessionStatsName[$slider2] = $lng->txt("stats_session_evaluable_access"); //"Auswertbare Sessionzugriffe (Zeit)";
            $SessionStatsWert[$slider2] = round($MessbareSessionZugriffe);
            $slider2 ++;
            $SessionStatsName[$slider2] = $lng->txt("stats_session_average_page_access"); //"durchschn. Seitenzugriffe je Session";
            $SessionStatsWert[$slider2] = round($SessionDurschnittsZeit, 4);
            $slider2 ++;
            $SessionStatsName[$slider2] = $lng->txt("stats_session_total_duration"); //"SessionsGesamtdauer";
            $SessionStatsWert[$slider2] =  ilLMStatistics::s_to_h($SessionGesamtDauerAll)." (".$SessionGesamtDauerAll.$lng->txt("stats_sec").")";
            $slider2 ++;
            $SessionStatsName[$slider2] = $lng->txt("stats_session_average_length"); //"durchschn. Sessionlaenge";
            $SessionStatsWert[$slider2] =  ilLMStatistics::s_to_m($SessionDurchschnittsDauer);
            $slider2 ++;

            $slider2 += $hop;

            $SessionStatsName[$slider2] = "<b>".$lng->txt("stats_session_longest")."</b>"; //"<b>Laengste Session</b>";
            $SessionStatsWert[$slider2] = "";

            include_once("Services/Tracking/classes/class.ilObjUserTracking.php");

            $anonymous = !ilObjUserTracking::_enabledUserRelatedData();


            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SessionStatsName[$i +1 + $slider2] = ($i +1).". ".$lng->txt("stats_user")." ". ($anonymous?$i+1:ilObjUser::_lookupLogin($SessionUserID[$SessionGesamtDauerMaxID[$i]])); //.$SessionGesamtDauerMaxID[$i]." von User "
                $SessionStatsWert[$i +1 + $slider2] =  ilLMStatistics::s_to_h($SessionGesamtDauerMax[$i])." (".$SessionGesamtDauerMax[$i].$lng->txt("stats_sec").")"; //"s)";
            }
            $slider2 += $RankAnzahl + $hop;

            $SessionStatsName[$slider2] = "<b>".$lng->txt("stats_session_longest_total_duration_usr")."</b>"; //"<b>Laengste Gesamtdauer pro User</b>";
            $SessionStatsWert[$slider2] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SessionStatsName[$i +1 + $slider2] = ($i +1).". ".$lng->txt("stats_user")." ".($anonymous?$i+1:ilObjUser::_lookupLogin($UserGesamtSessionsDauerMaxID[$i]));
                $SessionStatsWert[$i +1 + $slider2] =  ilLMStatistics::s_to_h($UserGesamtSessionsDauerMax[$i])." (".$UserGesamtSessionsDauerMax[$i].$lng->txt("stats_sec").")"; //."s)";
            }
            $slider2 += $RankAnzahl + $hop;

            $SessionStatsName[$slider2] = "<b>".$lng->txt("stats_session_most").":"."</b>"; //"<b>Meiste Sessions:</b>";
            $SessionStatsWert[$slider2] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $SessionStatsName[$i +1 + $slider2] = ($i +1).". ".$lng->txt("stats_user")." ".($anonymous?$i+1:ilObjUser::_lookupLogin($UserSessionAnzMaxID[$i])); //".  User "
                $SessionStatsWert[$i +1 + $slider2] = round($UserSessionAnzMax[$i])." ".$lng->txt("stats_sessions"); //" Sessions";
            }
            $slider2 += $RankAnzahl + $hop;

            //KAPITELSTATS

            $hop = 0;

            $slider3 ++;
            $KapitelStatsName[$slider3] = $lng->txt("stats_chapter_number"); //"Anzahl Kapitel";
            $KapitelStatsWert[$slider3] = round($Kapitelanz);
            $slider3 ++;
            $KapitelStatsName[$slider3] = $lng->txt("stats_depth")." 0"; //"Tiefe 2";
            $KapitelStatsWert[$slider3] = round($KapitelTiefe2Anzahl);
            $slider3 ++;
            $KapitelStatsName[$slider3] = $lng->txt("stats_depth")." 1"; //"Tiefe 3";
            $KapitelStatsWert[$slider3] = round($KapitelTiefe3Anzahl);
            $slider3 ++;
            $KapitelStatsName[$slider3] = $lng->txt("stats_depth")." 2"; //"Tiefe 4";
            $KapitelStatsWert[$slider3] = round($KapitelTiefe4Anzahl);
            $slider3 ++;
            $KapitelStatsName[$slider3] = $lng->txt("stats_depth")." 3+"; //"Tiefe 5+";
            $KapitelStatsWert[$slider3] = round($KapitelTiefe5Anzahl);
            $slider3 ++;

            $slider3 += $hop;

            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_longest_total_dwell_time_depth")." 0:"."</b>"; //"<b>LaengsteGesamtVerweildauer Tiefe 2:</b>";
            $KapitelStatsWert[$slider3] = "";
            for ($i = 0; $i < min($KapitelTiefe2Anzahl,$RankAnzahl); $i ++) {
                $KapitelStatsName[$i + 1 + $slider3] = ($i +1).". ".str_replace($d1, $d2, substr($KapitelName[$KapitelGesamtVerweilMaxID2[$i]], 0, $TNA));
                $KapitelStatsWert[$i + 1 + $slider3] =  ilLMStatistics::s_to_h($KapitelGesamtVerweilMax2[$i]);

            }
            $slider3 += $i + $hop + 1;
            //$slider3 += $RankAnzahl + $hop;

            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_most_access_depth")." 0:"."</b>"; //"<b>Meisten Zugriffe Tiefe 2</b>";
            $KapitelStatsWert[$slider3] = "";
            for ($i = 0; $i <  min($KapitelTiefe2Anzahl, $RankAnzahl); $i ++) {
                $KapitelStatsName[$i +1 + $slider3] = ($i +1).". ".str_replace($d1, $d2, substr($KapitelName[$KapitelZugriffMaxID2[$i]], 0, $TNA));
                $KapitelStatsWert[$i +1 + $slider3] = round($KapitelZugriffMax2[$i])." ".$lng->txt("stats_accesses"); //." Zugriffe";
            }
            $slider3 += $i + $hop + 1;
            //$slider3 += $RankAnzahl + $hop;

            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_longest_total_dwell_time_depth")." 1:"."</b>"; //"<b>LaengsteGesamtVerweildauer Tiefe 3:</b>";
            $KapitelStatsWert[$slider3] = "";
            for ($i = 0; $i <  min($KapitelTiefe3Anzahl, $RankAnzahl); $i ++) {
                $KapitelStatsName[$i +1 + $slider3] = ($i +1).". ".str_replace($d1, $d2, substr($KapitelName[$KapitelGesamtVerweilMaxID3[$i]], 0, $TNA));
                $KapitelStatsWert[$i +1 + $slider3] =  ilLMStatistics::s_to_h($KapitelGesamtVerweilMax3[$i]);
            }
            //$slider3 += $RankAnzahl + $hop;
            $slider3 += $i + $hop + 1;

            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_most_access_depth")." 1:"."</b>"; //"<b>Meisten Zugriffe Tiefe 3</b>";
            $KapitelStatsWert[$slider3] = "";
            for ($i = 0; $i <   min($KapitelTiefe3Anzahl, $RankAnzahl); $i ++) {
                $KapitelStatsName[$i +1 + $slider3] = ($i +1).". ".str_replace($d1, $d2, substr($KapitelName[$KapitelZugriffMaxID3[$i]], 0, $TNA));
                $KapitelStatsWert[$i +1 + $slider3] = round($KapitelZugriffMax3[$i])." ".$lng->txt("stats_accesses"); //." Zugriffe";
            }
            $slider3 += $i + $hop + 1;
            //$slider3 += $RankAnzahl + $hop;

            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_longest_total_dwell_time_depth")." 2:"."</b>"; //"<b>LaengsteGesamtVerweildauer Tiefe 4:</b>";
            $KapitelStatsWert[$slider3] = "";
            for ($i = 0; $i <   min($KapitelTiefe4Anzahl, $RankAnzahl); $i ++) {
                $KapitelStatsName[$i +1 + $slider3] = ($i + 1).". ".str_replace($d1, $d2, substr($KapitelName[$KapitelGesamtVerweilMaxID4[$i]], 0, $TNA));
                $KapitelStatsWert[$i +1 + $slider3] =  ilLMStatistics::s_to_h($KapitelGesamtVerweilMax4[$i]);

            }
            $slider3 += $i + $hop + 1;
            //$slider3 += $RankAnzahl + $hop;

            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_most_access_depth")." 2:"."</b>"; //"<b>Meisten Zugriffe Tiefe 4</b>";
            $KapitelStatsWert[$slider3] = "";
            for ($i = 0; $i <    min($KapitelTiefe4Anzahl, $RankAnzahl); $i ++) {
                $KapitelStatsName[$i +1 + $slider3] = ($i +1).". ".str_replace($d1, $d2, substr($KapitelName[$KapitelZugriffMaxID4[$i]], 0, $TNA));
                $KapitelStatsWert[$i +1 + $slider3] = round($KapitelZugriffMax4[$i])." ".$lng->txt("stats_accesses"); //." Zugriffe";

            }
            $slider3 += $i + $hop + 1;
            //$slider3 += $RankAnzahl + $hop;

            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_longest_total_dwell_time_depth")." 3+:"."</b>"; //"<b>LaengsteGesamtVerweildauer Tiefe 5+:</b>";
            $KapitelStatsWert[$slider3] = "";
            for ($i = 0; $i <     min($KapitelTiefe5Anzahl, $RankAnzahl); $i ++) {
                $KapitelStatsName[$i +1 + $slider3] = ($i +1).". ".str_replace($d1, $d2, substr($KapitelName[$KapitelGesamtVerweilMaxID5[$i]], 0, $TNA));
                $KapitelStatsWert[$i +1 + $slider3] =  ilLMStatistics::s_to_h($KapitelGesamtVerweilMax5[$i]);
            }
            $slider3 += $i + $hop + 1;
            //$slider3 += $RankAnzahl + $hop;


            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_most_access_depth")." 3+:"."</b>"; //"<b>Meisten Zugriffe Tiefe 5+</b>";
            $KapitelStatsWert[$slider3] = "";
            for ($i = 0; $i < min($KapitelTiefe5Anzahl, $RankAnzahl); $i ++) {
                $KapitelStatsName[$i +1 + $slider3] = ($i +1).". ".str_replace($d1, $d2, substr($KapitelName[$KapitelZugriffMaxID5[$i]], 0, $TNA));
                $KapitelStatsWert[$i +1 + $slider3] = round($KapitelZugriffMax5[$i])." ".$lng->txt("stats_accesses"); //." Zugriffe";
            }
            //$slider3 += $RankAnzahl + $hop;
            $slider3 += $i + $hop + 1;

            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_stubbiest_total_dwell_time").":"."</b>"; //"<b>kuerzeste GesamtVerweildauer:</b>";
            $KapitelStatsWert[$slider3] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $KapitelStatsName[$i +1 + $slider3] = ($i +1).". ".str_replace($d1, $d2, substr($KapitelName[$KapitelGesamtVerweilMinID[$i]], 0, $TNA));
                $KapitelStatsWert[$i +1 + $slider3] =  ilLMStatistics::s_to_h($KapitelGesamtVerweilMin[$i]);
            }
            $slider3 += $RankAnzahl + $hop;

            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_fewest_access").":"."</b>"; //"<b>wenigste Zugriffe:</b>";
            $KapitelStatsWert[$slider3] = "";
            for ($i = 0; $i < $RankAnzahl; $i ++) {
                $KapitelStatsName[$i +1 + $slider3] = ($i +1).". ".str_replace($d1, $d2, substr($KapitelName[$KapitelZugriffMinID[$i]], 0, $TNA));
                $KapitelStatsWert[$i +1 + $slider3] = round($KapitelZugriffMin[$i])." ".$lng->txt("stats_accesses"); //." Zugriffe";
            }
            $slider3 += $RankAnzahl + $hop;

            $slider3save = $slider3;

            //Abdeckungsgrade Tiefe2
            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_depth_coverage")." 0:"."</b>"; //"<b>Abdeckungsgrade Tiefe 2:</b>";
            $KapitelStatsWertA1[$slider3] = $lng->txt("stats_accesses").">0";
            $KapitelStatsWertA2[$slider3] = ">".floor($KapitelDurchZugriffe2 * $KapitelAbdeckungsKennzahlFaktor2);
            $KapitelStatsWertA3[$slider3] = ">".floor($KapitelDurchZugriffe2 * $KapitelAbdeckungsKennzahlFaktor3);
            $KapitelStatsWertA4[$slider3] = ">".floor($KapitelDurchZugriffe2 * $KapitelAbdeckungsKennzahlFaktor4);
            $KapitelStatsWertA5[$slider3] = ">".floor($KapitelDurchZugriffe2 * $KapitelAbdeckungsKennzahlFaktor5);

            $u = 0;
            for ($i = 1; $i <= $Kapitelanz; $i ++) {
                if ($seitenTiefe[$KapitelID[$i]] == 2) {
                    if ($KapitelSeitenSumme[$i] > 0) {
                        $u ++;
                        $KapitelStatsName[$u +1 + $slider3] = $u.". ".str_replace($d1, $d2, substr($KapitelName[$i], 0, $TNA))." (".$KapitelSeitenSumme[$i]." ".$lng->txt("stats_pages").")";
                        $KapitelStatsWertA1[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten1[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten1[$i]).")";
                        $KapitelStatsWertA2[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten2[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten2[$i]).")";
                        $KapitelStatsWertA3[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten3[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten3[$i]).")";
                        $KapitelStatsWertA4[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten4[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten4[$i]).")";
                        $KapitelStatsWertA5[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten5[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten5[$i]).")";
                    }
                }
            }
            $slider3 += $u + $hop +2;

            //Abdeckungsgrade Tiefe3
            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_depth_coverage")." 1:"."</b>"; //"<b>Abdeckungsgrade Tiefe 3:</b>";
            $KapitelStatsWertA1[$slider3] = $lng->txt("stats_accesses").">0";
            $KapitelStatsWertA2[$slider3] = ">".floor($KapitelDurchZugriffe3 * $KapitelAbdeckungsKennzahlFaktor2);
            $KapitelStatsWertA3[$slider3] = ">".floor($KapitelDurchZugriffe3 * $KapitelAbdeckungsKennzahlFaktor3);
            $KapitelStatsWertA4[$slider3] = ">".floor($KapitelDurchZugriffe3 * $KapitelAbdeckungsKennzahlFaktor4);
            $KapitelStatsWertA5[$slider3] = ">".floor($KapitelDurchZugriffe3 * $KapitelAbdeckungsKennzahlFaktor5);
            $u = 0;
            for ($i = 1; $i <= $Kapitelanz; $i ++) {
                if ($seitenTiefe[$KapitelID[$i]] == 3) {
                    if ($KapitelSeitenSumme[$i] > 0) {
                        $u ++;
                        $KapitelStatsName[$u +1 + $slider3] = str_replace($d1, $d2, substr($KapitelName[$i], 0, $TNA))." (".$KapitelSeitenSumme[$i]." ".$lng->txt("stats_pages").")";
                        $KapitelStatsWertA1[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten1[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten1[$i]).")";
                        $KapitelStatsWertA2[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten2[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten2[$i]).")";
                        $KapitelStatsWertA3[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten3[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten3[$i]).")";
                        $KapitelStatsWertA4[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten4[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten4[$i]).")";
                        $KapitelStatsWertA5[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten5[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten5[$i]).")";
                    }
                }
            }
            $slider3 += $u + $hop +2;

            //Abdeckungsgrade Tiefe4
            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_depth_coverage")." 2:"."</b>"; //"<b>Abdeckungsgrade Tiefe 4:</b>";
            $KapitelStatsWertA1[$slider3] = $lng->txt("stats_accesses").">0";
            $KapitelStatsWertA2[$slider3] = ">".floor($KapitelDurchZugriffe4 * $KapitelAbdeckungsKennzahlFaktor2);
            $KapitelStatsWertA3[$slider3] = ">".floor($KapitelDurchZugriffe4 * $KapitelAbdeckungsKennzahlFaktor3);
            $KapitelStatsWertA4[$slider3] = ">".floor($KapitelDurchZugriffe4 * $KapitelAbdeckungsKennzahlFaktor4);
            $KapitelStatsWertA5[$slider3] = ">".floor($KapitelDurchZugriffe4 * $KapitelAbdeckungsKennzahlFaktor5);
            $u = 0;
            for ($i = 1; $i <= $Kapitelanz; $i ++) {
                if ($seitenTiefe[$KapitelID[$i]] == 4) {
                    if ($KapitelSeitenSumme[$i] > 0) {
                        $u ++;
                        $KapitelStatsName[$u +1 + $slider3] = str_replace($d1, $d2, substr($KapitelName[$i], 0, $TNA))." (".$KapitelSeitenSumme[$i]." ".$lng->txt("stats_pages").")";
                        $KapitelStatsWertA1[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten1[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten1[$i]).")";
                        $KapitelStatsWertA2[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten2[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten2[$i]).")";
                        $KapitelStatsWertA3[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten3[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten3[$i]).")";
                        $KapitelStatsWertA4[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten4[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten4[$i]).")";
                        $KapitelStatsWertA5[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten5[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten5[$i]).")";
                    }
                }
            }
            $slider3 += $u + $hop +2;

            //Abdeckungsgrade Tiefe5
            $KapitelStatsName[$slider3] = "<b>".$lng->txt("stats_chapter_depth_coverage")." 3+:"."</b>"; //"<b>Abdeckungsgrade Tiefe 5+:</b>";
            $KapitelStatsWertA1[$slider3] = $lng->txt("stats_accesses").">0";
            $KapitelStatsWertA2[$slider3] = ">".floor($KapitelDurchZugriffe5 * $KapitelAbdeckungsKennzahlFaktor2);
            $KapitelStatsWertA3[$slider3] = ">".floor($KapitelDurchZugriffe5 * $KapitelAbdeckungsKennzahlFaktor3);
            $KapitelStatsWertA4[$slider3] = ">".floor($KapitelDurchZugriffe5 * $KapitelAbdeckungsKennzahlFaktor4);
            $KapitelStatsWertA5[$slider3] = ">".floor($KapitelDurchZugriffe5 * $KapitelAbdeckungsKennzahlFaktor5);
            $u = 0;
            for ($i = 1; $i <= $Kapitelanz; $i ++) {
                if ($seitenTiefe[$KapitelID[$i]] == 5) {
                    if ($KapitelSeitenSumme[$i] > 0) {
                        $u ++;
                        $KapitelStatsName[$u +1 + $slider3] = str_replace($d1, $d2, substr($KapitelName[$i], 0, $TNA))." (".$KapitelSeitenSumme[$i]." ".$lng->txt("stats_pages").")"; //." Seiten)";//$KapitelZugriffe[$i];
                        $KapitelStatsWertA1[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten1[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten1[$i]).")";
                        $KapitelStatsWertA2[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten2[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten2[$i]).")";
                        $KapitelStatsWertA3[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten3[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten3[$i]).")";
                        $KapitelStatsWertA4[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten4[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten4[$i]).")";
                        $KapitelStatsWertA5[$u +1 + $slider3] =  ilLMStatistics::proz(floor($KapitelAbgedeckteSeiten5[$i] * 100 / $KapitelSeitenSumme[$i]))."%(". ilLMStatistics::proz($KapitelAbgedeckteSeiten5[$i]).")";
                    }
                }
            }
            $slider3 += $u + $hop +2;

            //AUSGABE BEOBACHTUNGSMODELL

            if ($_POST["stat"] == 'u') {
                //Daten f�r das Applet zusammenstellen

                $delim1 = " ";

                for ($i = 1; $i <= $Seitenanz; $i ++) {
                    $KapitelZuSeite2 = $KapitelZuSeite2. (-1 + $rKapitelID[$vatizumkind[$SeitenID[$i]]]).$delim1;
                }
                for ($i = 1; $i <= $Seitenanz; $i ++) {
                    for ($j = 1; $j <= $Seitenanz; $j ++) {
                        $SessionVonNach2 = $SessionVonNach2. (0 + $SessionVonNach[$i][$j]).$delim1;
                    }
                }
                for ($i = 1; $i <= $Kapitelanz; $i ++) {
                    for ($j = 1; $j <= $Kapitelanz; $j ++) {
                        $KapitelVonNach2 = $KapitelVonNach2. (0 + $KapitelVonNach[$i][$j]).$delim1;
                    }
                }
                for ($i = 1; $i <= $Kapitelanz; $i ++) {
                    $OberkapitelZuKapitel2 = $OberkapitelZuKapitel2. (-1 + $rKapitelID[$vatizumkind[$KapitelID[$i]]]).$delim1;
                }

                $delim2 = "  sName ";

                for ($i = 1; $i <= $Seitenanz; $i ++) {
                    $Seitenname2 = $Seitenname2.$SeitenName[$i].$delim2;
                }

                $delim3 = " kName ";

                for ($i = 1; $i <= $Kapitelanz; $i ++) {
                    $Kapitelname2 = $Kapitelname2.$KapitelName[$i].$delim3;
                }
                //Ende der Variablen
            }
            //ENDE von AUSGABE BEOBACHTUNGSMODELL

            else {
                //SCHREIBE die SEITEN,SESSION oder KAPITELSTATS in Tabelle

                include_once "./Services/Table/classes/class.ilTableGUI.php";
                //				$tbl = new ilTableGUI();
                $tpl->addBlockfile("ADM_CONTENT", "adm_content", MODULE_PATH."/templates/default/tpl.lm_statistics_result.html");
                $tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
                $tpl->addBlockfile("TRACK_TABLE", "track_table", "tpl.table.html");
                $tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

                if ($_POST["stat"] == 'd') {
                    $title_new = array ("time", "", "", "", "", "");
                } else {
                    $title_new = array ("time", "count");
                }

                $tbl = new ilTableGUI();
                $tbl->setTitle($lng->txt("obj_trac"), 0);
                foreach ($title_new as $val) {
                    $header_names[] = $lng->txt($val);
                }
                $tbl->disable("sort");
                $tbl->setHeaderNames($header_names);
                if ($_POST["stat"] == 'h') { //SeitenSTATS
                    $num = $slider +1;
                    //$tbl->setMaxCount($num);
                } else
                if ($_POST["stat"] == 'd') { //KapitelSTATS
                    $num = $slider3save + $slider3 + 1;
                    //$tbl->setMaxCount($num);
                } else
                if ($_POST["stat"] == 'o') { //SessionSTATS
                    $num = $slider2 +1;
                    //$tbl->setMaxCount($num);
                } else { //BeobModell
                    $num = 24;
                    //$tbl->setMaxCount($num);
                }
                $tbl->setStyle("table", "std");
                $tbl->render();

                if ($_POST["stat"] == 'h') { //SeitenSTATS
                    for ($i = 0; $i < $num; $i ++) { //Soviele Zeilen ausgeben
                        $data[0] = $SeitenStatsName[$i]; //String in 1. Spalte
                        $data[1] = $SeitenStatsWert[$i]; // Werte der 2. Spalte
                        $css_row = $i % 2 == 0 ? "tblrow1" : "tblrow2"; //Tabelle erstellen
                        foreach ($data as $key => $val) { //Werte eintragen
                            $tpl->setCurrentBlock("text");
                            $tpl->setVariable("TEXT_CONTENT", $val); //Werte der Zelle setzen
                            $tpl->parseCurrentBlock();
                            $tpl->setCurrentBlock("table_cell"); //<td class="std" valign="top"> </td>
                            $tpl->parseCurrentBlock();
                        }
                        $tpl->setCurrentBlock("tbl_content"); //<tr class="{CSS_ROW}"></tr>
                        $tpl->setVariable("CSS_ROW", $css_row);
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->parseCurrentBlock();
                } else
                if ($_POST["stat"] == 'd') { //KapitelSTATS
                    for ($i = 1; $i < $slider3save; $i ++) { //Soviele Zeilen ausgeben
                        $data[0] = $KapitelStatsName[$i]; //String in 1. Spalte
                        $data[1] = $KapitelStatsWert[$i]; // Werte der 2. Spalte
                        $data[2] = "";
                        $data[3] = "";
                        $data[4] = "";
                        $data[5] = "";
                        $css_row = $i % 2 == 0 ? "tblrow1" : "tblrow2"; //Tabelle erstellen
                        foreach ($data as $key => $val) { //Werte eintragen
                            $tpl->setCurrentBlock("text");
                            $tpl->setVariable("TEXT_CONTENT", $val);
                            $tpl->parseCurrentBlock();
                            $tpl->setCurrentBlock("table_cell");
                            $tpl->parseCurrentBlock();
                        }
                        $tpl->setCurrentBlock("tbl_content");
                        $tpl->setVariable("CSS_ROW", $css_row);
                        $tpl->parseCurrentBlock();
                    }
                    //Abdeckungsgrade ausgeben
                    for ($i = $slider3save; $i < $slider3; $i ++) { //Soviele Zeilen ausgeben
                        $data[0] = $KapitelStatsName[$i]; //String in 1. Spalte
                        $data[1] = $KapitelStatsWertA1[$i]; // Werte der 2. Spalte
                        $data[2] = $KapitelStatsWertA2[$i];
                        $data[3] = $KapitelStatsWertA3[$i];
                        $data[4] = $KapitelStatsWertA4[$i];
                        $data[5] = $KapitelStatsWertA5[$i];
                        $css_row = $i % 2 == 0 ? "tblrow1" : "tblrow2"; //Tabelle erstellen
                        foreach ($data as $key => $val) { //Werte eintragen
                            $tpl->setCurrentBlock("text");
                            $tpl->setVariable("TEXT_CONTENT", $val);
                            $tpl->parseCurrentBlock();
                            $tpl->setCurrentBlock("table_cell");
                            $tpl->parseCurrentBlock();
                        }
                        $tpl->setCurrentBlock("tbl_content");
                        $tpl->setVariable("CSS_ROW", $css_row);
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->parseCurrentBlock();
                } else
                if ($_POST["stat"] == 'o') { //SessionSTATS
                    for ($i = 0; $i < $num; $i ++) { //Soviele Zeilen ausgeben
                        $data[0] = $SessionStatsName[$i]; //String in 1. Spalte
                        $data[1] = $SessionStatsWert[$i]; // Werte der 2. Spalte
                        $css_row = $i % 2 == 0 ? "tblrow1" : "tblrow2"; //Tabelle erstellen
                        foreach ($data as $key => $val) { //Werte eintragen
                            $tpl->setCurrentBlock("text");
                            $tpl->setVariable("TEXT_CONTENT", $val);
                            $tpl->parseCurrentBlock();
                            $tpl->setCurrentBlock("table_cell");
                            $tpl->parseCurrentBlock();
                        }
                        $tpl->setCurrentBlock("tbl_content");
                        $tpl->setVariable("CSS_ROW", $css_row);
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock("adm_content");
                $tpl->setVariable("TXT_TIME_PERIOD", $lng->txt("time_segment"));
                switch ($_POST["stat"]) {
                    case "h" :
                        $tpl->setVariable("TXT_STATISTIC", $lng->txt("stats_pages_statisics"));
                        $tpl->setVariable("TXT_TRACKED_HELP", $lng->txt("help"));
                        $tpl->setVariable("VAL_TRACKED_HELP_LINK", "<a href='".MODULE_URL."/docs/pagestats_help.html' target='_blank'>"."Link"."</a>");
                        break;
                    case "u" :
                        $tpl->setVariable("TXT_STATISTIC", $lng->txt("stats_navigation"));
                        break;
                    case "d" :
                        $tpl->setVariable("TXT_STATISTIC", $lng->txt("stats_chapter_statisics"));
                        $tpl->setVariable("TXT_TRACKED_HELP", $lng->txt("help"));
                        $tpl->setVariable("VAL_TRACKED_HELP_LINK", "<a href='".MODULE_URL."/docs/chapterstats_help.html' target='_blank'>"."Link"."</a>");
                        break;
                    case "o" :
                        $tpl->setVariable("TXT_STATISTIC", $lng->txt("stats_sessions_statisics"));
                        $tpl->setVariable("TXT_TRACKED_HELP", $lng->txt("help"));
                        $tpl->setVariable("VAL_TRACKED_HELP_LINK", "<a href='".MODULE_URL."/docs/sessionstats_help.html' target='_blank'>"."Link"."</a>");
                        break;
                }
                $tpl->setVariable("VAL_DATEF", date("Y-m-d", mktime(0, 0, 0, $monthf, $dayf, $yearf)));
                $tpl->setVariable("TXT_TO", $lng->txt("to"));
                $tpl->setVariable("VAL_DATET", date("Y-m-d", mktime(0, 0, 0, $montht, $dayt, $yeart)));
                $tpl->setVariable("TXT_USER_LANGUAGE", $lng->txt("user_language"));
                if ($_POST["language"] == "0") {
                    $tpl->setVariable("VAL_LANGUAGE", $lng->txt("any_language"));
                } else {
                    $tpl->setVariable("VAL_LANGUAGE", $lng->txt("lang_".$_POST["language"]));
                }
                $rLehrmodulID = $_SESSION["il_track_rlm_id"];
                $LehrmodulName = $_SESSION["il_track_lm_name"];
                $tpl->setVariable("TXT_TRACKED_OBJECTS", $lng->txt("tracked_objects"));
                $tpl->setVariable("VAL_TRACKED_OBJECTS", $LehrmodulName[$rLehrmodulID[$_POST["lmID"]]]);
                $tpl->setVariable("LEGENDE", $lng->txt("stats_legend"));
                $tpl->setVariable("VAL_LEGENDE_MZ", $lng->txt("stats_measurable_hits"));
                $tpl->setVariable("VAL_LEGENDE_Z", $lng->txt("stats_hits"));
                $tpl->setVariable("LEGENDE_MZ", "mz");
                $tpl->setVariable("LEGENDE_Z", "z");
                $tpl->parseCurrentBlock();
            }
            //ENDE von AUSGABE

            $this->Seitenanz = $Seitenanz;
            $this->KapitelZuSeite2 = $KapitelZuSeite2;
            $this->SessionVonNach2 = $SessionVonNach2;
            $this->Kapitelanz = $Kapitelanz;
            $this->KapitelVonNach2 = $KapitelVonNach2;
            $this->OberkapitelZuKapitel2 = $OberkapitelZuKapitel2;
            $this->Seitenname2 = $Seitenname2;
            $this->Kapitelname2 = $Kapitelname2;

        } //Ende if

    } //Ende OUTPUTfunktion

    function outputApplet() {
        global $tpl, $lng, $ilias, $db;
        $this->calcStats (0);

        $tpl->setVariable("Sprache", "<param name='Sprache' value= '".$ilias->account->prefs["language"]."'>");
        $tpl->setVariable("Seitenanz2", "<param name='Seitenanz2' value= '".$this->Seitenanz."'>");
        $tpl->setVariable("KapitelZuSeite2", "<param name='KapitelZuSeite2' value= '".$this->KapitelZuSeite2."'>");
        $tpl->setVariable("SessionVonNach2", "<param name='SessionVonNach2' value= '".$this->SessionVonNach2."'>");
        $tpl->setVariable("Kapitelanz2", "<param name='Kapitelanz2' value= '".$this->Kapitelanz."'>");
        $tpl->setVariable("KapitelVonNach2", "<param name='KapitelVonNach2' value= '".$this->KapitelVonNach2."'>");
        $tpl->setVariable("OberkapitelZuKapitel2", "<param name='OberkapitelZuKapitel2' value= '".$this->OberkapitelZuKapitel2."'>");
        $tpl->setVariable("Seitenname2", "<param name='Seitenname2' value= '".$this->Seitenname2."'>");
        $tpl->setVariable("Kapitelname2", "<param name='Kapitelname2' value= '".$this->Kapitelname2."'>");

        include_once "./Services/Table/classes/class.ilTableGUI.php";
        $tpl->addBlockFile("ADM_CONTENT", "adm_content", MODULE_PATH."/templates/default/tpl.lm_statistics_result_applet.html");
        $tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
        $tpl->setVariable("TXT_TRACKED_OBJECTS2", "Beobachtungsmodell");
        $tpl->setVariable("CODEBASE", MODULE_URL."/lib");
        $tpl->setCurrentBlock("adm_content");
        $tpl->parseCurrentBlock();
    }

    //#####BERECHNUNGEN#####

    //FUNKTIONEN

    /**
  *	calculate variance for values and average
  *
  *
  */
    function varianzSV($arr, $mittelwert) {

        if (!count($arr))
        return 0;

        $summe = 0;

        for ($i = 0; $i < count($arr); $i ++) {
            $summe += ($arr[$i] - $mittelwert) * ($arr[$i] - $mittelwert);
        }
        return $summe / (count($arr));
    }

    function s_to_h($sek) {
        $stund = floor($sek / 3600);
        $min = floor(($sek - $stund * 3600) / 60);
        $restsek = ($sek - $stund * 3600 - $min * 60);
        return $stund."h,".$min."m,".$restsek."s";
    }

    //Funktion zum umrechnen von Sekunden in Minuten

    function s_to_m($sek) {
        $min = floor($sek / 60);
        $restsek = ($sek - $min * 60);
        return $min."m,".round($restsek)."s";
    }

    function  proz($str) {
        return $str;
        /*
        * if(strlen($str)==0){
        *	$str2="&#160;"."&#160;"."&#160;".$str;
        *	return  $str2;
        * }
        * elseif(strlen($str)==1){
        *	$str2="&#160;"."&#160;".$str;
        *	return  $str2;
        * }
        * elseif(strlen($str)==2){
        *	$str2="&#160;".$str;
        *	return  $str2;
        * }
        * elseif(strlen($str)==3){
        *	$str2=$str;
        *	return $str2;
        * }
        */
    }

}
define(MODULE_PATH, ILIAS_ABSOLUTE_PATH."/Services/Tracking");
define(MODULE_URL, ILIAS_HTTP_PATH."/Services/Tracking");
?>