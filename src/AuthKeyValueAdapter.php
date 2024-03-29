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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

/**
 * Authentication adapter interface to be implemented by authentication methodes
 *
 * @author christophe massin
 * @package authKeyValue

 */

namespace oat\authKeyValue;

use core_kernel_users_Service;
use core_kernel_users_InvalidLoginException;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\user\auth\LoginAdapter;
use oat\oatbox\Configurable;
use oat\generis\model\GenerisRdf;

/**
 * Adapter to authenticate users stored in the key value implementation
 *
 * @author Christophe Massin <christope@taotesting.com>
 *
 */
class AuthKeyValueAdapter extends Configurable implements LoginAdapter
{
    /** default key used to retrieve the persistence information */
    CONST KEY_VALUE_PERSISTENCE_ID = 'authKeyValue';

    private string $username;
    private string $password;

    /**
     * Set the credential
     */
    public function setCredentials($login, $password): void
    {
        $this->username = $login;
        $this->password = $password;
    }

    public function getConfiguration(): array
    {
        return $this->getOptions();
    }

	/**
     * (non-PHPdoc)
     * @see common_user_auth_Adapter::authenticate()
     */
    public function authenticate() {

        $userData = $this->getAuthKeyValueUserService()->getUserData($this->username);

        $hashing = core_kernel_users_Service::getPasswordHash();

        if( isset($userData[GenerisRdf::PROPERTY_USER_PASSWORD]) && $hashing->verify($this->password, $userData[GenerisRdf::PROPERTY_USER_PASSWORD]))
        {
            // user is authentified, create the user for the session

            $params = json_decode($userData[AuthKeyValueUserService::USER_PARAMETERS],true);
            $user = new AuthKeyValueUser();
            $user->setConfiguration($this->getOptions());
            $user->setIdentifier($params['uri']);
            if (isset($params[GenerisRdf::PROPERTY_USER_DEFLG])) {
                $user->setLanguageDefLg($params[GenerisRdf::PROPERTY_USER_DEFLG]);
            }
            $user->setUserRawParameters($params);

            return $user;

        } else {
            throw new core_kernel_users_InvalidLoginException('User "'.$this->username.'" failed key-value authentication.');
        }

    }

    protected function getAuthKeyValueUserService(): AuthKeyValueUserService
    {
        return ServiceManager::getServiceManager()->get(AuthKeyValueUserService::SERVICE_ID);
    }
}
