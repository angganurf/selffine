<?php
$pixelphoto->AgorachannelName = "stream_".$me['user_id'].'_'.rand(1111111,9999999);
$pixelphoto->AgoraToken = null;
if (!empty($config['agora_app_certificate'])) {

	include(dirname(__DIR__)."/src/RtcTokenBuilder.php");

	$appID = $config['agora_app_id'];
	$appCertificate = $config['agora_app_certificate'];
	$uid = 0;
	$uidStr = "0";
	$role = RtcTokenBuilder::RoleAttendee;
	$expireTimeInSeconds = 36000000;
	$currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
	$privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
	$pixelphoto->AgoraToken = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $pixelphoto->AgorachannelName, $uid, $role, $privilegeExpiredTs);
}
?>
