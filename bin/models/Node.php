<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   Node.php Node (files + folders) model
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace bin\models;

use bin\models\mysql\{Mysql, SessionManager, Role};

class Node {

    /**
    * @var Object Mysqli connect
    *
    */
    private $_mysql;

    /**
    * @var array list of forbidden chars used to create file or folder
    *
    */
    private $_forbidenChars = [
        '%','$','≠','∞','~','ß','◊','©','≈','‹','≤','≥','µ','¬','ﬁ','ƒ','∂','‡','®','†','º','π','§','¶','','•','#','°','.', '/', '\\'
    ];

    public function __construct ()
    {
        $this->_mysql = Mysql::getInstance();
    }

    public function getNodes ()
    : array
    {
        $dataSet = $this->_mysql->getDBDatas("
          SELECT node_ID, parentNode_ID, path, record_name, authUsers, lastModif FROM nodes
        ")->toArrayAssoc();
        if($dataSet['success']) {
            if(Role::checkRoles((array) str_split($dataSet['session']["roles"]))) {
                $id = $dataSet['session']["id"];
                $arrayReturn = [];
                foreach($dataSet['result'] as $key => $value) {
                    $authUsers = explode("|", $value['authUsers']);
                    if(in_array($id, $authUsers)) {
                        unset($value['authUsers']);
                        $arrayReturn[] = $value;
                    }
                }
                return ['success' => true, 'result' => $arrayReturn];
            } else {
                return ['success' => false, 'message' => 'You have not the permission'];
            }
        }
        return ['success' => false, 'message' => 'You have not the permission'];
    }

    /**
    * @param $nodeId
    *
    */
    public function getNode (int $nodeId)
    : array
    {
        $dataSet = $this->_mysql->getDBDatas("
          SELECT node_ID, parentNode_ID, path, record_name, authUsers, lastModif FROM nodes WHERE node_ID = ?
        ", [$nodeId])->toObject();

        if($dataSet['success']) {
            if(Role::checkRoles((array) str_split($dataSet['session']["roles"]))) {

                unset($dataSet['session']);
                return ['success' => true, 'result' => $dataSet['result']];
                //Action sur les roles
            } else {
                return ['success' => false, 'message' => 'You have not the permission'];
            }
        }
        return ['success' => false, 'message' => 'You have not the permission'];
    }

    /**
    * @param $parentNodeId
    * @param $name
    * @param $isDirectory
    *
    */
    public function setNode (int $nodeId, string $name, string $langage = "", bool $isDir = false)
    : array
    {
        //check if the file or the folder still exist
        if($this->isNode($nodeId, $name)) {
            return ['success' => false, 'message' => "Ce noeud existe déja"];
        }
        //Delete the forbidden chars
        $this->_cleanNodeName($name);
        // Get tha info of the parent node
        $check = $this->getNode($nodeId);

        if($check['success']) {
            $nodePath = ($nodeId == 0) ? "/".$name : $check['result']->path.$name;
        } else {
            return $check;
        }
        if($isDir) {
            $paramArray = [$nodeId, $nodePath."/", $name, $langage, SessionManager::getSession()['id']."|"];
            $this->_createDir($nodePath);
        } else {
            $paramArray = [$nodeId, $nodePath, $name, $langage, SessionManager::getSession()['id']."|"];
        }

        $nodeId = $this->_mysql->setDBDatas(
            "nodes",
            "(parentNode_ID, path, record_name, langage, authUsers, lastModif) VALUE (?,?,?,?,?, NOW())",
            $paramArray
        );

        return $nodeId ? ['success' => true, 'result' => ['path' => $nodePath, 'nodeId' => $nodeId]]
            : ['success' => false, 'message' => "erreur à la création du node"];
    }

    public function isNode(int $nodeId, string $name)
    : bool
    {
        return $this->_mysql->getDBDatas(
                "SELECT node_ID FROM nodes WHERE parentNode_ID = ? AND record_name = ?",
                [$nodeId, $name]
            )->ToArray()['success'];
    }

    public function isNodePresent(string $path)
    : bool
    {
        // @TODO Check if the node is set in the file system
    }

    /**
    * @param $nodeId
    * This function check if the nodeID exist, if the user has the power of this nodeId and if the deletion works fine
    */
    public function unsetNode (int $nodeId)
    : array
    {
        $nodeInfo = $this->getNode($nodeId);

        if($nodeInfo['success']) {
            $userId = SessionManager::getSession()['id'];
            $authUsers = explode("|", $nodeInfo['result']->authUsers);
            if(in_array($userId, $authUsers)) {
                //var_dump($nodeId);
                if($this->_mysql->unsetDBDatas(
                        "nodes",
                        "node_ID = ? OR parentNode_ID = ?",
                        [$nodeId, $nodeId]
                    )
                ){
                    if(is_dir(USERDIR.$nodeInfo['result']->path)) {
                        $this->_rrmdir(USERDIR.$nodeInfo['result']->path);
                    } else {
                        unlink(USERDIR.$nodeInfo['result']->path);
                    }
                    return ['success' => true];
                }
                return ['success' => false];
            }
            return ['success' => false];
        }
        return ['success' => false];
    }

    private function _rrmdir(string $dir)
    : void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                     if (is_dir($dir."/".$object))
                       $this->_rrmdir($dir."/".$object);
                     else
                       unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }

    private function _createDir (string &$nodePath)
    : void
    {
        $nodePath .= "/";
        $oldmask = umask(0);
        mkdir(USERDIR.$nodePath, 0777);
        umask($oldmask);
    }

    private function _cleanNodeName (string &$name)
    : void
    {
        $ext = substr($name, strrpos($name, '.'));
        $name = substr($name, 0, strrpos($name, '.'));
        $name = str_replace($this->_forbidenChars, "", $name).$ext;
    }
}
