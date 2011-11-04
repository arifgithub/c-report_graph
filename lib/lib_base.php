<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/****************************************************
    [6] => Tista 707-708,8/21/2010 13:23,"lat:25.157975 long:089.755680 speed:000.0

    [7] => T:21/08/10  13:23

    [8] => http://www.wxlxy.com/GPSTracker.aspx?key=354776837427636@1263800.150@4534080.712",

    --------------------------------------------------

    d = acos( sin(lat1) * sin(lat2) + cos(lat1) * cos(lat2) * cos(lon1-lon2) )

 *****************************************************/


function printr($a_str)
{
    echo "<pre>";
    print_r($a_str);
    echo "</pre>";
}

function get_real_time_format($date_str)
{
    $a1 = explode(' ', $date_str);
    $a2 = explode('/', $a1[0]);

    $new_date_str = $a2[2].'/'.$a2[0].'/'.$a2[1].' '.$a1[1];

    return $new_date_str;
}

function get_distance($lat1, $lat2, $lon1, $lon2)
{
    //$radius = 3437.74677; // nautical miles
    //$radius = 3963.0; // statute miles

    $radius = 6378.7; // kilometers

    $dist = $radius * acos(sin($lat1/57.2958) * sin($lat2/57.2958) + cos($lat1/57.2958) * cos($lat2/57.2958) * cos($lon2/57.2958 - $lon1/57.2958));

    return $dist;
}


?>
