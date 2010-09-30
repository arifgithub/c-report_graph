<?php

//-----------------------[Notes Print (start)]----------------------------
$notes = <<< eof
<h2>Notes:</h2>
<u>Take three points as:</u>
A: Lat 23.2695, Long 91.6095
B: Lat 23.5465, Long 89.7895
C: Lat 24.8945, Long 89.9445

<u>Distances in-between points which i derived from calculation:</u>
AB: 188.537 km
BC: 150.134 km
CA: 247.096 km
<br /><hr />
<h1>Output</h1><br />
eof;

echo "<pre>";
echo $notes;
echo "</pre>";
//-----------------------[Notes Print (end)]-----------------------------

//-----------------------[Variable Declare (start)]-----------------------------
$lat1 = 23.2695;
$lon1 = 91.6095;

$lat2 = 23.5465;
$lon2 = 89.7895;

$lat3 = 24.8945;
$lon3 = 89.9445;
//-----------------------[Variable Declare (end)]------------------------------

//-----------------------[Formula-1 (start)]------------------------------
function lat_lon_distance($lat1, $lat2, $lon1, $lon2)
{
    $x = 69.1 * ($lat2 - $lat1);
    $y = 69.1 * ($lon2 - $lon1) * cos($lat1/57.3);
    $miles = sqrt($x * $x + $y * $y);
    $kilometers = 1.609 * $miles;

    return $kilometers;
}

echo "<h4>Formula-1:</h4>";
echo "Distance bet<sup>n</sup> A & B is: ". lat_lon_distance($lat1, $lat2, $lon1, $lon2) ." km";
echo "<br />";
echo "Distance bet<sup>n</sup> B & C is: ". lat_lon_distance($lat2, $lat3, $lon2, $lon3) ." km";
echo "<br />";
echo "Distance bet<sup>n</sup> C & A is: ". lat_lon_distance($lat3, $lat1, $lon3, $lon1) ." km";
//-----------------------[Formula-1 (end)]-------------------------------

//-----------------------[Formula-2 (start)]------------------------------
function get_distance2($lat1, $lat2, $lon1, $lon2)
{
    //$radius = 3437.74677; // nautical miles
    //$radius = 3963.0; // statute miles

    $radius = 6378.7; // kilometers

    $dist = $radius * acos(sin($lat1/57.2958) * sin($lat2/57.2958) + cos($lat1/57.2958) * cos($lat2/57.2958) * cos($lon2/57.2958 - $lon1/57.2958));

    return $dist;
}

echo "<br /><br /><br />";
echo "<h4>Formula-2(This is best formula)**:</h4>";
echo "Distance bet<sup>n</sup> A & B is: ". get_distance2($lat1, $lat2, $lon1, $lon2) ." km";
echo "<br />";
echo "Distance bet<sup>n</sup> B & C is: ". get_distance2($lat2, $lat3, $lon2, $lon3) ." km";
echo "<br />";
echo "Distance bet<sup>n</sup> C & A is: ". get_distance2($lat3, $lat1, $lon3, $lon1) ." km";
echo "<br /><br /><br />";
//-----------------------[Formula-2 (end)]-------------------------------
?>