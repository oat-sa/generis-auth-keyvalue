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
 */

/**
 * Authentication service to access db
 *
 * @author christophe massin
 * @package authKeyValue

 */

namespace oat\authKeyValue;
use common_persistence_AdvKeyValuePersistence;
use oat\generis\model\GenerisRdf;


class AuthKeyValueUserService
{

    const PREFIXES_KEY = 'auth';
    const USER_PARAMETERS = 'parameters';
    const USER_EXTRA_PARAMETERS = 'extra_parameters';

    private $persistence;

    public function __construct($id = AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID)
    {
        $this->persistence = common_persistence_AdvKeyValuePersistence::getPersistence($id);
    }

    /**
     * @return common_persistence_AdvKeyValuePersistence
     */
    protected function getPersistence()
    {
       return $this->persistence;
    }

    /**
     * @param string $login
     * @param string $password
     * @param array $data
     * @param array $extraParams
     * @throws \common_Exception
     */
    public function storeUserData($login, $password, array $data, array $extraParams = [])
    {
        if (empty($login) || empty($password)) {
            return;
        }

        $this->getPersistence()->hSet($this->getStorageKey($login), GenerisRdf::PROPERTY_USER_PASSWORD, $password);
        $this->getPersistence()->hSet($this->getStorageKey($login), self::USER_PARAMETERS, json_encode($data) );

        foreach ($extraParams as $property => $value) {
            $this->getPersistence()->hSet(
                $this->getParameterStorageKey($login),
                $property,
                $value
            );
        }
    }

    /**
     * @param $login
     * @return mixed
     */
    public function getUserData($login){
        return $this->getPersistence()->hGetAll($this->getStorageKey($login));
    }

    /**
     * @param string $login
     */
    public function removeUserData($login)
    {
        if (empty($login)) {
            return;
        }
        $this->getPersistence()->del($this->getStorageKey($login));
        $this->getPersistence()->del($this->getParameterStorageKey($login));
    }

    /**
     * @param $userLogin string
     * @param $parameter string
     * @return mixed
     */
    public function getUserParameter($userLogin, $parameter){
        return $this->getPersistence()->hGet($this->getParameterStorageKey($userLogin), $parameter);
    }

    /**
     * @param $userLogin string user login
     * @param $parameter string parameter
     * @param $value mixed
     */
    public function setUserParameter($userLogin, $parameter, $value){
        $this->getPersistence()->hSet($this->getParameterStorageKey($userLogin), $parameter, $value);
    }

    /**
     * @param $login
     * @return string
     */
    private function getStorageKey($login)
    {
        return self::PREFIXES_KEY . ':' . $login;
    }

    private function getParameterStorageKey($login)
    {
        return $this->getStorageKey($login) . ':' . self::USER_EXTRA_PARAMETERS;
    }
}
