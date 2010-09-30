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

if(isset($_POST['Submit']))
{
	if(isset($_FILES['fileCSV']['tmp_name'])){
		$a_str = file($_FILES['fileCSV']['tmp_name']);
	}else{
		//$a_str = file('./files/tista_report.csv');
	}
	
	//printr($_FILES);
	//printr($a_str);
	
	$station = "";
	$a_result = array();
	$i=5;
	$counter = count($a_str);
	do{
		$tmp = explode(',', $a_str[$i]);
		if($tmp[0]==""){
			$station = $tmp[2];
			$tmp = explode(',', $a_str[++$i]);
		}
		//printr($tmp);
		$timestamp = (int) strtotime(get_real_time_format($tmp[1]));
		$exists = array_key_exists($timestamp, $a_result);
		if(array_key_exists($timestamp, $a_result)){
			$a_result[$timestamp]['timestamp'] = $timestamp + 30;
			$a_result[$timestamp]['dateTime'] = date('d/m/Y h:i:sa', $timestamp + 30);
			$a_result[$timestamp + 30] = $a_result[$timestamp];
		}
		//----------------------------------------------------
		$latlong = explode(' ', $tmp[2]);
		$lat = explode(':', $latlong[0]);
		$long = explode(':', $latlong[1]);
		$speed = explode(':', $latlong[2]);
		//----------------------------------------------------
		$a_result[$timestamp]['station'] = trim($station);
		$a_result[$timestamp]['timestamp'] = $timestamp;
		$a_result[$timestamp]['dateTime'] = date('d/m/Y h:i:sa', $timestamp);
		$a_result[$timestamp]['trainID'] = $tmp[0];
		$a_result[$timestamp]['lat'] = $lat[1];
		$a_result[$timestamp]['long'] = $long[1];
		$a_result[$timestamp]['speed'] = trim($speed[1]);
		$a_result[$timestamp]['track-url'] = rtrim(trim($a_str[$i+2]), "\",");
	
		$i += 3;
		
	}while($i<$counter);
	
	ksort($a_result);
	
	//echo count($a_result);
	//printr($a_result);
	
	foreach ($a_result as $key => $val) {
		$lat1 = $val['lat'];
		$lon1 = $val['long'];
		//$str .= "[$lat1, $lon1],";
		if(isset($lat2) && isset($lon2)){
			$d = acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lon1 - $lon2));
			$dd += $d;
			$time += date('s', $val['timestamp']);
			$str .= "[".$time.", ".($dd)."],";
			$str2 .= "[".($time+20).", ".($dd+0.1)."],";
		}
		$lat2 = $lat1;
		$lon2 = $lon1;
	}
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>GPS Track Report Graph</title>
    <link href="css/layout.css" rel="stylesheet" type="text/css"></link>
    <!--[if IE]><script language="javascript" type="text/javascript" src="js/excanvas.min.js"></script><![endif]-->
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="js/jquery.flot.js"></script>
 </head>
    <body>
    <h1>Graph Presentation<br>
    </h1>

    <form name="form1" enctype="multipart/form-data" method="post" action="">
      Submit a CSV track report file: 
        <input name="fileCSV" type="file" id="fileCSV">
        <input type="submit" name="Submit" value="  Submit  ">
        <br>
        <br>
    </form>
    <div id="placeholder" style="width:1000px;height:1000px;"></div>

    <script id="source" language="javascript" type="text/javascript">
$(function () {

    var d1 = [<?php echo rtrim($str, ',');?>];
	//var d2 = [[0, 3], [4, 8], [8, 5], [9, 6], [17, 7], [20, 8], [28, 9]];
    var d2 = [<?php echo rtrim($str2, ',');?>];

    $.plot($("#placeholder"), [ {data:d1, label: "Up track"}, {data:d2, label: "Down track", color: 2 } ],
		{ 
		xaxis: {
            ticks: [0, [Math.PI/2, "\u03c0/2"], [Math.PI, "\u03c0"], [Math.PI * 3/2, "3\u03c0/2"], [Math.PI * 2, "2\u03c0"]]
        },
		yaxis: {
            ticks: [0, [Math.PI/2, "\u03c0/2"], [Math.PI, "\u03c0"], [Math.PI * 3/2, "3\u03c0/2"], [Math.PI * 2, "2\u03c0"]]
        }

		}
	);
});
</script>

 </body>
</html>