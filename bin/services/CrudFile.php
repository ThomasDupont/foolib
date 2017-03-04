<?php
namespace bin\services;

use bin\models\mysql\{Mysql, SessionManager};
use bin\models\Node;
use bin\models\mongo\Mongo;
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
        //* Mongo code
            $userId = SessionManager::getSession()['id'];
            $insert = [[
                'action' => 'insert', 'body' => [
                    'userId' => $userId,
                    'content' => base64_decode($params['file']),
                    'langage' => $params['langage'],
                    'name' => $params['name'],
                    'time' => time(),
                    'id' => md5(uniqid().$userId)
                ]
            ]];
            return Mongo::getInstance()->addToBulk($insert)->execute('save');

        /*/
        return Upload::checkFile(
            $params['file'],str_replace(" ","_",$params['name']))
            ->moveFile($params['parent'], $params['langage']);
        //*/
    }

    public static function deleteFile(string $elId)
    : array
    {
        $delete = [[
            'action' => 'delete', 'body' => [
                'userId' => SessionManager::getSession()['id'],
                'id' => $elId
            ]
        ]];
        return Mongo::getInstance()->addToBulk($delete)->execute('save');
    }

    public static function updateFile(\stdClass $element)
    : array
    {
        unset($element->_id);
        $element->updateTime = time();
        $update = [[
            'action' => 'update', 'body' => [
                ['id' => $element->id ], ['$set' => $element]
            ]
        ]];
        return Mongo::getInstance()->addToBulk($update)->execute('save');
    }

    public static function getFiles()
    : array
    {
        $filter = [
            'userId' => SessionManager::getSession()['id']
        ];
        $options = [
            'sort' => ['time' => -1]
        ];
        $result = Mongo::getInstance()->createQuery($filter, $options)->execute("save");
        $node = new Node();
        $nodes = $node->getNodes();
        if($nodes['success']) {
            return ['success' => true, 'codes' => $result, 'nodes' => $nodes['result']];
        }
        return ['success' => false, 'message' => "Impossible de récupérer les codes"];

    }

    public static function getFile(int $nodeId)
    : array
    {
        return self::$_node->getNode($nodeId);
    }
}
