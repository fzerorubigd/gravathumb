<?php

include "gravatile.php";


$tmp = new Gravatile(
    [
        'fzerorubigd@gmail.com',
        'kahzad@gmail.com',
        'fzerorubigd@gmail.com',
        'reza@gmail.com',
        'fzerorubigd@gmail.com',
        'hasan@gmail.com',
        'fzerorubigd@gmail.com',
        'hosein@gmail.com',
        'fzerorubigd@gmail.com',
        'chalist@gmail.com',
        'fzerorubigd@gmail.com',
        'alireza@gmail.com',
    ],
    64
);

$count = $tmp->buildTile();
$json = array(
    'size' => 64,
    'count' => $count
);

file_put_contents('./tile.json', json_encode($json));