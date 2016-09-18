<?php

$lng     = $_GET['lng'];
$msg     = str_replace("_", " ", $_GET['msg']);

file_put_contents('php://stderr', print_r($msg, TRUE));

//$basefile = uniqid();
$basefile = hash('tiger192,3', $msg);
$filewav = $basefile . '.wav';
$filemp3 = $basefile . '.mp3';

require 'vendor/autoload.php';

use Lame\Lame;
use Lame\Settings;

if(!file_exists($filemp3)) {

// perform TTS using pico2wave
try {
    exec('/usr/bin/pico2wave -l=' . $lng . ' -w=' . $filewav . ' "' . $msg . '"');
} catch(\RuntimeException $e) {
    var_dump($e->getMessage());
}

// encoding type
$encoding = new Settings\Encoding\VBR();
$encoding->setMinBitrate(64);

// lame settings
$settings = new Settings\Settings($encoding);
$settings->setAlgorithmQuality(5);

// encoding type
//$encoding = new Settings\Encoding\Preset();
//$encoding->setType(Settings\Encoding\Preset::TYPE_STANDARD);

// lame settings
//$settings = new Settings\Settings($encoding);

// encoding type
//$encoding = new Settings\Encoding\NullEncoding();

// lame settings
//$settings = new Settings\Settings($encoding, array(
//    '--cbr'     => true,
//    '-b'        => 256,
//    '--resample'=> '44.1'
//));

// lame wrapper
$lame = new Lame('/usr/bin/lame', $settings);

// convert wav - mp3 using lame
try {
    $lame->encode($filewav, $filemp3);
} catch(\RuntimeException $e) {
    var_dump($e->getMessage());
}
unlink($filewav);
}

// send the converted mp3 back to the client
if(file_exists($filemp3)) {
    header('Content-Transfer-Encoding: binary');
    header('Content-Type: audio/mpeg');
    header('Content-length: ' . filesize($filemp3));
    header('Content-Disposition: filename=' . $filemp3);
    header('Cache-Control: no-cache');
    header('icy-br: 128');
    header('icy-name: TTS Announcement: ' . $msg);
    readfile($filemp3);
} else {
    header("HTTP/1.0 404 Not Found");
}

?>
