<?php
require 'vendor/autoload.php';

use Intervention\Image\ImageManagerStatic as Image;


// and you are ready to go ...
if(isset($_GET["img"])){
    $main = Image::canvas(1280, 720);
    $img = Image::make($_GET["img"])->heighten(598)->rotate(3);
    $main->insert($img, "center");
    $layer = Image::make("/var/www/html/bots/myanimetv/photoshop/layoutBanner.png")->widen(1280);
    $main->insert($layer, "center");
    $name = $_GET["name"];
    $main->save("/var/www/html/bots/myanimetv/resources/img/$name");
    //echo $main->response();
}