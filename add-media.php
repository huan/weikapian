<?php

if ( 'XXX'!=$_GET['key'] ){
    die('Hard');
}

$url    = $_GET['url'];
$openid = $_GET['openid'];

if ( !$url || !$openid ){
    die('Harder');
}

$time = time();

$filename = "img/" . date('Y/m/d_His_') . $openid . ".jpg";

$s = new SaeStorage();
$s->write(  'media'
        ,$filename
        ,file_get_contents($url) 
        );

echo $s->getUrl('media',$filename);

?>
