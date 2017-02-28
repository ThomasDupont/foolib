<?php

namespace bin\controllers;

use bin\models\{Cart, Order, Node, User};
use bin\services\Upload;
use bin\services\CrudFile;

final class CodeController extends Controller implements APIInterface {

    /**
    * MUST be implemented
    *
    */
    public function execute ()
    : string
    {
        $funct = "_".strtoupper($this->request->action);
        $functWhiteList = [
            '_CREATEFILE',
            '_GETCODES',
            '_SUPPRCODE'
        ];
        return in_array($funct, $functWhiteList) ?
            $this->$funct($this->request) :
            json_encode(['success' => false, 'message' => "not authorized $funct"]);
    }

    private function _CREATEFILE (\stdClass $request)
    : string
    {
        if(!is_array($request->langage)) {
            return json_encode(
                ['success' => false, 'message' => "the langage parameter is not an array"]
            );
        }
        $params = [
            'name'    => $request->filename,
            'file'    => $request->file,
            'parent'  => $request->pNodeId,
            'langage' => $request->langage
        ];
        return json_encode(
            CrudFile::createFile($params)
        );
    }

    private function _GETCODES (\stdClass $request)
    {

        return json_encode(CrudFile::getFiles());
    }

    private function _SUPPRCODE (\stdClass $request)
    {
        return json_encode(
            CrudFile::deleteFile($request->id)
        );
    }
}
