<?php
//Shehan Bhavan - hSenid Mobile Solutions
//shehanb@hsenidmobile.com
//Dialog Ideamart
ini_set('error_log', 'ussd-app-error.log');

require 'libs/MoUssdReceiver.php';
require 'libs/MtUssdSender.php';
require 'class/operationsClass.php';
require 'libs/Log.php';
require 'db.php';


$production=false;

	if($production==false){
		$ussdserverurl ='http://localhost:7000/ussd/send';
	}
	else{
		$ussdserverurl= 'https://api.dialog.lk/ussd/send';
	}


$receiver 	= new UssdReceiver();
$sender 	= new UssdSender($ussdserverurl,'APP_000001','password');
$operations = new Operations();

$content 			= 	$receiver->getMessage(); // get the message content
$address 			= 	$receiver->getAddress(); // get the sender's address
$requestId 			= 	$receiver->getRequestID(); // get the request ID
$applicationId 		= 	$receiver->getApplicationId(); // get application ID
$encoding 			=	$receiver->getEncoding(); // get the encoding value
$version 			= 	$receiver->getVersion(); // get the version
$sessionId 			= 	$receiver->getSessionId(); // get the session ID;
$ussdOperation 		= 	$receiver->getUssdOperation(); // get the ussd operation


$responseMsg = array(
    "main" =>  
    "Welcome To hSenid Mobile

1. Singapore 
2. SriLanka 

99. Exit"
);


if ($ussdOperation  == "mo-init") { 
   
	try {
		
		$sessionArrary=array("sessionsid"=>$sessionId,"tel"=>$address,"menu"=>"main","pg"=>"","others"=>"");

  		$operations->setSessions($sessionArrary);

		$sender->ussd($sessionId, $responseMsg["main"],$address );

	} catch (Exception $e) {
			$sender->ussd($sessionId, 'Please try again',$address );
	}
	
}else {

	$flag=0;
  	$sessiondetails=  $operations->getSession($sessionId);
  	$cuch_menu=$sessiondetails['menu'];
  	$operations->session_id=$sessiondetails['sessionsid'];

		switch($cuch_menu){
		
			case "main": 	// Following is the main menu
					switch ($receiver->getMessage()) {
						case "1":
							$operations->session_menu="Singapore";
							$operations->saveSesssion();
							$sender->ussd($sessionId,'Enter Your ID',$address );
							break;
						case "2":
							$operations->session_menu="SriLanka";
							$operations->saveSesssion();
							$sender->ussd($sessionId,'Enter Your ID',$address );
							break;
						default:
							$operations->session_menu="main";
							$operations->saveSesssion();
							$sender->ussd($sessionId, $responseMsg["main"],$address );
							break;
					}
					break;
			case "Singapore":
				$operations->session_menu="Srilanka";
				$operations->session_others=$receiver->getMessage();
				$operations->saveSesssion();
				$sender->ussd($sessionId,'hSenid Mobile Solutions (Singapore)
7500A, Beach Road,
#05-322 The Plaza,
Singapore 199591.

+65 65 332 140
+65 62 910 023'.$receiver->getMessage(),$address ,'mt-fin');
				break;
			case "Srilanka":
				$sender->ussd($sessionId,'hSenid Mobile Solutions
No 320, 3rd Floor,
T.B.Jayah Mawatha,
Colombo 10.

+94 11 268 6751
+94 11 268 3951'.$receiver->getMessage(),$address ,'mt-fin');
				break;
			default:
				$operations->session_menu="main";
				$operations->saveSesssion();
				$sender->ussd($sessionId,'Incorrect option',$address );
				break;
		}
	
}