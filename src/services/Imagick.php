<?php

namespace src\services;

/**
 * Class Imagick
 * @package src\services
 */
final class Imagick
{
    /**
     * @param string $path
     * @param int    $w
     * @param int    $h
     * @param string $outPath
     * @return array
     */
    public static function createCropThumbernail(string $path, int $w = WIDTH, int $h = HEIGHT, string $outPath = ""): array
    {
        $outPath = empty($outPath) ? $path : $outPath;
        $cmd = "convert";
        $arg = "'".$path."' -resize ".$w."x".$h."^ -gravity center -extent ".$w."x".$h." '".$outPath."'";
        //var_dump($cmd." ".$arg);
        exec($cmd." ".$arg, $output, $status);
        return self::return($status, $output, $outPath);
    }

    /**
     * @param string $path
     * @param string $ext
     * @return array
     */
    public static function changeImageFormat(string $path, string $ext): array
    {
        $outPath = substr($path, 0, strpos($path, '.')).".".$ext;
        $cmd = "convert";
        $arg = "'".$path."' -resize 500x500 '".$outPath."'";
        exec($cmd." ".$arg, $output, $status);
        return self::return($status, $output, $outPath);
    }

    /**
     * @param int    $status
     * @param array  $output
     * @param string $outPath
     * @return array
     */
    private static function return(int $status, array $output, string $outPath): array
    {
        return ($status == 0) ? ['success' => true, 'message' => $outPath] : ['success' => true, 'message' => $output];
    }
}
