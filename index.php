<?php
require_once('svg.php');
$announce = array();
$mainRoad = array();
$puzzleId = array();
define ('GRAS' , 'lightgreen');
define ('ROAD' , 'grey');

function generateSVG() {
    $layout = generateRandomLayout();
    $mainRoad = generateMainRoad($layout);
    $svg = '<svg viewBox="0 0 150 150" width="500" height="500" xmlns="http://www.w3.org/2000/svg">';
    $x = 0;
    $y = 0;
    foreach ($layout as $index => $color) {
        if($index < 4 or $index == 5 or $index == 6){
            $svg .= '<rect x="' . $x . '" y="' . $y . '" width="50" height="50" fill="' . $color . '" />';
        } elseif ($index == 7) {
            $svg .= '<rect x="' . $x . '" y="' . $y . '" width="50" height="50" fill="' . $color . '"></rect>';
            $svg .= generateBicycleSVG($x, $y);
        } elseif ($index == 8) {
            $svg .= '<rect x="' . $x . '" y="' . $y . '" width="50" height="50" fill="' . $color . '"></rect>';
            $svg .= insertTrafficSign($x, $y, $mainRoad, $layout);
        } elseif ($index == 4) {
            $svg .= '<rect x="' . $x . '" y="' . $y . '" width="50" height="50" fill="' . $color . '"></rect>';
            // Zufällige Richtung für den Pfeil wählen
            $svg .= generateArrowSVG($x, $y, $layout);
        }
        $x += 50;
        if (($index + 1) % 3 == 0) {
            $x = 0;
            $y += 50;
        }

    }
    $x = 0;
    $y = 0;

    foreach($layout as $index => $color) {
        if($index==3 && $layout[3] == 'grey'){
            $svg .= generateCar($x, $y, 'lightblue', $layout);
        }
        if($index==5 && $layout[5] == 'grey'){
            $svg .= generateCar($x, $y, 'lightgreen', $layout);
        }
        if($index==1 && $layout[1] == 'grey'){
            $svg .= generateCar($x, $y, 'pink', $layout);
        }

        $x += 50;
        if (($index + 1) % 3 == 0) {
            $x = 0;
            $y += 50;
        }

    }
    // Weitere Logik zur Hinzufügung von Fahrzeugen, Verkehrszeichen usw. hier einfügen
    $svg .= '</svg>';
    return $svg;
}

function announce($from, $to){
    global $announce;
    $announce[$from] = $to;
}

function insertTrafficSign($x, $y, $mainRoad, $layout){
    $sign = '';
    $x_rand = rand(0,1);
    if(isset($_GET['sign'])){
        $x_rand = $_GET['sign'];
    }
    storeData('sign', $x_rand);
    if($mainRoad) {
        if(in_array(7, $mainRoad)){
            $sign = generateMainRoadSignSVG($x, $y, $mainRoad, $layout);
        } else {
            if ($x_rand == 0) $sign = generateGiveWaySignSVG($x, $y, $mainRoad, $layout);
            if ($x_rand == 1) $sign = generateStopSignSVG($x, $y, $mainRoad, $layout);
        }
    }
    return $sign;
}

function storeData($what, $value){
    global $puzzleId;
    $puzzleId[$what]=$value;
}

function guessRightOfWay($mainRoad, $announce){

    // priorities =
    // 00 - policeman
    // 10 - no intersecting traffic
    // 10 - green traffic lights
    // 20 - stay on main road
    // 30 - leave main road
    // 40 - not turning left
    // 50 - no one right of me
    // 60 - stop sign
    // 70 - red traffic lights

    $done = array();
    $carPrios = array();

    foreach($announce as $from=>$to){
        if($from=='stop') continue;
        // car stays on main road - prio 0
        $x = array($from, $to);
        if(sizeof(array_intersect($x, $mainRoad)) == 2){
            $carPrios[$from] = 20;
            $done[$from]=$from;
        }

        // car comes from main road - but diverts.. - prio 1
        if(!isset($done[$from]) && sizeof(array_intersect(array($from), $mainRoad)) == 1){
            // intersect with other in same category??
            $carPrios[$from] = 30;
            $done[$from]=$from;
        }
    }

    $intersect = array();
    foreach($announce as $from=>$to){
        if($from=='stop') continue;
        switch($from){
            case 1:
                $intersect[0][$from]=$from;
                if($to==5){
                    $intersect[2][$from]=$from;
                }
                break;
            case 3:
                $intersect[2][$from]=$from;
                if($to==1){
                    $intersect[3][$from]=$from;
                }
                break;
            case 5:
                $intersect[1][$from]=$from;
                if($to==7){
                    $intersect[0][$from]=$from;
                }
                break;
            case 7:
                $intersect[3][$from]=$from;
                if($to==3){
                    $intersect[1][$from]=$from;
                }
                break;
        }
        switch($to){
            case 1:
                $intersect[1][$from]=$from;
                break;
            case 3:
                $intersect[0][$from]=$from;
                break;
            case 5:
                $intersect[3][$from]=$from;
                break;
            case 7:
                $intersect[2][$from]=$from;
                break;
        }
    }

    // find cars with no intersecting traffic -
    foreach($announce as $from=>$to){
        if($from=='stop') continue;
        $canGo = true;
        foreach($intersect as $corner){
            if(in_array($from, $corner) && sizeof($corner)>1){
                $canGo = false;
            }
        }
        if($canGo){
            // not intersecting - but STOP sign 8applies only on car 7 = bike)
            if(isset($announce['stop']) && $from == 7){
                $carPrios[$from] = 61;
            } else {
                // no intersecting traffic - prio 0
                $carPrios[$from] = 'anytime';
            }
            $done[$from]=$from;
        }
    }


    if(isset($announce['stop'])){
        $carPrios['stop'] = 0;
    }

    foreach($announce as $from=>$to){
        if($from=='stop') continue;
        $isLeftTurn = ($from == 1 && $to == 5) || ($from == 5 && $to == 7) ||
            ($from == 7 && $to == 3) || ($from == 3 && $to == 1);
        if(!$isLeftTurn && !isset($done[$from])){
            $done[$from] = $from;
            $carPrios[$from] = 40;
        }
    }

    foreach($announce as $from=>$to){
        if($from=='stop') continue;
        $rightIsFree = ($from == 1 && !$announce[3]) || ($from == 5 && !$announce[1]) ||
            ($from == 7 && !$announce[5]) || ($from == 3 && $announce[7]);
        if($rightIsFree && !isset($done[$from])){
            $done[$from] = $from;
            $carPrios[$from] = 50;
        }
    }

    // check collision on 30 prio
    $carsOn30 = array();
    foreach($carPrios as $car=>$p){
        if($p==30) $carsOn30[] = $car;
    }
    if(sizeof($carsOn30)>1){
        //echo "carsOn30 = ".implode(', ',$carsOn30);
        // check intersection sector 0
        $x = array_intersect($intersect[0], $carsOn30);
        $x = array_diff($x, array(1));
        foreach ($x as $c){
            //echo "in sector 0 have to wait : ".implode($x)."<br>\n";
            $carPrios[$c]++;
        }
        // check intersection sector 1
        $x = array_intersect($intersect[1], $carsOn30);
        $x = array_diff($x, array(5));
        foreach ($x as $c){
            //echo "in sector 1 have to wait : ".implode($x)."<br>\n";
            $carPrios[$c]++;
        }
        // check intersection sector 2
        $x = array_intersect($intersect[2], $carsOn30);
        $x = array_diff($x, array(3));
        foreach ($x as $c){
            //echo "in sector 2 : ".implode($x)."<br>\n";
            $carPrios[$c]++;
        }
        // check intersection sector 3
        $x = array_intersect($intersect[3], $carsOn30);
        $x = array_diff($x, array(7));
        foreach ($x as $c){
            //echo "in sector 3 : ".implode($x)."<br>\n";
            $carPrios[$c]++;
        }
    }

    // check collision on 30 prio
    $carsOn40 = array();
    foreach($carPrios as $car=>$p){
        if($p==40) $carsOn40[] = $car;
    }
    if(sizeof($carsOn40)>1){
        //echo "carsOn40 = ".implode(', ',$carsOn40);
        // check intersection sector 0
        $x = array_intersect($intersect[0], $carsOn40);
        $x = array_diff($x, array(1));
        foreach ($x as $c){
            //echo "in sector 0 have to wait : ".implode($x)."<br>\n";
            $carPrios[$c]++;
        }
        // check intersection sector 1
        $x = array_intersect($intersect[1], $carsOn40);
        $x = array_diff($x, array(5));
        foreach ($x as $c){
            //echo "in sector 1 have to wait : ".implode($x)."<br>\n";
            $carPrios[$c]++;
        }
        // check intersection sector 2
        $x = array_intersect($intersect[2], $carsOn40);
        $x = array_diff($x, array(3));
        foreach ($x as $c){
            //echo "in sector 2 : ".implode($x)."<br>\n";
            $carPrios[$c]++;
        }
        // check intersection sector 3
        $x = array_intersect($intersect[3], $carsOn40);
        $x = array_diff($x, array(7));
        foreach ($x as $c){
            //echo "in sector 3 : ".implode($x)."<br>\n";
            $carPrios[$c]++;
        }
    }

    if(!$carPrios[1] && $announce[1]) $carPrios[1] = 99;
    if(!$carPrios[3] && $announce[3]) $carPrios[3] = 99;
    if(!$carPrios[5] && $announce[5]) $carPrios[5] = 99;
    if(!$carPrios[7] && $announce[7]) $carPrios[7] = 99;

    asort($carPrios);


    // input is a dynamic array of 1 to 4 cars with same priority

    return $carPrios;
}


echo "<h1>Punkte <span id='p'></span> - Highscore <span id='h'></span></h1>";

echo generateSVG();
$url = '?';
foreach($puzzleId as $key=> $val){
    $url.=$key.'='.$val.'&';
}
echo "<br><A href='".$url."' target='_blank'>Puzzle Url</A><br><hr>";
$prio = guessRightOfWay($mainRoad, $announce);
echo '<hr><pre>';
print_r(
    array(
        //'announce'=>$announce,
        //'mainRoad'=>$mainRoad,
        'prio'=>$prio
    )
);
echo "<script> var data = JSON.parse('".json_encode($prio)."');
var p=0;
var h=0;
h = getCookie('h');
p = getCookie('p');

document.getElementById('h').innerHTML=h;
document.getElementById('p').innerHTML=p;
function clickOn(id){
    if(checkCollision(id)) {
            document.getElementById('car'+id).style.display = 'none';
            document.getElementById('carpath'+id).style.display = 'none';
            delete data[id];
            if(id == 7){
                setCookie('p', ++p, 4);
                if(p>h) setCookie('h', p, 4);
                //alert('Alles richtig! Jetzt darfst Du fahren');
                location.href='?';
                exit;
            }
    } else {
        alert('Es gab einen schrecklichen Unfall!');
        p = 0;
        setCookie('p', 0, 4);
    }
}

function noSmallerValues(obj, key) {
    for (let k in obj) {
        if (obj.hasOwnProperty(k) && k !== key && obj[k] < obj[key] && obj[k] != 'anytime' && obj[k] != 'stop') {
            return false;
        }
    }
    return true;
}

function checkCollision(id){
    if (data[id] == 'anytime') return true;
    if (noSmallerValues(data, id)) return true;
}


if(document.getElementById('car1') != undefined)
document.getElementById('car1').addEventListener('click', function() {
    clickOn(1);
});

if(document.getElementById('car3') != undefined)
document.getElementById('car3').addEventListener('click', function() {
    clickOn(3);
});

if(document.getElementById('car5') != undefined)
document.getElementById('car5').addEventListener('click', function() {
    clickOn(5);
});

if(document.getElementById('car7') != undefined)
document.getElementById('car7').addEventListener('click', function() {
    clickOn(7);
});

if(document.getElementById('stop') != undefined)
document.getElementById('stop').addEventListener('click', function() {
    delete data['stop'];
    this.style.fill='green';
});

if(document.getElementById('stop2') != undefined)
document.getElementById('stop2').addEventListener('click', function() {
    delete data['stop'];
    document.getElementById('stop').style.fill='green';
});

";
echo '
function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var cookies = document.cookie.split(";");
    for(var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i];
        while (cookie.charAt(0) === " ") cookie = cookie.substring(1, cookie.length);
        if (cookie.indexOf(nameEQ) === 0) return cookie.substring(nameEQ.length, cookie.length);
    }
    return null;
}
';
echo "</script>";
