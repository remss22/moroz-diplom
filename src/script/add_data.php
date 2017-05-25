<?php
require __DIR__ . '/../../vendor/autoload.php';

$sensor = $argv[1];
$sensor = 'c4ca4238a0b923820dcc509a6f75849b';
$maxValue = $argv[2];
$maxValue = 500;
$minValue = $argv[3];
$minValue = 100;
$countSignal = isset($argv[4]) ? $argv[4] : 100;
use \Curl\Curl;

while ($countSignal !== 0) {
    $signal = [
        'hash' => $sensor,
        'signal' => rand($minValue, $maxValue),
    ];
    $curl = new Curl();
    $curl->post("http://192.168.21.21/addData/{$sensor}", ['data' => $signal]);
    if ($curl->error) {
        echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
    } else {
        echo 'Response:' . "\n";
        var_dump($curl->response);
    }
    $countSignal --;
}