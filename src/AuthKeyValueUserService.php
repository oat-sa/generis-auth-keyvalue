<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

/**
 * Authentication service to access db
 *
 * @author christophe massin
 * @package authKeyValue

 */

namespace oat\authKeyValue;
use common_persistence_AdvKeyValuePersistence;


class AuthKeyValueUserService {


    const PREFIXES_KEY = 'auth';

    const USER_PARAMETERS = 'parameters';

    public function __construct(){
        $this->persistence = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);
    }
    
    /**
     * @return common_persistence_AdvKeyValuePersistence
     */
    protected function getPersistence()
    {
       return $this->persistence; 
    }


    /**
     * @param $login
     * @return mixed
     */
    public function getUserData($login){
        return $this->getPersistence()->hGetAll(AuthKeyValueUserService::PREFIXES_KEY.':'.$login);
    }


    /**
     * @param $userLogin string
     * @param $parameter string
     * @return mixed
     */
    public function getUserParameter($userLogin, $parameter){
        return $this->getPersistence()->get(AuthKeyValueUserService::PREFIXES_KEY.':'.$userLogin.':'.$parameter);
    }

    /**
     * @param $userLogin string user login
     * @param $parameter string parameter
     * @param $value mixed
     */
    public function addUserParameter($userLogin, $parameter, $value){
        $this->getPersistence()->set(AuthKeyValueUserService::PREFIXES_KEY.':'.$userLogin.':'.$parameter, $value);
    }


    /**
     * @param $userLogin string
     * @param $parameter string
     */
    public function deleteUserParameter($userLogin, $parameter){
        $this->getPersistence()->del(AuthKeyValueUserService::PREFIXES_KEY.':'.$userLogin.':'.$parameter);
    }


    /**
     * @param $userLogin
     * @param $parameter
     * @param $value
     */
    public function editUserParameter($userLogin, $parameter, $value){
        $this->getPersistence()->set(AuthKeyValueUserService::PREFIXES_KEY.':'.$userLogin.':'.$parameter, $value);
    }
} 