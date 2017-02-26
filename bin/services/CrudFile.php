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
        /* Mongo code

            $insert = [[
                'action' => 'insert', 'body' => [
                    'userId' => SessionManager::getSession()['id'],
                    'content' => base64_decode($params['file']),
                    'langage' => $params['langage'],
                    'name' => $params['name']
                ]
            ]];
            return bin\models\mongo\Mongo::getInstance()->addToBulk($insert)->execute('save');

        */
        return Upload::checkFile(
            $params['file'],str_replace(" ","_",$params['name']))
            ->moveFile($params['parent'], $params['langage']);
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
