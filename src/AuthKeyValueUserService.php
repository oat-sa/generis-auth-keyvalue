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

use common_exception_Error;
use common_persistence_AdvKeyValuePersistence;
use core_kernel_classes_Resource;
use oat\authKeyValue\helpers\OntologyDataMigration;
use oat\generis\model\GenerisRdf;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\event\UserRemovedEvent;
use oat\tao\model\event\UserUpdatedEvent;


class AuthKeyValueUserService extends ConfigurableService
{

    const SERVICE_ID = 'tao/AuthKeyValueUserService';

    const OPTION_PERSISTENCE = 'persistence';

    const PREFIXES_KEY = 'auth';
    const USER_PARAMETERS = 'parameters';
    const USER_EXTRA_PARAMETERS = 'extra_parameters';

    private $persistence;

    /**
     * @return common_persistence_AdvKeyValuePersistence
     */
    protected function getPersistence()
    {
        if (empty($this->persistence)) {
            $persistenceId = AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID;
            if ($this->hasOption(self::OPTION_PERSISTENCE)) {
                $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
            }
            $this->persistence = common_persistence_AdvKeyValuePersistence::getPersistence($persistenceId);
        }

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
     * @param UserUpdatedEvent $event
     * @throws common_exception_Error
     */
    public function userUpdated(UserUpdatedEvent $event)
    {
        $eventData = $event->jsonSerialize();
        if (isset($eventData['uri'])) {
            OntologyDataMigration::cacheUser(new core_kernel_classes_Resource($eventData['uri']));
        }
    }

    /**
     * @param UserRemovedEvent $event
     */
    public function userRemoved(UserRemovedEvent $event)
    {
        $eventData = $event->jsonSerialize();
        if (isset($eventData['login'])) {
            $this->removeUserData($eventData['login']);
        }
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
