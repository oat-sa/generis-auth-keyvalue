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

use common_Exception;
use common_persistence_Manager;
use common_persistence_Persistence;
use oat\generis\model\GenerisRdf;
use oat\oatbox\service\ConfigurableService;


class AuthKeyValueUserService extends ConfigurableService
{

    const SERVICE_ID = 'authKeyValue/UserService';

    const OPTION_PERSISTENCE = 'persistence';

    const PREFIXES_KEY = 'auth';
    const USER_PARAMETERS = 'parameters';
    const USER_EXTRA_PARAMETERS = 'extra_parameters';

    private $persistence;

    /**
     * @return common_persistence_Persistence
     */
    protected function getPersistence()
    {
        if (empty($this->persistence)) {
            $persistenceId = AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID;
            if ($this->hasOption(self::OPTION_PERSISTENCE)) {
                $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
            }
            $this->persistence = $this->getPersistenceManager()->getPersistenceById($persistenceId);
        }

        return $this->persistence;
    }

    /**
     * @param string $login
     * @param string $password
     * @param array $data
     * @param array $extraParams
     * @throws common_Exception
     */
    public function storeUserData($uri, $login, $password, array $data, array $extraParams = [])
    {
        if (empty($login) || empty($password)) {
            return;
        }

        $this->getPersistence()->set($this->getUriMapKey($uri), $login);
        $this->getPersistence()->hSet($this->getStorageKey($login), GenerisRdf::PROPERTY_USER_PASSWORD, $password);
        $this->getPersistence()->hSet($this->getStorageKey($login), self::USER_PARAMETERS, json_encode($data) );

        foreach ($extraParams as $property => $value) {
            $this->getPersistence()->hSet(
                $this->getParameterStorageKey($login),
                $property,
                json_encode($value)
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
     * @param string $userUri
     */
    public function removeUserData($userUri)
    {
        $login = $this->findUserLoginFromUri($userUri);
        if (empty($login)) {
            return;
        }
        $this->getPersistence()->del($this->getStorageKey($login));
        $this->getPersistence()->del($this->getParameterStorageKey($login));
        $this->getPersistence()->del($this->getUriMapKey($userUri));
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

    private function findUserLoginFromUri($userUri)
    {
        return $this->getPersistence()->get($this->getUriMapKey($userUri));
    }

    /**
     * @param string $login
     * @return string
     */
    private function getStorageKey($login)
    {
        return self::PREFIXES_KEY . ':' . $login;
    }

    /**
     * @param string $login
     * @return string
     */
    private function getParameterStorageKey($login)
    {
        return $this->getStorageKey($login) . ':' . self::USER_EXTRA_PARAMETERS;
    }

    /**
     * @param string $userUri
     * @return string
     */
    private function getUriMapKey($userUri)
    {
        return self::PREFIXES_KEY . ':' . $userUri;
    }

    /**
     * @return common_persistence_Manager
     */
    private function getPersistenceManager()
    {
        return $this->getServiceLocator()->get(common_persistence_Manager::SERVICE_ID);
    }
}
