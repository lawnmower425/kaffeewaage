<?php

session_start();

initSessionValues();

ini_set("display_errors", "on");
ini_set("error_reporting", E_ALL);

$sFBottletxt = "fullBottle.txt";
$sEBottletxt = "emptyBottle.txt";

$aCooks = array(
	
	'Julia',
	'David',
	'Antje',
	'René',
	'Silvio',
	'Tino',
	'Stephan'
);

$iCurrValue		=  exec("python waage.py read");
$iDivMaxHeight	= 371;
$iTimeIsCooking	= 1;

//a possible error to not determine the last bit as a missing bottle
$iErrorMargin	= 2;

if (isset($_POST["order"])) {

	switch($_POST["order"]) {

		case "empty":
		file_put_contents($sEBottletxt, $iCurrValue);
		break;
		case "full":
		file_put_contents($sFBottletxt, $iCurrValue);
		break;
	}
}

$iFullBottle	= (int)file_get_contents($sFBottletxt);
$iEmptyBottle	= (int)file_get_contents($sEBottletxt); 
$iDiffValue		= $iFullBottle - $iEmptyBottle;

//if the bottle is missing for the first time, notice it
if (bottleIsMissing($iCurrValue, $iEmptyBottle, $iFullBottle, $iErrorMargin)) {
	
	//it's not been missing before: start the timer!!11
	if ($_SESSION['timeBottleDisappeared'] == 0) {
		
		$_SESSION['timeBottleDisappeared'] = time();
	}
	
	//if the bottle has been away long enough, get a new cook - it must be cooking now
	if (bottleIsCooking($iTimeIsCooking, $_SESSION['timeBottleDisappeared'])) {

		$_SESSION['nextCook']				= findNewCook($aCooks, $_SESSION['lastCook']);
		//reset the bottle missing timer, because we would otherwise always recalc the cook
		//we assume that the bottle will be back in time, otherwise
		$_SESSION['timeBottleDisappeared']	= 0;
	}

} else {
	
	//just to be safe, the bottle is here, no need for a timer
	$_SESSION['timeBottleDisappeared']	= 0;
}

if ($iDiffValue > 0 && $iCurrValue >= $iEmptyBottle) {
	
	//correct values are set
	$iHeightPerFill = round($iDivMaxHeight / ($iDiffValue));
	$iHeight = ($iHeightPerFill * ($iCurrValue - $iEmptyBottle));
	$iTopPos = $iDivMaxHeight - $iHeight;
	
} else {

	//the values are somehow shitty
	$iHeightPerFill = 0;
	$iTopPos = $iDivMaxHeight;
	$iHeight = 0;
}

/**
echo $iFullBottle."<br>";
echo $iEmptyBottle."<br>";
echo $iCurrValue."<br>";
echo $iDiffValue."<br>";
echo $iHeight."<br>";
**/

/**
 * Check if the bottle is in the kitchen - this needs some time!
 * e.G. it could be in the hand of someone talking...
 */
function bottleIsCooking($iTimeTillCooking, $iTimeBottleDisappeared) {
	
	return (time() - $iTimeBottleDisappeared) / 60 >= $iTimeTillCooking;
}

/**
 * Check if the bottle is still standing there
 * 
 * 
 */
function bottleIsMissing($iCurrValue, $iEmptyBottle, $iFullBottle, $iErrorMargin) {
	
	if ($iCurrValue < ($iEmptyBottle - $iErrorMargin) || $iCurrValue > ($iFullBottle + $iErrorMargin)) {
		
		return true;
	}
	return false;
}

/**
 * Will give the name of the next cook
 * @return string
 */
function findNewCook($aCooks, $sLastCook) {
	
	$_SESSION['lastCook'] = $sLastCook;
	//to prevent duplicate cooks, set them equal and select till different
	$sNextCook = $sLastCook;
	while ($sNextCook == $sLastCook) {
		
		$sNextCook = $aCooks[mt_rand(0, count($aCooks) -1)];
	}

	return $sNextCook;
}

/**
 * Initialize session values to prevent notices 'n shit
 */
function initSessionValues()	{
	
	if (!isset($_SESSION['lastCook'])) {
		
		$_SESSION['lastCook'] = '';
		$_SESSION['nextCook'] = '';
		$_SESSION['timeBottleDisappeared'] = 0;
	}
}

?> <html>
	<head>
		<style>
			body {
				text-align:center;
			}
			form {
				display:inline-block;
				margin:auto;

			}
			input {

				font-size: 0.7em;
			}
			div.bottleFrame {

				width: 350px;
				margin:auto;
				height: 550px;
				border: 1px solid red;
			}
			div.floatingDiv {

				float:left;
				width:48%;
					}
					div.bottlepic {

						background-image: url(img/Kanne02.jpg);
						background-size: 100%;
					}
			div.outerBottle {

				border: 1px solid black;
				width:76px;
				position:relative;
				top:15px;
				left:113px;
				height:<?php echo $iDivMaxHeight; ?>px;
				font-size:5em;
				overflow:auto;
			}
			div.bottle {

				height: <?php echo $iHeight; ?>px;
				position: absolute;
				top: <?php echo $iTopPos; ?>px;
				background-color: black;
				width:100%;
			}
			div.nextCookDiv {

				font-size:3.5em;
				width:100%;
				color:white;
			}

		</style>
	</head>
	<body>
		<div class="bottleFrame bottlepic">
			
			<div class="nextCookDiv">
				<br>
				<?php 
				//if the bottle is reaching emptiness - when it's cooking, we do not need this hint
				//BUT, if it is cooking - we could set another picture :D
				if ($iCurrValue <= (($iFullBottle - $iEmptyBottle) / 3 + $iEmptyBottle)) { echo '- '.$_SESSION['nextCook'].' -'; } ?>
			</div>
			<div class="outerBottle">
				
				<?php if (bottleIsMissing($iCurrValue, $iEmptyBottle, $iFullBottle, $iErrorMargin)) { echo "<br>?"; } ?>
				<div class="bottle"></div>
			</div><br>
			<form action="" method="post">
				<input type="submit" value="Flasche leer" />
				<input type="hidden" name="order" value="empty" />
			</form>
			<form action="" method="post">
					<input type="submit" value="Flasche voll" />
					<input type="hidden" name="order" value="full" />
				</form>
		</div>
	</body>

</html>
