<?php
require_once 'GoogleAuthenticator.php';
$ga = new PHPGangsta_GoogleAuthenticator();

/*Check what to do*/
if(isset($_REQUEST["logout"])){
	$doLogin = false;
	$deleteAllCookies = true;
}elseif(isset($_REQUEST["softlogout"])){
	$doLogin = false;
	$deleteCodeCookie = true;
}elseif(isset($_REQUEST["code"]) && isset($_REQUEST["secret"])){
	$secret = $_REQUEST["secret"];
	$oneCode = $_REQUEST["code"];
	$doLogin = true;
	$saveInCookies = true;
}elseif(isset($_COOKIE["code"]) && isset($_COOKIE["secret"])){
	$secret = $_COOKIE["secret"];
	$oneCode = $_COOKIE["code"];
	$doLogin = true;
}elseif(isset($_COOKIE["secret"]) && isset($_REQUEST["code"])){
	$secret = $_COOKIE["secret"];
	$oneCode = $_REQUEST["code"];
	$hasSecret = true;
	$doLogin = true;
	$saveInCookies = true;
}elseif(isset($_COOKIE["secret"])){
	$secret = $_COOKIE["secret"];
	$hasSecret = true;
	$doLogin = false;
}else{
	$doLogin = false;
}

/*Login*/
if($doLogin){ //you have to use a new code every 2 minutes (4*30secs)
	$loggedIn = $ga->verifyCode($secret, $oneCode, 4);
	if(!$loggedIn){$deleteCodeCookie = true;}
}else{$loggedIn = false;}

/*Save in Cookies*/
if($saveInCookies && $loggedIn){ // 3600 = 1 hour
	setcookie("secret", $secret, time() + (3600 * 24 * 356));	//secret lasts a year
	setcookie("code", $oneCode, time() + (3600 * 2));			//code lasts two hours, but is getting deleted when google auth. makes a new one
}

/*Delete Old Cookies*/
if($deleteCodeCookie){setcookie("code",	"", time() - 3600);	header("Location: ?");}
if($deleteAllCookies){setcookie("code",	"", time() - 3600);	setcookie("secret",	"", time() - 3600); header("Location: ?");}
	
/*Output*/
if($loggedIn){
	/*Do desired Action*/
	if(isset($_REQUEST["command"])){switch($_REQUEST["command"]){
		case "restart":	/* shell_exec("restart.sh");*/	echo '<a href="?" style="float: right;">X</a>Server is restarting...<hr/>';	break;
		case "start":	/* shell_exec("start.sh");*/	echo '<a href="?" style="float: right;">X</a>Server is starting...<hr/>';		break;
		case "stop":	/* shell_exec("stop.sh");*/		echo '<a href="?" style="float: right;">X</a>Server is stopping...<hr/>';		break;
		default: 										echo '<a href="?" style="float: right;">X</a>Unknown Command<hr/>';	 		break;
	}}
	/*Show Admin Control Panel*/
	?>
		<div style="float: right; text-align: right;"><a href="?softlogout">Close Session</a><br/><a href="?logout">Full Log-Out</a></div>
		<h1>Admin Control Panel</h1>
		<hr/>
		<a href="?command=restart">Restart</a> | <a href="?command=start">Start</a> | <a href="?command=stop">Stop</a> | 
		<hr/>
		<pre style="overflow: auto; height: 500px;"><?=file_get_contents("latest.log")?></pre>
		<input type="submit" style="float: right; width: 100px;"/>
		<input type="text" style="width: calc(100% - 120px);" placeholder="Enter a Command"/>
		<hr/>
		Currently online:<br/>
		<ul style="padding: 0px;">
			<li><img src="https://blockspot.eu/img/skin/head/50/fliege01.png" style="height: 10px;"/> fliege01 <a href="?action=ban&player=fliege01">Ban</a> | <a href="?action=kick&player=fliege01">Kick</a> | <a href="?action=deop&player=fliege01">De-Op</a></li>
			<li><img src="https://blockspot.eu/img/skin/head/50/GlabbichRulz.png" style="height: 10px;"/> GlabbichRulz <a href="?action=ban&player=GlabbichRulz">Ban</a> | <a href="?action=kick&player=GlabbichRulz">Kick</a> | <a href="?action=deop&player=GlabbichRulz">De-Op</a></li>
		</ul>
	<?php
}else{
	/*Show Registration Form*/
	if(!$hasSecret){
		$secret = $ga->createSecret();
		$qrCodeUrl = $ga->getQRCodeGoogleUrl('BlockSpot Admin Control', $secret);
		echo '<img src="'.$qrCodeUrl.'"/><br/>';
		echo $secret."<br/>";
	}else{
		echo '<div style="float: right;"><a href="?logout">Log-Out</a></div>';
		echo 'You were inactive for too long, please enter a new code.';
	}
	?>
	<form method="POST" action = "?">
		<input type="hidden" name="secret" value="<?=$secret?>"/>
		<input type="text"   name="code" placeholder="Code (e.g. 217534)" autofocus/>
		<input type="submit" value="Anmelden"/>
	</form>
<?php
}

?>
