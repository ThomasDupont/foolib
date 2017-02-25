<?php
namespace models;

use models\mysql\Mysql;

class Cart {
    private $mysql;

    public function __construct(Mysql $mysql)
    {
        $this->mysql = $mysql;
    }

    public function getCart()
    {
      $dataSet = $this->mysql->getDBDatas("
        SELECT * FROM cart WHERE user = '".$this->mysql->getSession()['API_key']."'
      ");
    }

    public function setCart($nodes)
    {

    }
}
