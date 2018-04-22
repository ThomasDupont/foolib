<?php

namespace src\services;

use src\models\mysql\Mysql;
use src\models\mysql\SessionManager;
use src\models\Node;
use src\services\Imagick;

/**
* @pattern Singleton
*/
final class Upload
{

    /**
    * @var Object Upload()
    */
    private static $instance;

    /**
    * @var Array $checkFile
    */
    private static $checkFile;

    /**
    * @var Array $fileInfo
    */
    private static $fileInfo;

    /**
    * @var Object Node()
    */
    private static $node;

    /**
     * @var array
     */
    private static $fileTypes = [];

    private static function getInstance(): void
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
    }

    private function __construct()
    {
        self::$fileTypes = explode(',', FILE_TYPES);
        self::$node = new Node();
    }

    /**
     * @param string $file
     * @param string $filename
     * @param string $type
     * @return array
     */
    private static function createTmpFile (string $file, string $filename, string $type): array
    {
        self::getInstance();
        $contentFile = substr($file, strpos($file, "base64,")+7);
        $tmpName = md5(uniqid()).".".substr(strrchr($filename, '.'), 1);
        $path = FILETMPDIR.$tmpName;
        file_put_contents($path, base64_decode($contentFile));
        Imagick::changeImageFormat($path, 'png');
        $ext = pathinfo($path)['extension'];
        if(in_array($ext, self::$fileTypes) && $type == 'profil'){
            Imagick::createCropThumbernail($path);
        }
        return [
            'ext' => $ext,
            'tmp_path' => FILETMPDIR.$tmpName,
            'name' => $filename,
            'size' => filesize(FILETMPDIR.$tmpName),
            'tmp_name' => $tmpName
        ];
    }

    /**
    * @param $file (base64 file)
    * @param $filename
    */
    public static function checkFile(string $file, string $filename, string $type): self
    {
        self::$fileInfo = $file = self::createTmpFile($file, $filename, $type);

        if (($length = $file['size']) > MAX_FILE_SIZE) {
            self::$checkFile = ['success' => false, 'message' => "The size of the file is too big $length for ".MAX_FILE_SIZE." authorized"];
        } elseif (
            !in_array($file['ext'], self::$fileTypes)
            && !preg_match("/(".implode(')|(', self::$fileTypes).")/", mime_content_type($file['tmp_path']))
          ) {
            self::$checkFile = ['success' => false, 'message' => "file type ".mime_content_type($file['tmp_path'])." not authorized"];
        }

        self::$checkFile = ['success' => true];

        return self::$instance;
    }

    /**
    * @param $parentNodeId
    * @param $langage, the langage (code) of the node
    */
    public static function moveFile(int $parentNodeId): array
    {
        if (self::$checkFile['success']) {
            if (($token = SessionManager::getSession()['APITOKEN']) != "") {
                $newNode = self::$node->setNode($parentNodeId, self::$fileInfo['tmp_name'], false);
                if (!$newNode['success']) {
                    return $newNode;
                }
                $tmpFile = self::$fileInfo['tmp_path'];
                rename($tmpFile, USERDIR.$newNode['result']['path']);
                if (is_file($tmpFile)) {
                    unlink($tmpFile);
                }
                return $newNode;
            }
        }

        return self::$checkFile;
    }
}
