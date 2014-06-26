<?php

include "gravatile.php";
$curl = curl_init('http://payment.zconf.ir/?emails=1');

//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($curl);
$emails = explode(',', trim($data));

$tmp = new Gravatile(
    $emails,
    64
);


$image = $tmp->buildTile($count, 'horizontal');

imagepng($image, './tile.png');

$json = array(
    'size' => 64,
    'width' => 64,
    'height' => 64,
    'count' => $count
);
file_put_contents('./tile.json', json_encode($json));