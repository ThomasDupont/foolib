<?php

namespace src\controllers;

use src\models\Cart;
use src\models\Order;
use src\models\Node;
use src\services\CrudFile;
use src\log\Log;

/**
 * Class CodeController
 * @package src\controllers
 */
final class CodeController extends Controller implements APIInterface
{
    public function execute(): string
    {
        $funct = $this->request->action;
        return $this->$funct($this->request);
    }

    private function createfile(): string
    {
        if (!is_array($this->request->langage)) {
            return json_encode(
                ['success' => false, 'message' => "the langage parameter is not an array"]
            );
        }
        $params = [
            //'iteration' => (int) $this->request->iteration,
            'name'    => $this->request->filename,
            'file'    => $this->request->file,
            'langage' => $this->request->langage,
            'description' => $this->request->description
        ];

        return json_encode(
            CrudFile::createFile($params)
        );
    }

    private function getcodes(): string
    {
        return json_encode(CrudFile::getFiles());
    }

    private function supprcode(): string
    {
        return json_encode(
            CrudFile::deleteFile($this->request->id)
        );
    }

    private function supprscreen(): string
    {
        $mId = $this->request->id;
        $result = CrudFile::supprScreen($this->request->files, $mId);
        $oNode = $this->request->oldNodeId;
        if ($result['success']) {
            $node = new Node();
            if (!$node->unsetNode($oNode)['success']) {
                Log::error("Error delete node {old}", ['old' => $oNode]);
            }
        } else {
            Log::user("Error delete node {old}, on Mongo {mongo}", ['old' => $oNode, 'mongo' => $mId]);
        }
        return json_encode($result);
    }
    private function updatecode(): string
    {
        if (
            !isset($this->request->codes) ||
            !isset($this->request->id) ||
            !isset($this->request->name)
        ) {
            return json_encode(['success' => false, 'message' => "One or other fields are missing"]);
        }
        return json_encode(
            CrudFile::updateFile(
                $this->request->codes,
                $this->request->id,
                $this->request->name
            )
        );
    }
}
