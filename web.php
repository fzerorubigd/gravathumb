<?php

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
    !preg_match('/\/([a-zA-Z0-9._]+json)$/', $_SERVER['REQUEST_URI'])) {
    header('HTTP/1.0 304 Not Modified');
    exit;
}
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

if (preg_match('/\/([a-zA-Z0-9._]+png)$/', $_SERVER['REQUEST_URI'], $matches)) {
    header('Content-Type: image/png');
    header('Cache-Control: public');
    header('Content-Disposition: inline; filename="' . $matches[1] . '"');
    header("Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT");
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * 45)) . ' GMT');
    imagepng($image);
} elseif (preg_match('/\/([a-zA-Z0-9._]+json)$/', $_SERVER['REQUEST_URI'], $matches)) {
    header('Content-Type: application/json');
    header('Content-Disposition: inline; filename="' . $matches[1] . '"');
    $json = array(
        'size' => 64,
        'width' => 64,
        'height' => 64,
        'count' => $count
    );
    echo json_encode($json);
}

imagedestroy($image);
