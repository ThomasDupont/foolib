<?php

namespace bin\services;


final class Imagick {
    public static function createCropThumbernail(string $path, int $w = WIDTH, int $h = HEIGHT, string $outPath = "")
    : string
    {

        $outPath = empty($outPath) ? $path : $outPath;
        $cmd = "convert";
        $arg = "'".$path."' -resize ".$w."x".$h."^ -gravity center -extent ".$w."x".$h." '".$outPath."'";
        //var_dump($cmd." ".$arg);
        exec($cmd." ".$arg, $output, $status);
        return ($status == 0) ? $outPath : $output;
    }
}
