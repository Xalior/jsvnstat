<?php
$interface = "eth0";	/* Interface to monitor, default is eth0 */
$graph_type = "lines";	/* Default look of the graph, "lines" or "bars" */
$time_frame = "hours";	/* Default time frame, "hours", "days", "months" or "top10" */
$tx_color = "#0f0";		/* TX graph color, default is #0f0 */
$rx_color = "#f00";		/* RX graph color, default is #f00 */
$precision = 2;			/* Number of decimal digits to round to, default is 2 (example: 2 = 0.00, 3 = 0.000, etc...) */
$css = "default.css"	/* If you want to change the look, CopyPasta the default.css and adapt is to your needs */

# uncomment (and adapt) to make php stop complainig about timezones:
#date_default_timezone_set('Europe/Berlin');
?>
