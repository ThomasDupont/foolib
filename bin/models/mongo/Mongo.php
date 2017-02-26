<?php

 namespace bin\models\mongo;

 final class Mongo {

     private static $_instance;
     private static $_mongo;
     private static $_bulk;
     private static $_type;
     private static $_result;

     private function __construct()
     {
        self::$_mongo = new \MongoDB\Driver\Manager("mongodb://".MONGOIP.":".MONGOPORT);
        self::$_bulk = new \MongoDB\Driver\BulkWrite();
     }

     public static function getInstance()
     : self
     {
         if(is_null(self::$_instance)) {
              self::$_instance = new self;
         }
         return self::$_instance;
     }

     public static function addToBulk(array $params)
     : self
     {
         foreach($params as $value) {
             switch ($value['action']) {
                case 'insert':
                    self::$_bulk->insert($value['body']);
                    break;
                case 'update':
                    self::$_bulk->update($value['body']);
                    break;
                case 'delete':
                    self::$_bulk->delete($value['body']);
                    break;
             }

         }
         self::$_type = "bulk";
         return self::$_instance;
     }

     public static function execute (string $collection)
     : array
     {
         switch(self::$_type) {
            case "bulk":
                self::$_result = self::$_mongo->executeBulkWrite(MONGODATABASE.'.'.$collection, self::$_bulk);
                break;
            default:
                return ['success' => false, 'message' => "No type of query setting"];
         }

         return ['success' => true];

     }

 }
