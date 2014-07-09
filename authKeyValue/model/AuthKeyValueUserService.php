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


    const PREFIXES_KEY = 'auth';

    const USER_PARAMETERS = 'parameters';

    /**
     * @var \common_persistence_Driver
     */
    protected $driver;


    public function __construct(){
        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);
        $this->driver = $kvStore->getDriver();
    }


    /**
     * @param $login
     * @return mixed
     */
    public function getUserData($login){
        return $this->driver->hGetAll(AuthKeyValueUserService::PREFIXES_KEY.':'.$login);
    }


    /**
     * @param $userLogin string
     * @param $parameter string
     * @return mixed
     */
    public function getUserParameter($userLogin, $parameter){
        return $this->driver->get(AuthKeyValueUserService::PREFIXES_KEY.':'.$userLogin.':'.$parameter);
    }

    /**
     * @param $userLogin string user login
     * @param $parameter string parameter
     * @param $value mixed
     */
    public function addUserParameter($userLogin, $parameter, $value){
        $this->driver->set(AuthKeyValueUserService::PREFIXES_KEY.':'.$userLogin.':'.$parameter, $value);
    }


    /**
     * @param $userLogin string
     * @param $parameter string
     */
    public function deleteUserParameter($userLogin, $parameter){
        $this->driver->del(AuthKeyValueUserService::PREFIXES_KEY.':'.$userLogin.':'.$parameter);
    }


    /**
     * @param $userLogin
     * @param $parameter
     * @param $value
     */
    public function editUserParameter($userLogin, $parameter, $value){
        $this->driver->set(AuthKeyValueUserService::PREFIXES_KEY.':'.$userLogin.':'.$parameter, $value);
    }
} 