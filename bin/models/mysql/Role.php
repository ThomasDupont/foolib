<?php

 namespace bin\models\mysql;

 class Role {

     /**
     * @var write Role
     */
     private static $_write = false;

     private function _construct ()
     {
     }

     /**
     * @var Array $roles
     * @return Boolean
     */
     public static function checkRoles (array $roles)
     : bool
     {
         $implode = "";
         foreach ($roles as $type => $role) {
             switch ($type) {
               case 0: //admin
                 if($role) {
                     self::$_write = true;
                 }
                 break;
               case 1: //Owner
                 if($role) {
                     self::$_write = true;
                 }
                 break;
               case 2: //Read
                 if($role) {
                     self::$_write = false;
                 }
                 break;
               case 3: //Write
                 if($role) {
                     self::$_write = true;
                 }
                 break;
             }
             $implode = $implode.$role;
         }
         return !($implode == '0000') ;
     }

 }
