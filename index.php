<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('lib/lib_base.php');


if (isset($_POST['Submit'])) {
    //printr($_FILES);
    //exit;
    //======================= Y axis values (start) ==============================
    $a_destName = file('files/route/' . $_POST['selRoute'] . '.csv');
    $i = 1;
    do {
        $tmp = explode(',', $a_destName[$i]);
        $yStr .= '[' . $tmp[0] . ', "km ' . $tmp[0] . '"], ';
        $y2Str .= '[' . $tmp[0] . ', "' . $tmp[2] . '"], ';
        $mapHeight +=10;
        $i++;
    } while ($i < count($a_destName));
    //======================= Y axis values (end) ================================


    $fileCount = count($_FILES['fileCSV']['name']);

    //============ Parsing data from file into array (start) ============
    foreach ($_FILES['fileCSV']['tmp_name'] as $fkey => $fval) {

        if ($_FILES['fileCSV']['tmp_name'][$fkey] != "") {
            $a_str = file($_FILES['fileCSV']['tmp_name'][$fkey]);
        } else {
            continue;
        }

        //printr($_FILES);
        //printr($a_str);

        $station = "";
        $timestamp = 0;
        $a_result = array();
        $i = 5;
        $counter = count($a_str);
        do {
            $tmp = explode(',', $a_str[$i]);
            if ($tmp[0] == "") {
                $station = $tmp[2];
                $tmp = explode(',', $a_str[++$i]);
            }
            //printr($tmp);
            $timestamp = (int) strtotime(get_real_time_format($tmp[1]));
            //$exists = array_key_exists($timestamp, $a_result);
            if (array_key_exists($timestamp, $a_result)) {
                $a_result[$timestamp]['timestamp'] = $timestamp + 30;
                $a_result[$timestamp]['dateTime'] = date('d/m/Y h:i:sa', $timestamp + 30);
                $a_result[$timestamp + 30] = $a_result[$timestamp]; //if(($timestamp+30)==1282379550)echo "got value";
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
            $a_result[$timestamp]['trainID'] = ucwords(str_replace(array('-', '_'), ' ', basename($_FILES['fileCSV']['name'][$fkey], '.csv')));
            $a_result[$timestamp]['lat'] = $lat[1];
            $a_result[$timestamp]['long'] = $long[1];
            $a_result[$timestamp]['speed'] = trim($speed[1]);
            $a_result[$timestamp]['track-url'] = rtrim(trim($a_str[$i + 2]), "\",");

            $i += 3;
        } while ($i < $counter);
        //============ Parsing data from file into array (end) ==============
        // Sorting array in ascending order
        if ($_POST['selTrack'][$fkey] == 'Up Track') {
            ksort($a_result);
        } else {
            krsort($a_result);
        }

        //echo count($a_result);
        //printr($a_result);
        //============ Generating line string to use in JS (start) ============
        $iTime = 1;
        $iDist = 1;
        $aCount = count($a_result);
        $dd = 0;
        unset($lat2);
        unset($lon2);

        foreach ($a_result as $key => $val) {
            $lat1 = $val['lat'];
            $lon1 = $val['long'];
            //$str .= "[$lat1, $lon1],";
            if ($_POST['selTrack'][$fkey] == 'Up Track') {
                if ($iTime == 1) {
                    $time[$fkey]['minTime'] = $minTime = $val['timestamp'];
                }
                if ($iTime == $aCount) {
                    $time[$fkey]['maxTime'] = $maxTime = $val['timestamp'];
                }
            } else {
                if ($iTime == 1) {
                    $time[$fkey]['maxTime'] = $maxTime = $val['timestamp'];
                }
                if ($iTime == $aCount) {
                    $time[$fkey]['minTime'] = $minTime = $val['timestamp'];
                }
            }

            if (!isset($str[$fkey]['trainID'])) {
                $str[$fkey]['trainID'] = $val['trainID'];
            }

            if (isset($lat2) && isset($lon2)) {
                $d = get_distance($lat1, $lat2, $lon1, $lon2);

                $dd += $d;

                if ($iDist == 1 || !isset($minDist)) {
                    $minDist = $d;
                }
                if ($iDist == $aCount) {
                    $maxDist = $dd;
                }

                $str[$fkey]['line'] .= "[" . $val['timestamp'] . ", " . ($dd) . "],";
            }
            $lat2 = $lat1;
            $lon2 = $lon1;

            $iTime++;
            $iDist++;
        }// End result loop
        $str[$fkey]['track'] = $_POST['selTrack'][$fkey];
    }// End file loop
    //============ Generating line string to use in JS (end) ==============
    //============ Making xAxis base line value string to use in JS (start) ============
    if (is_array($time)) {
        foreach ($time as $val) {
            if ($minTime > $val['minTime']) {
                $minTime = $val['minTime'];
            }
            if ($maxTime < $val['maxTime']) {
                $maxTime = $val['maxTime'];
            }
        }
    }
    //printr($time);
    //echo $minTime.'::'.$maxTime;
    $tStamp = strtotime(date('Y-m-d H:00:00', $minTime));
    $interval = 60;
    for ($i = $minTime; $i <= ($maxTime + ($maxTime - $minTime)); $i+=60) {
        if ((($interval / 60) % 20) == 0) {
            //echo "$tStamp<br/>";
            $xStr .= '[' . ($tStamp + $interval) . ', "<div>' . date("M,d-H:i", ($tStamp + $interval)) . '</div>"], ';
        }
        $interval += 60;
        //echo $i."<br>";
    }
    //============ Making xAxis base line value string to use in JS (end) ==============

}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>GPS Track Report Graph</title>
    <link href="css/layout.css" rel="stylesheet" type="text/css"></link>
    <link href="css/style.css" rel="stylesheet" type="text/css"></link>
    <!--[if IE]><script language="javascript" type="text/javascript" src="js/excanvas.min.js"></script><![endif]-->
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="js/jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="js/jquery.flot.navigate.js"></script>

 </head>
    <body style="<?php echo isset($_GET['print']) ? "print" : "";?>">
        <div class="page-header print_area">
            <h1>Train Control Chart</h1>
            <h3>Bangladesh Railway</h3>
            <h4>[ Developed by - Suncrops ]</h4>
        </div>
        <hr class="print_area" style="padding-bottom:20px;border:0;border-top: 1px dotted #000;" />
    <form name="form1" enctype="multipart/form-data" method="post" action="">
        Select the route:
        <div id="track-root">
            <select id="selRoute" name="selRoute">
                <option value="">--select a route</option>
                <option value="Dhaka-Bahadurabad" <?php if($_POST['selRoute']=='Dhaka-Bahadurabad') echo "selected";?>>Dhaka-Bahadurabad</option>
                <option value="Dhaka-Chittagong" <?php if($_POST['selRoute']=='Dhaka-Chittagong') echo "selected";?>>Dhaka-Chittagong</option>
                <option value="Dhaka-Khulna" <?php if($_POST['selRoute']=='Dhaka-Khulna') echo "selected";?>>Dhaka-Khulna</option>
                <option value="Dhaka-Rajshahi" <?php if($_POST['selRoute']=='Dhaka-Rajshahi') echo "selected";?>>Dhaka-Rajshahi</option>
                <option value="Dhaka-Sylhet" <?php if($_POST['selRoute']=='Dhaka-Sylhet') echo "selected";?>>Dhaka-Sylhet</option>
            </select>
        </div>
        
        Submit CSV track report file:
        <div id="track-file-group">
			<div class="track-file">
				<input name="fileCSV[]" type="file" id="fileCSV[]">
				<select name="selTrack[]">
					<option>Up Track</option>
					<option>Down Track</option>
				</select>
			</div>
		</div>
        <input type="submit" id="btn-submit" name="Submit" value="  Submit  ">
        <input type="button" id="btnAddMore" value="  Add More  ">
        <input type="button" id="btnPrint" value="  Print  " onclick="window.print();">
        <br>
        <br>
    </form>
    <div class="print_area" id="placeholder" style="width:1000px;height:800px;"></div>

    <script id="source" language="javascript" type="text/javascript">
$(function () {
	
	var placeholder = $("#placeholder");
	 
	<?php 
	$colorCode = 2;
	foreach($str as $key => $val){
		$Data .= '{data:['.rtrim($val['line'], ',').'], label: "'.$val['trainID'].' : '.$val['track'].'", color: '.($colorCode++).' },'."\n";
	}
	?>

	//var d2 = [<?php echo rtrim($str[0], ',');?>];
	//var d2Data = {data:d2, label: "Up track", color: 2 };

	var data = [ <?php echo rtrim($Data, ",\n");?>, {data:[], color: 1,  yaxis: 2 } ];

    var plot = $.plot(placeholder, data,
		{ 
			series: { lines: { show: true }, shadowSize: 0 },
			xaxis: {
                ticks: [ <?php echo rtrim($xStr, ', ');?> ],
				tickFormatter: 1
            },
			yaxis: {
                ticks: [ <?php echo rtrim($yStr, ', ');?> ],
				min: 0
            },
			y2axis: {
                ticks: [ <?php echo rtrim($y2Str, ', ');?> ],
				min: 0
            },
			grid: {
				backgroundColor: { colors: ["#fff", "#ccc"] }
			},
			zoom: {
				interactive: true
			},
			pan: {
				interactive: true
			}

		}
	);
	
	// add zoom out button 
    $('<div class="button-zoom-out">zoom out</div>').appendTo(placeholder).click(function (e) {
        e.preventDefault();
        plot.zoomOut();
    });
	
	// add zoom in button 
    $('<div class="button-zoom-in">zoom in</div>').appendTo(placeholder).click(function (e) {
        e.preventDefault();
        plot.zoom();
    });

    // add Route label
    $('<div class="route-label">Selected route is- <br/><b><?=$_POST['selRoute'];?></b></div>').appendTo(placeholder);
	
	// and add panning buttons
    // little helper for taking the repetitive work out of placing
    // panning arrows
    function addArrow(dir, right, top, offset) {
        $('<img class="button" src="images/arrow-' + dir + '.gif" style="right:' + right + 'px;top:' + top + 'px">').appendTo(placeholder).click(function (e) {
            e.preventDefault();
            plot.pan(offset);
        });
    }
    addArrow('left', 885, 40, { left: -100 });
    addArrow('right', 855, 40, { left: 100 });
    addArrow('up', 870, 25, { top: -100 });
    addArrow('down', 870, 55, { top: 100 });


});
</script>

    <script type="text/javascript">

$(document).ready(function(){

    var file_browser = '<div class="track-file"> <input name="fileCSV[]" type="file" id="fileCSV[]"> <select name="selTrack[]"> <option>Up Track</option> <option>Down Track</option> </select> </div>';
    var file_browser_with_button = '<div class="track-file"> <input name="fileCSV[]" type="file" id="fileCSV[]"> <select name="selTrack[]"> <option>Up Track</option> <option>Down Track</option> </select> <input type="button" id="btnRemove" onclick="removeThisTrack(this);" value="  Remove  "></div>';

    $('#btnAddMore').click(function(){
        $('#track-file-group').append(file_browser_with_button);
    });

    removeThisTrack = function(elem){
        $(elem).parent().fadeOut(200, function(){
            $(this).remove();
        });
	};

    //$('#track-file-group').append(file_browser);

    $('#btn-submit').click(function(){
        if($('#selRoute').val()==""){
            alert('Your have to choose a route first.');
            $('#selRoute').focus();
            //return false;
        }
    });
});

    </script>

 </body>
</html>