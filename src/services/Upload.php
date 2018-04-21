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
    * @param $file (base64 file)
    * @param $filename
    */
    public static function checkFile(string $file, string $filename, string $type): self
    {
        self::$fileInfo = $file = self::_createTmpFile($file, $filename, $type);

        if (($length = $file['size']) > MAX_FILE_SIZE) {
            self::$checkFile = ['success' => false, 'message' => "The size of the file is too big $length for ".MAX_FILE_SIZE." authorized"];
        } elseif (
            !in_array($file['ext'], self::$_fileTypes)
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
