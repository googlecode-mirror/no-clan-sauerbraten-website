<?php
require_once 'config.php';

function send_mail ($to, $subject, $body, $attachment='')
{
	include_once 'mail/class.phpmailer.php';
	include_once 'mail/class.smtp.php';
	
	$mail = new PHPMailer();
	$mail->IsSMTP();
	
	$mail->SMTPAuth = true;	
	
	$mail->SMTPSecure = "ssl";
	$mail->Host = "smtp.gmail.com";
	$mail->Port = 465;

	$mail->IsHTML(true);
	
	$mail->Username = NOCLAN_EMAIL;
	$mail->Password = NOCLAN_EMAIL_PASS;
	$mail->From     = NOCLAN_EMAIL;
	$mail->FromName = 'The -NC-Team';
	
	if(is_array($to)){
		foreach($to as $t){ $mail->AddAddress("$t"); }
	}else{ $mail->AddAddress("$to"); }
	
	if (is_array($attachment)){
		foreach($attachment as $a){
			if (is_file($a)){ $mail->AddAttachment($a); }
		}
		
	} elseif (is_file($attachment)){ $mail->AddAttachment($attachment); }
	
	$mail->Subject = "$subject";
	$mail->MsgHTML("$body");
	$mail->AltBody = strip_tags($body);
	
	if(!$mail->Send()) return false;
	else return true;
}
?>
