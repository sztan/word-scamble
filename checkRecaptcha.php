<?php
$curl_body = array(
    "secret" => my_get_env('RECAPTCHA_SECRET'),
    "response" => $_POST['grecaptchaToken'],
);
$data_string = "secret=" . $curl_body['secret'] . "&response=" . $curl_body['response'];
//die(var_dump($_POST));
$url = "https://www.google.com/recaptcha/api/siteverify";
$ch = curl_init();
$a[] = curl_setopt($ch, CURLOPT_URL, $url);
$a[] = curl_setopt($ch, CURLOPT_POST, true);
$a[] = curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
$a[] = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = json_decode(curl_exec($ch));

$recaptchaLogMessage="validation recaptcha ...";
if ($result->success) {
    $recaptchaSuccess=true;
    $recaptchaLogMessage.="OK";
}
error_log($recaptchaLogMessage);