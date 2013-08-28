<?
	include("GCMSender.php");

	$GCMSender	=	new GCMSender("AIzaSyATO4u2LXkE1bRR_BJHS7W3L2Edy6RDx5w");
	
	//$GCMSender->setDryrun(true);
	$GCMSender->setRegistrationIds(array(
										"APA91bFJ3TpYnliJcXWuh7DRc71tmzaJ64HRuY4gAlTMCD1vu_Rn49TgXcp2VSRTd52PySUAiU5YJLRIepevVxlddxkYOgHAfKfAURaRq8ewzGTfugxoT_5JTRznJkvpJ6wcOMQW_sg1rfe65vyuMK8TBTCbI6us_Q",
										"APA91bFJ3TpYnliJcXWuh7DRc71tmzaJ64HRuY4gAlTMCD1vu_Rn49TgXcp2VSRTd52PySUAiU5YJLRIepevVxlddxkYOgHAfKfAURaRq8ewzGTfugxoT_5JTRznJkvpJ6wcOMQW_sg1rfe65vyuMK8TBTCbIsfsdfsdf"
									));
	print_r($GCMSender->sendMessage(array("Jan" => "ist Gut")));
	