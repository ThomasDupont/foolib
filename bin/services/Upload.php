<?php

namespace bin\services;

use bin\models\mysql\{Mysql, SessionManager};
use bin\models\Node;
use bin\services\Imagick;


/**
* @pattern Singleton
*/
final class Upload {

    /**
    * @var Object Upload()
    *
    */
    private static $_instance;

    /**
    * @var Array $_checkFile
    *
    */
    private static $_checkFile;

    /**
    * @var Array $_fileInfo
    *
    */
    private static $_fileInfo;

    /**
    * @var Object Node()
    *
    */
    private static $_node;

    private static $_fileTypes = [];

    private static function _getInstance ()
    : void
    {
        if(is_null(self::$_instance)) {
            self::$_instance = new self;
        }
    }

    private function __construct ()
    {
        self::$_fileTypes = explode(',', FILE_TYPES);
        self::$_node = new Node();
    }

    private static function _createTmpFile (string $file, string $filename, string $type)
    : array
    {
        self::_getInstance();

        $contentFile = substr($file, strpos($file, "base64,")+7);
        $tmpName = md5(uniqid()).".".substr(strrchr($filename, '.'), 1);
        $path = FILETMPDIR.$tmpName;
        file_put_contents($path, base64_decode($contentFile));

        Imagick::changeImageFormat($path, 'png');

        $ext = pathinfo($path)['extension'];
        if(in_array($ext, self::$_fileTypes) && $type == 'profil'){

            $result = Imagick::createCropThumbernail($path);

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
    public static function checkFile (string $file, string $filename, string $type)
    : self
    {

        self::$_fileInfo = $file = self::_createTmpFile($file, $filename, $type);

        if(($length = $file['size']) > MAX_FILE_SIZE) {
            self::$_checkFile = ['success' => false, 'message' => "La taille du fichier est trop grande $length pour ".MAX_FILE_SIZE." autorisé"];
        } else if(
            !in_array($file['ext'], self::$_fileTypes)
            && !preg_match("/(".implode(')|(',self::$_fileTypes).")/", mime_content_type($file['tmp_path']))
          ) {
            self::$_checkFile = ['success' => false, 'message' => "Type de fichier ".mime_content_type($file['tmp_path'])." non autorisé"];
        }

        self::$_checkFile = ['success' => true];
        return self::$_instance;
    }

    /**
    * @param $parentNodeId
    * @param $langage, the langage (code) of the node
    */
    public static function moveFile (int $parentNodeId, string $langage = "")
    : array
    {
        if(self::$_checkFile['success']) {
            if(($token = SessionManager::getSession()['APITOKEN']) != "") {
                $newNode = self::$_node->setNode($parentNodeId, self::$_fileInfo['tmp_name'], false);
                if(!$newNode['success']) {
                    return $newNode;
                }
                $tmpFile = self::$_fileInfo['tmp_path'];
                rename($tmpFile, USERDIR.$newNode['result']['path']);
                if(is_file($tmpFile)) {
                    unlink($tmpFile);
                }
                return $newNode;
            }
        }
        return self::$_checkFile;
    }
}
