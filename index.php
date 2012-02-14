<?php
require 'settings.php';
/* Update and get data from vnstat
 * This assumes that vnstat is executable on the server
 * and that a database exists. */
$fd = popen("vnstat --dumpdb -i $interface", "r");
$buffer = '';
while (!feof($fd)) {
	$buffer .= fgets($fd);
}
pclose($fd);
$line = explode("\n", $buffer);
$info = array(); $hour = array(); $day = array(); $month = array(); $top10 = array();
/* Fill the arrays above with the appropriate data from the vnstat output
 * The first letter denotes which array a line belongs to
 * For more information, see the vnstat documentation */
for($i = 0; $i < sizeof($line); ++$i) {
    $line[$i] = explode(";", $line[$i]);
	switch ($line[$i][0]) {
		case "d":
			array_push($day, $line[$i]);
			break;
		case "m":
			array_push($month, $line[$i]);
			break;
		case "t":
			array_push($top10, $line[$i]);
			break;
		case "h":
			array_push($hour, $line[$i]);
			break;
		default:
			array_push($info, $line[$i]);
	}
}
/* Ok, let's start to generate some HTML, shall we? */
echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>jsvnstat - interactive network traffic analysis</title>
		<link href="css/'.$css.'" rel="stylesheet" type="text/css" />
		<!--[if IE]><script language="javascript" type="text/javascript" src="js/excanvas.pack.js"></script><![endif]-->
		<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
		<script language="javascript" type="text/javascript" src="js/jquery.flot.js"></script>
		<script language="javascript" type="text/javascript" src="js/radio.js"></script>
		<script language="javascript" type="text/javascript">
			$(function () {
				var datasets = {
					"hourstx": {
						label: "KB TX", color: "'.$tx_color.'", shadowSize: 5,
						data: '.jsarray($hour, 4).'
					},
					"hoursrx": {
						label: "KB RX", color: "'.$rx_color.'", shadowSize: 5,
						data: '.jsarray($hour, 3).'
					},
					"daystx": {
						label: "MB TX", color: "'.$tx_color.'", shadowSize: 5,
						data: '.jsarray($day, 4).'
					},
					"daysrx": {
						label: "MB RX", color: "'.$rx_color.'", shadowSize: 5,
						data: '.jsarray($day, 3).'
					},
					"monthstx": {
						label: "MB TX", color: "'.$tx_color.'", shadowSize: 5,
						data: '.jsarray($month, 4).'
					},
					"monthsrx": {
						label: "MB RX", color: "'.$rx_color.'", shadowSize: 5,
						data: '.jsarray($month, 3).'
					},
					"top10tx": {
						label: "MB TX", color: "'.$tx_color.'", shadowSize: 5,
						data: '.jsarray($top10, 4, 1).'
					},
					"top10rx": {
						label: "MB RX", color: "'.$rx_color.'", shadowSize: 5,
						data: '.jsarray($top10, 3, 1).'
					}
				};
				var key = "hours";
				var graph_type = "'.$graph_type.'";
				var previousPoint = null;
				var row = null; var rowx = 0;
				var choiceContainer = $("#choices");
				choiceContainer.find("input").click(plotAccordingToChoices);
				var typeContainer = $("#types");
				typeContainer.find("input").click(plotAccordingToChoices);
				plotAccordingToChoices();
						 
				function plotAccordingToChoices() {
					var data = [];

					choiceContainer.find("input:checked").each(function () {
						key = $(this).attr("value");
						data.push(datasets[key+"tx"]);
						data.push(datasets[key+"rx"]);
					});
					
					typeContainer.find("input:checked").each(function () {
						graph_type = $(this).attr("value");
					});
					
					if (graph_type != "bars") {
						var plot = $.plot($("#placeholder"), data, {
							lines: { show: true },
							points: { show: true },	
							grid: { hoverable: true },
							xaxis: { tickDecimals: 0 },
							yaxis: { tickDecimals: 0, min: 0 }
						});
					} else {
						$.plot($("#placeholder"), data, {
							bars: { show: true },	
							grid: { hoverable: true },
							xaxis: { tickDecimals: 0 },
							yaxis: { tickDecimals: 0, min: 0 }
						});
					}
					
					// show corresponding table
					hide("hours_table");
					hide("days_table");
					hide("months_table");
					hide("top10_table");
					show(key+"_table");
				}
				
				function showTooltip(x, y, contents) {
					$(\'<div id="tooltip">\' + contents + \'</div>\').css( {
						position: \'absolute\',
						display: \'none\',
						top: y + 5,
						left: x + 5,
						border: \'1px solid #c2e78c\',
						padding: \'2px\',
						\'background-color\': \'#d1f899\',
						opacity: 0.80
					}).appendTo("body").fadeIn(200);
				}

				$("#placeholder").bind("plothover", function (event, pos, item) {
					if (item) {
						if (previousPoint != item.datapoint) {
							previousPoint = item.datapoint;
							$("#tooltip").remove();
							if (row) {row.style.backgroundColor = "";}
							var x = item.datapoint[0].toFixed(2),
								y = item.datapoint[1].toFixed(2);
							
							if (key == "hours") {
								showTooltip(item.pageX, item.pageY,	Math.floor(x) + ":00 - " + Math.floor(y) + " " + item.series.label);
							} else if (key == "top10") {
								showTooltip(item.pageX, item.pageY,	"Place #" + Math.floor(x) + ": " + Math.floor(y) + " MB");
							} else {
								showTooltip(item.pageX, item.pageY,	Math.floor(x) + " " + key + " ago: " + Math.floor(y) + " " + item.series.label);
							}
							row = document.getElementById(key+"_"+item.datapoint[0]);
							row.style.backgroundColor = "#d1f899";
							rowx = Math.floor(x);
							if (key == "top10") {rowx += 1;}
						}
					}
					else {
						$("#tooltip").remove();
						if (row) {row.style.backgroundColor = "";}
						previousPoint = null;
					}
				});
				
				function show(item){
					document.getElementById(item).style.display = "block";
				}
				
				function hide(item){
					document.getElementById(item).style.display = "none";
				}
			});
		</script>
	</head>
	<body>
		<div id="header">
			<h1>jsvnstat<h1>
		</div>
		<div id="leftcolumn">
			<p id="choices">
				<label class="hiddenJS" for="r1">
					<input class="hiddenJS" type="radio" name="group1" id="r1" value="hours" '; if ($time_frame == "hours") {echo "checked";} echo '></input>
					Hours&nbsp;&nbsp;&nbsp;</label><br /><br />
				<label class="hiddenJS" for="r2">
					<input class="hiddenJS" type="radio" name="group1" id="r2" value="days" '; if ($time_frame == "days") {echo "checked";} echo '></input>
					Days&nbsp;&nbsp;&nbsp;&nbsp;</label><br /><br />
				<label class="hiddenJS" for="r3">
					<input class="hiddenJS" type="radio" name="group1" id="r3" value="months" '; if ($time_frame == "months") {echo "checked";} echo '></input>
					Months&nbsp;&nbsp;</label><br /><br />
				<label class="hiddenJS" for="r4">
					<input class="hiddenJS" type="radio" name="group1" id="r4" value="top10" '; if ($time_frame == "top10") {echo "checked";} echo '></input>
					Top 10&nbsp;&nbsp;</label><br /><br />
			</p>
			<p id="types">
				<label class="hiddenJS" for="g1" style="font-size: 9px; padding-right: 7px;">
					<input class="hiddenJS" type="radio" name="group2" id="g1" value="lines" '; if ($graph_type == "lines") {echo "checked";} echo '></input>
					Lines</label>
				<label class="hiddenJS" for="g2" style="font-size: 9px; padding-right: 6px;">
					<input class="hiddenJS" type="radio" name="group2" id="g2" value="bars" '; if ($graph_type == "bars") {echo "checked";} echo '></input>
					Bars&nbsp;</label>
			</p>
			<br />
			<h3>Interface</h3>
			<acronym title="'.$info[3][1].'">'.$info[2][1].'</abbr><br />
			<br />
			<h3>Total (GB)</h3>
			<acronym title="'.round($info[7][1] + ($info[11][1]/1024), 0).' MB">TX: '.round(($info[7][1] + ($info[11][1]/1024))/1024, 1).'</abbr><br />
			<acronym title="'.round($info[6][1] + ($info[10][1]/1024), 0).' MB">RX: '.round(($info[6][1] + ($info[10][1]/1024))/1024, 1).'</abbr><br />
			<br />
			<h3>Uptime</h3>
			<acronym title="since '.date("d.m.Y, H:i", $info[12][1]).'">'.floor((time() - $info[12][1]) / 3600).'h '.floor(((time() - $info[12][1]) / 60) % 60).'min</abbr><br />
			<br />
			<h3>Database</h3>
			Created:<br />
			'.date("d.m.Y H:i:s", $info[4][1]).'<br />
			Last update:<br />
			'.date("d.m.Y H:i:s", $info[5][1]).'<br />
			<br />
			<h3><a href="" onclick="window.location.reload(false);">&rarr; reload</a></h3>
		</div>
		<div id="content">';
		// check if we have all the data we need, and if not, warn the user
		if (sizeof($hour) != 24 || sizeof($day) != 30 || sizeof($month) != 12 || sizeof($top10) != 10) {
			echo '<p class="warning">Failed to retrieve data from vnstat!</p><br />
					<small>Ensure that:<br />
					<ul style="margin-left: 30px;">
						<li>vnstat is installed</li>
						<li>vnstat is executable (check php security settings)</li>
						<li>vnstat has a database (if not: vnstat -u -i eth0)</li>
					</ul></small>
				';
		}
echo '			<div id="placeholder"></div>
			<br />
			<div id="tables">'.
			table($hour, "hours", "H:00", "MB",$precision).
			table($day, "days", "D, d.m.Y", "GB", $precision).
			table($month, "months", "M Y", "GB", $precision).
			table($top10, "top10", "d.m.Y, H:i", "GB", $precision, 1).'
			</div>	
		</div>
		<div id="footer">
			jsvnstat v1.9 was created by <a href="http://www.rakudave.ch">rakudave</a> for <a href="http://humdi.net/vnstat">vnstat</a>, using <a href="http://code.google.com/p/flot/">flot</a> and <a href="http://www.chriserwin.com/scripts/crir">crir</a>, and is published under the <a href="http://en.wikipedia.org/wiki/Gplv3#Version_3">GPLv3</a> License
		</div>
	</body>
</html>';

/* Turns a php array into a javascript-readable array.
 * This new array has only two columns, hence the index argument (2. column) */
function jsarray($array2D, $index, $offset = 0) {
	$out = "[";
    for ($i = 0; $i < sizeof($array2D); ++$i) {
		$out .= "[".($i+$offset).",".$array2D[$i][$index]."],";
	}
	return substr($out, 0, -1)."]";
}

function table($array2d, $name, $dateformat, $unit, $precision, $offset = 0) {
$class = '';
$out = '
<div id="'.$name.'_table">
	<table class="graph">
		<tr class="title"><td>Time</td><td>TX</td><td>RX</td><td>Ratio</td><td>Total</td></tr>';
			for ($i = 0; $i < sizeof($array2d); ++$i) {
				if (($i % 2) == 1) {$class = "odd";} else {$class="even";}
				$out .= '
				<tr id="'.$name.'_'.($i + $offset).'" class="'.$class.'">';
					if ($array2d[$i][2] != 0) {
						$out .= '<td class="time">'.date($dateformat, $array2d[$i][2]).'</td>';
					} else {
						$out .= '<td class="time">-</td>';
					}
				$out .=	'<td>'.round($array2d[$i][4] / 1024, $precision).' '.$unit.'</td>
					<td>'.round($array2d[$i][3] / 1024, $precision).' '.$unit.'</td>
					<td>'.round($array2d[$i][4] / ($array2d[$i][3] + 0.001), $precision).'</td>
					<td>'.round(($array2d[$i][3] + $array2d[$i][4]) / 1024, $precision).' '.$unit.'</td>
				</tr>';
			}
$out .= '
	</table>
</div>';
return $out;
}
?>
