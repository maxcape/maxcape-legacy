<?php
    error_reporting(E_ALL);
    require_once("../rsfunctions.php");
    require_once("dbfunctions.php");
    $rsf = new rsfunctions;
    $dbf = new dbfunctions;

    $level99 = 13034431;
    $level120 = 104273166;
    $maxcapexp = $level99 * 26;
    $compcapexp = ($level99 * 25) + $level120;

    $ranks = array();
    $levels = array();
    $experience = array();
    $experienceRemaining = array();

    $dbf->connectToDatabase($dbf->database);

    $rsn = mysql_real_escape_string($_POST['rsn']);

    $skills = $dbf->getAllAssocResults("SELECT * FROM skills ORDER BY Number ASC");

    $recentStats = $dbf->queryToText("SELECT Response FROM apicache WHERE RSN='$rsn' AND Active='1'");
    if ($recentStats != "") {
        $recentStats = preg_split("/\s+/", $recentStats);

        for ($i = 0; $i < count($skills); $i++) {
            $skillNumber = $skills[$i]['Number'];
            $thisSkill = explode(",", $recentStats[$skillNumber]);

            if ($thisSkill[0] == -1) {
                $thisSkill[0] = 0;
            }
            if ($thisSkill[1] == -1) {
                $thisSkill[1] = 0;
            }
            if ($thisSkill[2] == -1) {
                $thisSkill[2] = 0;
            }

            $ranks[$skillNumber] = $thisSkill[0];
            $levels[$skillNumber] = $thisSkill[1];
            $experience[$skillNumber] = $thisSkill[2];
        }

        $xpToCompCape = 0;
        $xpToMaxCape = 0;

        if ($levels[0] < 2673) {
            $milestone = (floor(min($levels) / 10) * 10);
            $msno = $milestone / 10;
        } else {
            $notmaxed = false;
            if ($levels[0] >= 2673 && $levels[0] < 2715) {
                for ($i = 1; $i < count($levels); $i++) {
                    if ($levels[$i] < 99) {
                        $notmaxed = true;
                    }
                }

                if ($notmaxed) {
                    $milestone = (floor(min($levels) / 10) * 10);
                    $msno = $milestone / 10;
                } else {
                    $milestone = "Max";
                    $msno = 10;
                }

            } else {
                $milestone = "Completionist";
                $msno = 11;
            }
        }

        for($i = 1; $i < count($skills); $i++) {
            if($i != 25 && $i != 27) { //Not Dungeoneering or Invention
                if($experience[$i] < $level99) {
                    $experienceRemaining[$i] = $level99 - $experience[$i];
                } else {
                    $experienceRemaining[$i] = 0;
                }
            } elseif($i == 25) { //Dungeoneering
                if($experience[$i] < $level99) {
                    $experienceRemaining[$i] = $level99 - $experience[$i];
                } elseif($experience[$i] < $level120) {
                    $experienceRemaining[$i] = $level120 - $experience[$i];
                } else {
                    $experienceRemaining[$i] = 0;
                }
            } elseif($i == 27) {
                $invent99 = $rsf->getExperience(99, "invention");
                $invent120 = $rsf->getExperience(120, "invention");
                if($experience[$i] < $invent99) {
                    $experienceRemaining[$i] = $invent99 - $experience[$i];
                } elseif($experience[$i] < $invent120) {
                    $experienceRemaining[$i] = $invent120 - $experience[$i];
                } else {
                    $experienceRemaining[$i] = 0;
                }
            }
        }

        for($i = 1; $i < count($skills); $i++) {
            if($i != 25 && $i != 27) { //Not dungeoneerng or invention
                $xpToMaxCape += $experienceRemaining[$i];
                $xpToCompCape += $experienceRemaining[$i];
            } elseif($i == 25) {
                if($experience[$i] < $level99) {
                    $xpToMaxCape += $experienceRemaining[$i];
                    $xpToCompCape += $level120 - $experience[$i];
                } elseif($experience[$i] < $level120) {
                    $xpToMaxCape += 0;
                    $xpToCompCape += $experienceRemaining[$i];
                } else {
                    $xpToMaxCape += 0;
                    $xpToCompCape += 0;
                }
            } else {
                $invent99 = $rsf->getExperience(99, "invention");
                $invent120 = $rsf->getExperience(120, "invention");

                if($experience[$i] < $invent99) {
                    $xpToMaxCape += $experienceRemaining[$i];
                    $xpToCompCape += $invent120 - $experience[$i];
                } elseif($experience[$i] < $invent120) {
                    $xpToMaxCape += 0;
                    $xpToCompCape += $experienceRemaining[$i];
                } else {
                    $xpToMaxCape += 0;
                    $xpToCompCape += 0;
                }
            }
        }

        if ($milestone != "Max" && $milestone != "Completionist") {
            echo $xpToMaxCape > 0 ? number_format((($maxcapexp - $xpToMaxCape) / $maxcapexp) * 100, 2) : "100.00";
        } else {
            echo $xpToCompCape > 0 ? number_format((($compcapexp - $xpToCompCape) / $compcapexp) * 100, 2) : "100.00";
        }
    }