<?php

 namespace bin\models\mongo;

 final class Mongo {

     private static $_instance;
     private static $_mongo;
     private static $_bulk;
     private static $_type;
     private static $_query;
     private static $_result;
     private static $_insertIds = [];

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
                    self::$_insertIds[] = $value['body']['id'];
                    break;
                case 'update':
                    self::$_bulk->update($value['body'][0], $value['body'][1]);
                    break;
                case 'delete':
                    self::$_bulk->delete($value['body']);
                    break;
             }

         }

         self::$_type = "bulk";
         return self::$_instance;
     }

     public static function createQuery(array $filter, array $options = [])
     : self
     {
         self::$_query = new \MongoDB\Driver\Query($filter, $options);
         self::$_type = "query";
         return self::$_instance;
     }

     public static function execute (string $collection)
     : array
     {
         $insertIds = self::$_insertIds;
         self::$_insertIds = [];
         switch(self::$_type) {
            case "bulk":
                self::$_result = self::$_mongo->executeBulkWrite(MONGODATABASE.'.'.$collection, self::$_bulk);
                return ['success' => true, 'result' => $insertIds];
                break;
            case "query":
                return self::$_mongo->executeQuery(MONGODATABASE.'.'.$collection, self::$_query)->toArray();
                break;
            default:
                return ['success' => false, 'message' => "No type of query setting"];
         }



     }

 }
