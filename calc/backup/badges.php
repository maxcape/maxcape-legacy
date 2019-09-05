<?php
    require_once("../dbfunctions.php");


    function withinPercentage($first, $second, $percent)
    {
        $decimalPercent = ($percent / 100.0);
        $highRange = ($second * (1.0 + $decimalPercent));
        $lowRange = ($second * (1.0 - $decimalPercent));

        return $lowRange <= $first && $first <= $highRange;
    }

    function printBadges($skills, $experience, $levels)
    {
        $dbf = new dbfunctions;
        $total99s = 0;
        for ($i = 1; $i < count($levels); $i++) {
            if ($levels[$i] >= 99) {
                $total99s += 1;
            }
        }
        $stx = array_combine($skills, $experience);
        $stl = array_combine($skills, $levels);

        //Survivalist
        $survLow = min(array($stl['Agility'], $stl['Hunter'], $stl['Thieving'], $stl['Slayer']));
        if ($survLow == 99) {
            ?>
            <div class="badge survivalist" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Survivalist</h2><p>Level 99 in the following skills:<br>Agility, Hunter, Thieving, and Slayer.</p>');"></div>
        <?php
        } else {
            ?>
            <div class="badge survivalist unobtained" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Survivalist</h2><p>Level 99 in the following skills:<br>Agility, Hunter, Thieving, and Slayer.</p>');"></div>
        <?php
        }

        //Naturalist
        $natLow = min(array($stl['Cooking'], $stl['Farming'], $stl['Herblore'], $stl['Runecraft']));
        if ($natLow == 99) {
            ?>
            <div class="badge naturalist" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Naturalist</h2><p>Level 99 in the following skills:<br>Cooking, Farming, Herblore, and Runecrafting.</p>');"></div>
        <?php
        } else {
            ?>
            <div class="badge naturalist unobtained" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Naturalist</h2><p>Level 99 in the following skills:<br>Cooking, Farming, Herblore, and Runecrafting.</p>');"></div>
            <?php
        }

        //Artisan
        $artLow = min(array($stl['Smithing'], $stl['Crafting'], $stl['Fletching'], $stl['Construction'], $stl['Firemaking']));
        if ($artLow == 99) {
            ?>
            <div class="badge artisan" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Artisan</h2><p>Level 99 in the following skills:<br>Smithing, Crafting, Fletching, Construction, and Firemaking.</p>');"></div>
        <?php
        } else {
            ?>
            <div class="badge artisan unobtained" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Artisan</h2><p>Level 99 in the following skills:<br>Smithing, Crafting, Fletching, Construction, and Firemaking.</p>');"></div>
        <?php
        }

        //Gatherer
        $gatLow = min(array($stl['Woodcutting'], $stl['Mining'], $stl['Fishing'], $stl['Divination']));
        if ($gatLow == 99) {
            ?>
            <div class="badge gatherer" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Gatherer</h2><p>Level 99 in the following skills:<br>Woodcutting, Mining, Fishing, and Divination.</p>');"></div>
        <?php
        } else {
            ?>
            <div class="badge gatherer unobtained" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Gatherer</h2><p>Level 99 in the following skills:<br>Woodcutting, Mining, and Fishing, and Divination.</p>');"></div>
        <?php
        }

        //Count skills over X experience
        $double = 0;
        $fifty = 0;
        $onehundred = 0;
        $onehundredfifty = 0;
        $twohundred = 0;
        $onetwentycapes = 0;
        for ($i = 1; $i < count($experience); $i++) {
            if ($experience[$i] >= 25000000) {
                $double += 1;
            }

            if ($experience[$i] >= 50000000) {
                $fifty += 1;
            }

            if ($experience[$i] >= 100000000) {
                $onehundred += 1;
            }

            if ($experience[$i] >= 150000000) {
                $onehundredfifty += 1;
            }

            if ($experience[$i] >= 200000000) {
                $twohundred += 1;
            }

            if($experience[$i] >= 104273166 && $i != 25) {
                $onetwentycapes += 1;
            }
        }

        // Masterful, Pro, Semi-Pro, Experienced, Doubly Skilled
        if ($twohundred >= 1) {
            ?>
            <div class="badge twohundred" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Masterful</h2><p>200,000,000 experience in a skill.</p>');"></div>
        <?php
        } else if ($onehundredfifty >= 1) {
            ?>
            <div class="badge onehundredfifty" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Pro</h2><p>At least 150,000,000 experience in a skill.</p>');"></div>
        <?php
        } else if ($onehundred >= 1) {
            ?>
            <div class="badge onehundred" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Expert</h2><p>At least 100,000,000 experience in a skill.</p>');"></div>
        <?php
        } else if ($fifty >= 1) {
            ?>
            <div class="badge fifty" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Skilled</h2><p>At least 50,000,000 experience in a skill.</p>');"></div>
        <?php
        } else if ($double >= 1) {
            ?>
            <div class="badge twentyfive" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Experienced</h2><p>At least 25,000,000 experience in a skill.</p>');"></div>
        <?php
        } else {
            ?>
            <div class="badge twentyfive unobtained" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Experienced</h2><p>At least 25,000,000 experience in a skill.</p>');"></div>
        <?php
        }


        //Classic Completionist
        $classicLow = min(array($stl['Attack'], $stl['Defence'], $stl['Strength'], $stl['Constitution'], $stl['Ranged'], $stl['Prayer'], $stl['Magic'], $stl['Cooking'], $stl['Woodcutting'], $stl['Fletching'], $stl['Fishing'], $stl['Firemaking'], $stl['Crafting'], $stl['Smithing'], $stl['Mining'], $stl['Herblore'], $stl['Agility'], $stl['Thieving']));
        if ($classicLow == 99) {
            ?>
            <div class="badge classic" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Classic Completionist</h2><p>Level 99 in all skills available in RuneScape Classic.</p>');"></div>
        <?php
        } else {
            ?>
            <div class="badge classic unobtained" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Classic Completionist</h2><p>Level 99 in all skills available in RuneScape Classic.</p>');"></div>
        <?php
        }

        //F2P Maxed
        $f2pLow = min(array($stl['Attack'], $stl['Defence'], $stl['Strength'], $stl['Constitution'], $stl['Ranged'], $stl['Prayer'], $stl['Magic'], $stl['Cooking'], $stl['Woodcutting'], $stl['Fishing'], $stl['Firemaking'], $stl['Crafting'], $stl['Smithing'], $stl['Mining'], $stl['Dungeoneering'], $stl['Runecraft']));
        if ($f2pLow == 99 && $stl['Dungeoneering'] == 120) {
            ?>
            <div class="badge f2pComp" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>F2P Completionist</h2><p>Maximum level in all skills available in Free to Play RuneScape, including 120 Dungeoneering.</p>');"></div>
        <?php
        } else if ($f2pLow == 99) {
            ?>
            <div class="badge f2pMax" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>F2P Maxed</h2><p>Level 99 in all skills available in Free to Play RuneScape.</p>');"></div>
        <?php
        } else {
            ?>
            <div class="badge f2pMax unobtained" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>F2P Maxed</h2><p>Level 99 in all skills available in Free to Play RuneScape.</p>');"></div>
        <?php
        }

        //Billionaire
        if ($stx['Overall'] >= 1000000000) {
            ?>
            <div class="badge onebil" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Billionaire</h2><p>At least 1,000,000,000 total experience.</p>');"></div>
        <?php
        } else {
            ?>
            <div class="badge onebil unobtained" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Billionaire</h2><p>At least 1,000,000,000 total experience.</p>');"></div>
        <?php
        }

        $combatlevel = floor(0.25 * (1.3 * max($stl['Attack'] + $stl['Strength'], 2 * $stl['Magic'], 2 * $stl['Ranged']) + $stl['Defence'] + $stl['Constitution'] + (0.5 * $stl['Prayer']) + (0.5 * $stl['Summoning'])));
        //Master of Combat, Offensive
        if ($combatlevel == 138) {
            ?>
            <div class="badge lvl138" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Master of Combat</h2><p>Achieved the maximum combat level of 138.</p>');"></div>
        <?php
        }else if ($stl['Attack'] == 99 && $stl['Strength'] == 99 && $stl['Magic'] == 99 && $stl['Ranged'] == 99) {
            ?>
            <div class="badge lvl126" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Combatant</h2><p>Level 99 in the following skills:<br/>Attack, Strength, Magic, and Ranged.</p>');"></div>
        <?php
        } else {
            ?>
            <div class="badge lvl126 unobtained" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Combatant</h2><p>Level 99 in the following skills:<br/>Attack, Strength, Magic, and Ranged.</p>');"></div>
        <?php
        }

        //Portmaster
        if ($stl['Fishing'] >= 90 && $stl['Slayer'] >= 90 &&
            $stl['Runecraft'] >= 90 && $stl['Herblore'] >= 90 &&
            $stl['Prayer'] >= 90 && $stl['Thieving'] >= 90 &&
            $stl['Hunter'] >= 90 && $stl['Cooking'] >= 90 &&
            $stl['Construction'] >= 90 && $stl['Agility'] >= 90 &&
            $stl['Divination'] >= 90 && $stl['Dungeoneering'] >= 90) {
            ?>
            <div class="badge portmaster" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Portmaster</h2><p>Level 90+ in all skills required for Player Owned Ports.</p>');"></div>
        <?php
        } else {
            ?>
            <div class="badge portmaster unobtained" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Portmaster</h2><p>Level 90+ in all skills required for Player Owned Ports.</p>');"></div>
        <?php
        }

        $cmbavg = (($stl['Attack'] + $stl['Strength'] + $stl['Ranged'] + $stl['Magic'] + $stl['Summoning'] + $stl['Defence'] + $stl['Prayer'] + $stl['Constitution']) / 8);
        $skillavg = (($stl['Crafting'] + $stl['Mining'] + $stl['Smithing'] + $stl['Fishing'] + $stl['Cooking'] + $stl['Firemaking'] + $stl['Woodcutting']
                + $stl['Runecraft'] + $stl['Dungeoneering'] + $stl['Agility'] + $stl['Herblore'] + $stl['Thieving'] + $stl['Fletching'] + $stl['Slayer']
                + $stl['Farming'] + $stl['Construction'] + $stl['Hunter']) / 17);

        //Balanced, Skiller, Combatant
        if (withinPercentage($cmbavg, $skillavg, 5)) {
            ?>
            <div class="badge balanced" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Balanced</h2><p>Average Combat and Skilling skill levels within 5% of eachother.</p>');"></div>
        <?php
        } else if ($cmbavg > $skillavg) {
            ?>
            <div class="badge combatant" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Killer</h2><p>Higher average Combat skill levels than Skilling skill levels.</p>');"></div>
        <?php
        } else if ($skillavg > $cmbavg) {
            ?>
            <div class="badge skiller" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Skiller</h2><p>Higher average Skilling skill levels than Combat skill levels.</p>');"></div>
        <?php
        }

        //Nothing Like the First, Getting the Hang of This, Law of Nines, Scaper, Maxed, Completionist
        if ($total99s >= 26 && $stl['Dungeoneering'] == 120) {
            ?>
            <div class="badge completionist" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Completionist</h2><p>Maximum total level (all 99s & 120 Dungeoneering).</p>');"></div>
        <?php
        } else if ($total99s >= 26) {
            ?>
            <div class="badge maxed" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Maxed</h2><p>All skills at 99</p>');"></div>
        <?php
        } else if ($total99s >= 15) {
            ?>
            <div class="badge scaper" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Scaper</h2><p>At least 15 skills at 99</p>');"></div>
        <?php
        } else if ($total99s >= 9) {
            ?>
            <div class="badge lawOfNines" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Law of Nines</h2><p>At least 9 skills at 99</p>');"></div>
        <?php
        } else if ($total99s >= 5) {
            ?>
            <div class="badge gettingHangOfThis" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Getting the Hang of This</h2><p>At least 5 skills at 99.</p>');"></div>
        <?php
        } else if ($total99s >= 2) {
            ?>
            <div class="badge trimmed" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Badge (t)</h2><p>At least 2 skills at 99.</p>');"></div>
        <?php
        } else if ($total99s >= 1) {
            ?>
            <div class="badge nothingLikeTheFirst" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Nothing Like the First</h2><p>One skill at 99.</p>');"></div>
        <?php
        }

        if($onetwentycapes >= 1) {
            ?>
            <div class="badge mastercape" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Skill Mastery</h2><p>At least one true skill mastery cape, besides Dungeoneering. (vlvl 120)</p>');"></div>
        <?php
        } else {
            ?>
            <div class="badge mastercape unobtained" onmouseout="changeDesc('');" onmouseover="changeDesc('<h2>Skill Mastery</h2><p>At least one true skill mastery cape, besides Dungeoneering. (vlvl 120)</p>');"></div>
        <?php
        }
    }
