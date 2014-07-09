<?php
/**
 * Created by PhpStorm.
 * User: christophemassin
 * Date: 7/07/14
 * Time: 14:13
 */

namespace oat\authKeyValue\model;
use common_persistence_AdvKeyValuePersistence;


class AuthKeyValueUserService {


    protected $driver;


    public function __construct(){
        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);
        $this->driver = $kvStore->getDriver();
    }

    public function getUserParameter($userLogin, $userParameter){
        return $this->driver->hGet($userLogin, $userParameter);
    }


    public function addUser($userLogin,array $arrayParameter){

    }


    public function addUserParameter($userLogin, $userParameter, $value){
        $this->driver->hSet($userLogin, $userParameter, $value);
    }


    public function deleteUser($userLogin){
        $this->driver->del($userLogin);
    }


    public function deleteUserParameter($userLogin, $userParameter){
        $this->driver->hDel($userLogin, $userParameter);
    }


    public function editUser($userLogin, array $arrayParameter){

    }


    public function editUserParameter($userLogin, $userParameter, $value){
        $this->driver->hSet($userLogin,$userParameter,$value);
    }
} 