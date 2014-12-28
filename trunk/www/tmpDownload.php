<?php

$prefix = '880x1047_';
$img = $_GET['img'];
$filename = $prefix . $img;
$file = $_SERVER['DOCUMENT_ROOT'] . '/ss/attraction/www/data/images/catalog/products/' . $_GET['pid'] . '/' . $filename;

if (file_exists($file))
{
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename=' . basename($file)); 
    header('Content-Type: image/jpeg');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}
