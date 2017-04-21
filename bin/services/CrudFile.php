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
    * @param $params, create a snippet with the specified parameter
    * @return default success tab
    * @see Upload & Node
    */
    public static function createFile(array $params)
    : array
    {
        if(!isset(SessionManager::getSession()['id'])) {
            return ['success' => false, 'message' => "You are not login"];
        }
        $userId = SessionManager::getSession()['id'];
        $dataSet = Mysql::getInstance()->getDBDatas(
            "SELECT valid FROM users WHERE id = ?",
            [$userId]
        )->toObject();


        if(!$dataSet['result']->valid) {
            return ['success' => false, 'message' => "Your email has not been activated"];
        }
        if(
            !is_string($params['description']) ||
            !is_string($params['name']) ||
            !is_array($params['langage']) ||
            !is_array($params['content'])
        ) {
            return ['success' => false, 'message' => "The request is wrong"];
        }
        //* Mongo code
        $body = [];

        $body['id'] = md5(uniqid().$userId);
        $body['userId'] = $userId;
        $body['name'] = $params['name'];
        $body['description'] = $params['description'];
        $body['codes'] = [];

        if(($len = count($params['langage'])) != count($params['file'])) {
            return ['success' => false, 'message' => "The langage length doesn't match with the content length"];
        }

        for($i = 0; $i < $len; $i++) {
            $body['codes'][] = [
                'content' => utf8_encode(base64_decode($params['file'][$i])),
                'langage' => utf8_encode($params['langage'][$i]),
                'time' => time()
            ];
        }
        $insert = [[
            'action' => 'insert', 'body' => $body
        ]];

        return Mongo::getInstance()->addToBulk($insert)->execute('save');
    }

    public static function deleteFile(string $elId)
    : array
    {
        if(!isset(SessionManager::getSession()['id'])) {
            return ['success' => false, 'message' => "You are not login"];
        }
        $delete = [[
            'action' => 'delete', 'body' => [
                'userId' => SessionManager::getSession()['id'],
                'id' => $elId
            ]
        ]];
        return Mongo::getInstance()->addToBulk($delete)->execute('save');
    }

    public static function updateFile(array $codes, string $id, string $name)
    : array
    {
        if(!isset(SessionManager::getSession()['id'])) {
            return ['success' => false, 'message' => "You are not login"];
        }
        foreach($codes as &$code) {
            $code->content = utf8_encode($code->content);
            $code->langage = utf8_encode($code->langage);
        }
        $update = [[
            'action' => 'update', 'body' => [
                ['id' => $id, 'userId' => SessionManager::getSession()['id']],
                ['$set' => ['name' => $name, 'codes' => $codes]]
            ]
        ]];
        return Mongo::getInstance()->addToBulk($update)->execute('save');
    }

    /**
    * Add Screenshot to a Snippet
    */
    public static function updateDocumentWithScreenshot (\stdClass $params)
    : array
    {
        $update = [[
            'action' => 'update', 'body' => [
                ['id' => $params->mongoId, 'userId' => SessionManager::getSession()['id']],
                [$params->type => ['file' => ['path' => $params->path, 'id' => $params->fileId]]]
            ]
        ]];
        return Mongo::getInstance()->addToBulk($update)->execute('save');
    }

    /**
    * Update a screenShot inside a snippet
    */
    public static function updateScreenshot(\stdClass $params)
    : array
    {
        $update = [[
            'action' => 'update', 'body' => [
                ['id' => $params->mongoId, 'userId' => SessionManager::getSession()['id']],
                [
                    '$set' => ['file.'.$params->position => ['path' => $params->path, 'id' => $params->fileId]]
                ]
            ]
        ]];
        return Mongo::getInstance()->addToBulk($update)->execute('save');
    }

    /**
    * Get all codes and file for the current user
    */
    public static function getFiles()
    : array
    {
        $filter = [
            'userId' => SessionManager::getSession()['id'] ?? 0
        ];
        //Tri par ordre décroissant
        $options = [
            'sort' => ['time' => -1]
        ];
        $result = Mongo::getInstance()->createQuery($filter, $options)->execute("save");
        $node = new Node();
        $nodes = $node->getNodes();
        if($nodes['success']) {
            return ['success' => true, 'codes' => $result, 'nodes' => $nodes['result']];
        }
        return ['success' => false, 'message' => "Impossible to get the codes"];

    }

    public static function supprScreen(array $newFiles, string $mongoId)
    : array
    {
        $update = [[
            'action' => 'update', 'body' => [
                ['id' => $mongoId, 'userId' => SessionManager::getSession()['id']],
                [
                    '$set' => ['file' => $newFiles]
                ]
            ]
        ]];
        return Mongo::getInstance()->addToBulk($update)->execute('save');
    }

    public static function getUniqFile(int $nodeId)
    : array
    {
        return self::$_node->getNode($nodeId);
    }
}
