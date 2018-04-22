<?php

 namespace src\models\mongo;

 /**
  * Class Mongo
  * @package src\models\mongo
  */
 final class Mongo
 {
     private static $instance;
     private static $mongo;
     private static $bulk;
     private static $type;
     private static $query;
     private static $result;
     private static $insertIds = [];
     private static $db;

     private function __construct()
     {
         $parseUri = explode('/', MONGODBURI);
         static::$db = array_pop($parseUri);
         self::$mongo = new \MongoDB\Driver\Manager(MONGODBURI);
         self::$bulk = new \MongoDB\Driver\BulkWrite();
     }

     public static function getInstance()
     : self
     {
         if (is_null(self::$instance)) {
             self::$instance = new self;
         }

         return self::$instance;
     }
     /**
      * create a new bulk for a new opération
      * @return self
      */
     public function setNewBulk()
     : self
     {
         self::$bulk = new \MongoDB\Driver\BulkWrite();

         return self::$instance;
     }

     /**
      * Add by loop the data inside the bulk
      * @param  array $params list of data
      * @return self
      */
     public static function addToBulk(array $params): self
     {
         foreach ($params as $value) {
             switch ($value['action']) {
                case 'insert':
                    self::$bulk->insert($value['body']);
                    self::$insertIds[] = $value['body']['id'] ?? md5(uniqid());
                    break;
                case 'update':
                    self::$bulk->update($value['body'][0], $value['body'][1]);
                    break;
                case 'delete':
                    self::$bulk->delete($value['body']);
                    break;
             }
         }

         self::$type = "bulk";

         return self::$instance;
     }

     /**
      * create a MongoDb query
      * @param  array  $filter  the query parameter
      * @param  array $options the options
      * @see MongoDB driver query php documentation
      * @return self
      */
     public static function createQuery(array $filter, array $options = []): self
     {
         self::$query = new \MongoDB\Driver\Query($filter, $options);
         self::$type = "query";

         return self::$instance;
     }

     /**
      * Execute the operation inside Mongo
      * @param  string $collection collection name
      * @return array  success with result or not
      */
     public static function execute(string $collection): array
     {
         $insertIds = self::$insertIds;
         self::$insertIds = [];
         switch (self::$type) {
            case "bulk":
                try {
                    self::$result = self::$mongo->executeBulkWrite(static::$db.'.'.$collection, self::$bulk);
                } catch (\MongoDB\Driver\Exception\BulkWriteException $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                } catch (\MongoDB\Driver\Exception\RuntimeException $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }

                return ['success' => true, 'result' => $insertIds];
                break;
            case "query":
                return self::$mongo->executeQuery(static::$db.'.'.$collection, self::$query)->toArray();
                break;
         }

         return ['success' => false, 'message' => "No type of query setting"];
     }
 }
