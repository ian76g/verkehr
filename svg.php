<?php
function generateGiveWaySignSVG($x, $y, $mainRoad, $layout) {
    $svg = '';
    $svg .= '<polygon points="' . ($x + 12.5) . ',' . ($y + 20) . ' ' . ($x+5) . ',' . ($y+5) . ' ' . ($x + 20) . ',' . ($y+5) . '" fill="white" stroke="red" stroke-width="2" />';
    $svg .= '';
    $svg .= generateMainRoadAddOn($x, $y, $mainRoad, $layout);
    return $svg;
}

function generateMainRoadSignSVG($x, $y, $mainRoad, $layout) {
    $svg = '';
    $svg .= '<rect x="' . ($x + 5) . '" y="' . ($y + 5) . '" width="15" height="15" fill="white" stroke="yellow" stroke-width="2" transform="rotate(45 ' . ($x + 12.5) . ' ' . ($y + 12.5) . ')" />';
    $svg .= '';
    $svg .= generateMainRoadAddOn($x, $y, $mainRoad, $layout);
    return $svg;
}
function generateRandomLayout() {
    $layout = array_fill(0, 9, GRAS);
    $layout[4] = ROAD;
    $layout[7] = ROAD;
    $random = array(1,1,1,0);
    shuffle($random);
    $layout[1] = $random[0]?ROAD:GRAS;
    $layout[3] = $random[1]?ROAD:GRAS;
    $layout[5] = $random[2]?ROAD:GRAS;
    if(isset($_GET['layout'])){
        $layout = array();
        $items = preg_split('//', $_GET['layout'], -1, PREG_SPLIT_NO_EMPTY);
        foreach($items as $item){
            $layout[]=($item?ROAD:GRAS);
        }
    }
    $layoutString = '';
    foreach($layout as $l){
        $layoutString.=($l==ROAD)?1:0;
    }
    storeData('layout', $layoutString);
    return $layout;
}
function generateMainRoadAddOn($x, $y, $mainRoad, $layout) {
    $svg = '';
    // 1 3 5 7
    $mainRoadString = implode('', $mainRoad);
    if($mainRoadString == '35' || $mainRoadString == '17') {
        if($mainRoadString == '35') {
            // what da fuq?
            //$svg .= '<path d="M'.($x+2+5).','.(27.5+$y+0+5).' '.(15+$x+5-2).','.(27.5+$y+0+5).'" fill="none" stroke="black" stroke-width="2" />';
        }
        if($mainRoadString == '17') {
            // what da fuq?
        }
    } else {
        $svg .= '<rect x="' . ($x + 5) . '" y="' . ($y + 25) . '" width="15" height="15" fill="white" stroke="black" stroke-width="0" />';
        if($mainRoadString == '13') {
            $svg .= '<path d="M'.(7.5+$x+2+5).','.(20+$y+2+5).' A7.5,7.5 0 0,1 '.($x+5+2).','.(27.5+$y+2+5).'" fill="none" stroke="black" stroke-width="2" />';
        }
        if($mainRoadString == '15') {
            $svg .= '<path d="M'.(15+$x-2+5).','.(27.5+$y+2+5).' A7.5,7.5 0 0,1 '.($x+7.5-2+5).','.(20+$y+2+5).'" fill="none" stroke="black" stroke-width="2" />';
        }
        if($mainRoadString == '57') {
            $svg .= '<path d="M'.($x+7.5-2+5).','.(35+$y-2+5).' A7.5,7.5 0 0,1 '.(15+$x-2+5).','.(27.5+$y-2+5).'" fill="none" stroke="black" stroke-width="2" />';
        }
        if($mainRoadString == '37') {
            $svg .= '<path d="M'.($x+2+5).','.(27.5+$y-2+5).' A7.5,7.5 0 0,1 '.($x+7.5+2+5).','.(35+$y-2+5).'" fill="none" stroke="black" stroke-width="2" />';
        }
        if($layout[1]==ROAD && strpos($mainRoadString, '1') === false){
            $svg .= '<rect  x="' . ($x+12) . '" y="' . ($y+27) . '" width="1" height="3" fill="black" />';
        }
        if($layout[7]==ROAD && strpos($mainRoadString, '7') === false){
            $svg .= '<rect  x="' . ($x+12) . '" y="' . ($y+27+8) . '" width="1" height="3" fill="black" />';
        }
        if($layout[3]==ROAD && strpos($mainRoadString, '3') === false){
            $svg .= '<rect  x="' . (5+$x+2) . '" y="' . ($y+31) . '" width="3" height="1" fill="black" />';
        }
        if($layout[5]==ROAD && strpos($mainRoadString, '5') === false){
            $svg .= '<rect  x="' . (5+$x+10) . '" y="' . ($y+31) . '" width="3" height="1" fill="black" />';
        }
    }
    return $svg;
}

function generateStopSignSVG($x, $y, $mainRoad, $layout) {
    $svg = '';
    $svg .= '<polygon id="stop" points="' . ($x + 8) . ',' . $y . ' ' . ($x + 17) . ',' . $y . ' ' . ($x + 24) . ',' . ($y + 7) . ' ' . ($x + 24) . ',' . ($y + 17) . ' ' . ($x + 17) . ',' . ($y + 24) . ' ' . ($x + 8) . ',' . ($y + 24) . ' ' . $x . ',' . ($y + 17) . ' ' . $x . ',' . ($y + 7) . '" fill="red" />';
    $svg .= '<text id="stop2" x="' . ($x + 12.5) . '" y="' . ($y + 14) . '" font-family="Arial" font-size="7" fill="white" text-anchor="middle">STOP</text>';
    $svg .= generateMainRoadAddOn($x, $y, $mainRoad, $layout);
    announce('stop','stop');

    return $svg;
}

// Funktion zur Generierung des SVG für ein Fahrrad
function generateBicycleSVG($x, $y) {
    $svg = '';
    $svg .= '<rect id="car7" x="' . ($x+35) . '" y="' . ($y+10) . '" width="3" height="20" fill="white" />';
    $svg .= '<rect id="car7a" x="' . ($x + 31) . '" y="' . ($y + 12) . '" width="11" height="2" fill="black" />';
    $svg .= '';
    return $svg;
}

function generateCar($x, $y, $color, $layout){
    if($x==100){
        $offsetX = 5;
    }
    if($x == 0) {
        $offsetX = 10;
    }
    if($x==50) {
        $offsetX = 5;
    }
    if($x==0){
        $offsetY = 27;
    } else {
        $offsetY = 2;
    }
    if($y==50){
        $height = 18;
        $width = 35;
    } else {
        $height = 35;
        $width = 18;
    }

    $svg = '';

    if($x==0 && $y==50){
        $svg .= generateLeftCarPath($color, $layout);
        $id = 3;
    }
    if($x==50 && $y==0){
        $svg .= generateTopCarPath($color, $layout);
        $id = 1;
    }
    if($x==100 && $y==50){
        $svg .= generateRightCarPath($color, $layout);
        $id = 5;
    }

    $svg .= '<rect id="car'.$id.'" width="'.$width.'" height="'.$height.'" x="'.($x+$offsetX).'" y="'.($y+$offsetY).'" rx="2"
      stroke="silver" fill="'.$color.'" stroke-width="1" />';
    $svg .= '';


    return $svg;

}

function generateLeftCarPath($color, $layout){
    $x = 50; $y = 50;
    $svg = '';
    $possible = array();
    if($layout[1]==ROAD){
        $possible[] = 1;
    }
    if($layout[5]==ROAD){
        $possible[] = 5;
    }
    if($layout[7]==ROAD){
        $possible[] = 7;
    }
    shuffle($possible);
    if(isset($_GET['left'])) $possible[0]=$_GET['left'];
    switch($possible[0]){
        case 5:
            $svg .= '<polygon id="carpath3" points="' . ($x + 1) . ',' . ($y + 39) . ' ' . ($x + 49) . ',' . ($y + 39) . ' ' . ($x + 49) . ',' . ($y + 36). ' ' . ($x + 1) . ',' . ($y + 36) . '" fill="'.$color.'" />';
            announce(3, 5);
            break;
        case 1:
            $svg .= '<path id="carpath3" d="M'.($x+37.5).','.($y+1).' A37,37 0 0,1 '.($x+1).','.($y+37.5).'" fill="none" stroke="'.$color.'" stroke-width="3" />';
            announce(3, 1);
            break;
        case 7:
            $svg .= '<path id="carpath3" d="M'.($x+1).','.($y+37.5).' A12.5,12.5 0 0,1 '.($x+12.5).','.($y+49).'" fill="none" stroke="'.$color.'" stroke-width="3" />';
            announce(3, 7);
            break;
    }
    storeData('left', $possible[0]);
    return $svg;
}

function generateTopCarPath($color, $layout){
    $x = 50; $y = 50;
    $svg = '';
    $possible = array();
    if($layout[3]==ROAD){
        $possible[] = 3;
    }
    if($layout[5]==ROAD){
        $possible[] = 5;
    }
    if($layout[7]==ROAD){
        $possible[] = 7;
    }
    shuffle($possible);
    if(isset($_GET['top'])) $possible[0]=$_GET['top'];
    switch($possible[0]){
        case 7:
            $svg .= '<polygon id="carpath1" points="' . ($x + 11) . ',' . ($y + 1) . ' ' . ($x + 11) . ',' . ($y + 49) . ' ' . ($x + 14) . ',' . ($y + 49). ' ' . ($x + 14) . ',' . ($y + 1) . '" fill="'.$color.'" />';
            announce(1,7);
            break;
        case 3:
            $svg .= '<path id="carpath1" d="M'.($x+12.5).','.($y+1).' A12.5,12.5 0 0,1 '.($x+1).','.($y+12.5).'" fill="none" stroke="'.$color.'" stroke-width="3" />';
            announce(1,3);
            break;
        case 5:
            $svg .= '<path id="carpath1" d="M'.($x+49).','.($y+37.5).' A37,37 0 0,1 '.($x+12.5).','.($y+1).'" fill="none" stroke="'.$color.'" stroke-width="3" />';
            announce(1,5);
            break;
    }
    storeData('top', $possible[0]);
    return $svg;
}

function generateRightCarPath($color, $layout){
    $x = 50; $y = 50;
    $svg = '';
    $possible = array();
    if($layout[1]==ROAD){
        $possible[] = 1;
    }
    if($layout[3]==ROAD){
        $possible[] = 3;
    }
    if($layout[7]==ROAD){
        $possible[] = 7;
    }
    shuffle($possible);
    if(isset($_GET['right'])) $possible[0]=$_GET['right'];
    switch($possible[0]){
        case 1:
            $svg .= '<path id="carpath5" d="M'.($x+49).','.($y+12.5).' A12,12 0 0,1 '.($x+37.5).','.($y+1).'" fill="none" stroke="'.$color.'" stroke-width="3" />';
            announce(5,1);
            break;
        case 3:
            $svg .= '<polygon id="carpath5" points="' . ($x + 1) . ',' . ($y + 14) . ' ' . ($x + 49) . ',' . ($y + 14) . ' ' . ($x + 49) . ',' . ($y + 11). ' ' . ($x + 1) . ',' . ($y + 11) . '" fill="'.$color.'" />';
            announce(5,3);
            break;
        case 7:
            $svg .= '<path id="carpath5" d="M'.($x+12.5).','.($y+49).' A37.5,37.5 0 0,1 '.($x+49).','.($y+12.5).'" fill="none" stroke="'.$color.'" stroke-width="3" />';
            announce(5,7);
            break;
    }
    storeData('right', $possible[0]);
    return $svg;
}

function generateMainRoad($layout){
    global $mainRoad;
    $possible=array();

    if(!rand(0,5)){
        storeData('mainRoad', '');
        return $possible;
    }

    foreach($layout as $index => $piece){
        if($index==4) continue;
        if($piece == ROAD){
            $possible[]=$index;
        }
    }

    shuffle($possible);
    $mainRoad = array_slice($possible, 0, 2);
    sort($mainRoad);

    if(isset($_GET['mainRoad'])){
        $mainRoad = preg_split('//', $_GET['mainRoad'], -1, PREG_SPLIT_NO_EMPTY);
    }
    storeData('mainRoad', implode('', $mainRoad));
    return $mainRoad;
}

function generateArrowSVG($x, $y, $layout) {
    $possible = array();
    if($layout[1]==ROAD){
        $possible[] = 'straight';
    }
    if($layout[3]==ROAD){
        $possible[] = 'left';
    }
    if($layout[5]==ROAD){
        $possible[] = 'right';
    }
    shuffle($possible);
    $direction = array_pop($possible);
    if(isset($_GET['direction'])){
        $direction = $_GET['direction'];
    }
    storeData('direction', $direction);
    $svg = '';
    if ($direction == 'left') {
        $svg .= '<path id="carpath7" d="M'.($x+1).','.(12.5+$y).' A37,37 0 0,1 '.(37.5+$x).','.(50+$y-1).'" fill="none" stroke="white" stroke-width="3" />';
        announce(7,3);
    }
    elseif ($direction == 'straight') {
        $svg .= '<polygon id="carpath7" points="' . ($x + 36) . ',' . ($y + 50 -1) . ' ' . ($x + 36) . ',' . ($y + 2) . ' ' . ($x + 39) . ',' . ($y + 2). ' ' . ($x + 39) . ',' . ($y + 50-1) . '" fill="white" />';
        announce(7,1);
    }
    elseif ($direction == 'right') {
        $svg .= '<path id="carpath7" d="M'.(37.5+$x).','.(50+$y-1).' A12.5,12.5 0 0,1 '.(50+$x-1).','.(37.5+$y).'" fill="none" stroke="white" stroke-width="3" />';
        announce(7,5);
    }
    $svg .= '';

    return $svg;
}
