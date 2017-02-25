<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   AjaxController.php Controller for all Ajax request
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace bin\controllers;

use bin\models\{Cart, Order, Node, User};
use bin\services\Upload;
use bin\services\CrudFile;

/**
* @pattern Command, VMC
*/
final class AjaxController extends Controller implements APIInterface {

    public function __construct()
    {
        parent::__construct();
    }

    /**
    * MUST be implemented
    *
    */
    public function execute ()
    : string
    {
        $funct = "_".strtoupper($this->request->action);
        $functWhiteList = [
          '_SENDCONTACT',
          '_LOGIN',
          '_GETCART',
          '_GETHOME',
          '_UPLOAD',
          '_CHECKUSER',
          '_REGISTER',
          '_DISCONNECT',
          '_DELETENODE',
          '_CREATEFOLDER',
          '_CREATEFILE'
        ];
        return in_array($funct, $functWhiteList) ? $this->$funct($this->request) : json_encode(['success' => false]);
    }

    private function _SENDCONTACT (\stdClass $request)
    : string
    {
        return $request->text;
    }

    private function _LOGIN (\stdClass $request)
    : string
    {
        $user = new User();

        return json_encode($user->login((string) $request->login, (string) $request->password));
    }

    private function _GETCART (\stdClass $request)
    : string
    {
        $cart = new Cart($this->mysql);
        $myCart = $cart->getCart();
        //.....
    }
    private function _GETHOME ()
    : string
    {
        $node = new Node();
        return json_encode($node->getNodes());
    }

    private function _UPLOAD (\stdClass $request)
    : string
    {
        return json_encode(
            Upload::checkFile($request->file, $request->filename)->moveFile($request->pNodeId)
        );
    }

    private function _CREATEFILE (\stdClass $request)
    : string
    {
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

    private function _CREATEFOLDER (\stdClass $request)
    : string
    {
        $node = new Node();
        return json_encode($node->setNode($request->nodeId, $request->name, true));
    }

    private function _DELETENODE (\stdClass $request)
    : string
    {
        $node = new Node();
        return json_encode($node->unsetNode($request->nodeId));
    }

    private function _CHECKUSER ()
    : string
    {
        $user = new User();
        return json_encode($user->checkUser());
    }

    private function _REGISTER (\stdClass $request)
    : string
    {
        $user = new User();
        $createUser = $user->register((string) $request->login, (string) $request->password);
        return $createUser['success'] ? json_encode($createUser) : "Cet utilisateur éxiste déjà";
    }

    private function _DISCONNECT ()
    : string
    {
        $user = new User();
        return json_encode($user->disconnect());
    }
}
