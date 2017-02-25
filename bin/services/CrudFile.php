<?php
namespace bin\services;

use bin\models\mysql\Mysql;
use bin\models\Node;
use bin\services\Upload;


/**
* @pattern Singleton
*/
final class CrudFile {
    /**
    * @var Object CreateFile()
    *
    */
    private static $_instance;

    /**
    * @var instance of Node
    */
    private static $_node;

    private static function _getInstance ()
    : void
    {
        if(is_null(self::$_instance)) {
            self::$_instance = new self;
        }
    }

    private function __construct ()
    {
        self::$_node = new Node();
    }

    /**
    * @param $params, create a txt file with the specified parameter
    * @return default success tab
    * @see Upload & Node
    */
    public static function createFile(array $params)
    : array
    {
        return Upload::checkFile($params['file'], $params['name'])->moveFile($params['parent'], $params['langage']);
    }

    public static function deleteFile(int $nodeId)
    : self
    {
        //
    }

    public static function updateFile(array $params)
    : self
    {
        //
    }

    public static function getFile(int $nodeId)
    : array
    {
        return self::$_node->getNode($nodeId);
    }
}
