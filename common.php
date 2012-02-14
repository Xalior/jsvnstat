<?php
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
