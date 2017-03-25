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
        $body = [];
        $userId = SessionManager::getSession()['id'];
        $body['id'] = md5(uniqid().$userId);
        $body['userId'] = $userId;
        $body['name'] = $params['name'];
        $body['codes'] = [];
        for($i = $params['iteration']; $i>=0; $i--) {
            $body['codes'][] = [
                'content' => base64_decode($params['file'][$i]),
                'langage' => $params['langage'][$i],
                'time' => time()
            ];
        }
        $insert = [[
            'action' => 'insert', 'body' => $body
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

    public static function updateFile(array $codes, string $id, string $name)
    : array
    {
        //$result = Mongo::getInstance()->createQuery($filter, $options)->execute("save");
        //$element->updateTime = time();
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
        return ['success' => false, 'message' => "Impossible de récupérer les codes"];

    }

    public static function getUniqFile(int $nodeId)
    : array
    {
        return self::$_node->getNode($nodeId);
    }
}
