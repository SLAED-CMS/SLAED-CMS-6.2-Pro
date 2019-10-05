<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

global $conf;
$words = $conf['keywords'];
if (is_array($words)) {
	$kwords = "<style scoped>
	.tags a {
		display: inline-block;
		height: 21px;
		margin: 0 15px 10px 0;
		padding: 0 7px 0 14px;
		white-space: nowrap;
		position: relative;
		
		background: -moz-linear-gradient(top, #fed970 0%, #febc4a 100%);
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#fed970), color-stop(100%,#febc4a));
		background: -webkit-linear-gradient(top, #fed970 0%,#febc4a 100%);
		background: -o-linear-gradient(top, #fed970 0%,#febc4a 100%);
		background: linear-gradient(to bottom, #fed970 0%,#febc4a 100%);
		background-color: #FEC95B;
		
		color: #963;
		font: bold 12px/20px Arial, Tahoma, sans-serif;
		text-decoration: none;
		text-shadow: 0 1px rgba(255,255,255,0.4);
		
		border-top: 1px solid #EDB14A;
		border-bottom: 1px solid #CE922E;
		border-right: 1px solid #DCA03B;
		border-radius: 1px 3px 3px 1px;
		box-shadow: inset 0 1px #FEE395, 0 1px 2px rgba(0,0,0,0.21);
	}
	.tags a:before {
		content: '';
		position: absolute;
		top: 5px;
		left: -6px;
		width: 10px;
		height: 10px;
		
		background: -moz-linear-gradient(45deg, #fed970 0%, #febc4a 100%);
		background: -webkit-gradient(linear, left bottom, right top, color-stop(0%,#fed970), color-stop(100%,#febc4a));
		background: -webkit-linear-gradient(-45deg, #fed970 0%,#febc4a 100%);
		background: -o-linear-gradient(45deg, #fed970 0%,#febc4a 100%);
		background: linear-gradient(135deg, #fed970 0%,#febc4a 100%);
		background-color: #FEC95B;
		
		border-left: 1px solid #EDB14A;
		border-bottom: 1px solid #CE922E;
		border-radius: 0 0 0 2px;
		box-shadow: inset 1px 0 #FEDB7C, 0 2px 2px -2px rgba(0,0,0,0.33);
	}
	.tags a:before {
		-webkit-transform: scale(1, 1.5) rotate(45deg);
		-moz-transform: scale(1, 1.5) rotate(45deg);
		-ms-transform: scale(1, 1.5) rotate(45deg);
		transform: scale(1, 1.5) rotate(45deg);
	}
	.tags a:after {
		content: '';
		position: absolute;
		top: 7px;
		left: 1px;
		width: 5px;
		height: 5px;
		background: #FFF;
		border-radius: 4px;
		border: 1px solid #DCA03B;
		box-shadow: 0 1px 0 rgba(255,255,255,0.2), inset 0 1px 1px rgba(0,0,0,0.21);
	}
	</style>";
	foreach ($words as $val) {
		if ($val != "") $kwords .= "<a href=\"index.php?name=search&amp;word=".urlencode($val)."\" title=\"".$val."\">".$val."</a>";
	}
	$content = "<div class=\"tags\">".$kwords."</div>";
}
?>