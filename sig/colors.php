<?php
    class color {
        public $r;
        public $g;
        public $b;

        function __construct($r, $g, $b) {
            $this->r = $r;
            $this->g = $g;
            $this->b = $b;
        }
    }

    class milestone {
        public $outer;
        public $inner;
        public $ring;

        function __construct($outer, $inner, $ring) {
            $this->outer = new color($outer[0], $outer[1], $outer[2]);
            $this->inner = new color($inner[0], $inner[1], $inner[2]);
            $this->ring  = new color($ring[0],  $ring[1],  $ring[2] );
        }
    }
                    //milestone(outer, inner, ring)
    $ms = array(
        10             => new milestone(array(27 , 112, 52 ), array(87 , 189, 90 ), array(255, 255, 255)),
        20             => new milestone(array(27 , 112, 52 ), array(138, 199, 58 ), array(255, 255, 255)),
        30             => new milestone(array(184, 167, 15 ), array(237, 231, 40 ), array(255, 255, 255)),
        40             => new milestone(array(251, 196, 33 ), array(254, 228, 54 ), array(255, 255, 255)),
        50             => new milestone(array(156, 80 , 42 ), array(235, 116, 56 ), array(255, 255, 255)),
        60             => new milestone(array(161, 11 , 11 ), array(235, 105, 101), array(255, 255, 255)),
        70             => new milestone(array(245, 158, 240), array(255, 217, 249), array(255, 255, 255)),
        80             => new milestone(array(124, 46 , 176), array(214, 101, 207), array(255, 255, 255)),
        90             => new milestone(array(1  , 86 , 137), array(2  , 140, 223), array(255, 255, 255)),
        "max"          => new milestone(array(126, 44 , 44 ), array(216, 122, 122), array(255, 255, 255)),
        "comp-regular" => new milestone(array(163, 163, 155), array(214, 214, 214), array(255, 255, 255)),
        "comp-trimmed" => new milestone(array(170, 172, 29 ), array(230, 222, 14 ), array(255, 255, 255))
    );