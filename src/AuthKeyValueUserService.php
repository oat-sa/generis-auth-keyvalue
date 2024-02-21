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
use common_persistence_AdvKeyValuePersistence;
use common_persistence_Manager;
use oat\generis\persistence\PersistenceManager;
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

    protected function getPersistence(): common_persistence_AdvKeyValuePersistence
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
     * @throws common_Exception
     */
    public function storeUserData(
        ?string $uri,
        ?string $login,
        ?string $password,
        array $data,
        array $extraParams = []
        )
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

    public function getUserData(string $login): mixed
    {
        return $this->getPersistence()->hGetAll($this->getStorageKey($login));
    }

    public function removeUserData(string $userUri): void
    {
        $login = $this->findUserLoginFromUri($userUri);
        if (empty($login)) {
            return;
        }
        $this->getPersistence()->del($this->getStorageKey($login));
        $this->getPersistence()->del($this->getParameterStorageKey($login));
        $this->getPersistence()->del($this->getUriMapKey($userUri));
    }

    public function getUserParameter(string $userLogin, string $parameter): mixed
    {
        return $this->getPersistence()->hGet($this->getParameterStorageKey($userLogin), $parameter);
    }

    public function setUserParameter(string $userLogin, string $parameter, mixed $value)
    {
        $this->getPersistence()->hSet($this->getParameterStorageKey($userLogin), $parameter, $value);
    }

    private function findUserLoginFromUri($userUri): mixed
    {
        return $this->getPersistence()->get($this->getUriMapKey($userUri));
    }

    private function getStorageKey(string $login): string
    {
        return self::PREFIXES_KEY . ':' . $login;
    }

    private function getParameterStorageKey(string $login): string
    {
        return $this->getStorageKey($login) . ':' . self::USER_EXTRA_PARAMETERS;
    }

    private function getUriMapKey(string $userUri): string
    {
        return self::PREFIXES_KEY . ':' . $userUri;
    }

    private function getPersistenceManager(): PersistenceManager
    {
        return $this->getServiceLocator()->get(common_persistence_Manager::SERVICE_ID);
    }
}
