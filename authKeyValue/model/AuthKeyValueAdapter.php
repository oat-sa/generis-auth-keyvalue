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

namespace oat\authKeyValue\model;

use common_user_auth_Adapter;
use core_kernel_users_InvalidLoginException;
use core_kernel_users_AuthAdapter;
use oat\authKeyValue\model\AuthKeyValueUserService;
use oat\oatbox\user\auth\LoginAdapter;


/**
 * Adapter to authenticate users stored in the key value implementation
 * 
 * @author Christophe Massin <christope@taotesting.com>
 *
 */
class AuthKeyValueAdapter implements LoginAdapter
{

    /** Key used to retrieve the persistence information */
    CONST KEY_VALUE_PERSISTENCE_ID = 'authKeyValue';

    /** @var  $username string */
    private $username;

    /** @var  $password string */
    private $password;

    /** @var $configuration array $configuration  */
    protected $configuration;


    /**
     * @param array $configuration
     */
    public function __construct(array $configuration) {
        $this->configuration = $configuration;

    /**
     * Set the credential
     *
     * @param string $login
     * @param string $password
     */
    }

    public function setCredentials($login, $password){
        $this->username = $login;
        $this->password = $password;
    }

    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

	/**
     * (non-PHPdoc)
     * @see common_user_auth_Adapter::authenticate()
     */
    public function authenticate() {

        $service = new AuthKeyValueUserService();
        $userData = $service->getUserData($this->username);

        $hashing = core_kernel_users_AuthAdapter::getPasswordHash();

        if( isset($userData[PROPERTY_USER_PASSWORD]) && $hashing->verify($this->password, $userData[PROPERTY_USER_PASSWORD]))
        {
            // user is authentified, create the user for the session

            $params = json_decode($userData[AuthKeyValueUserService::USER_PARAMETERS],true);
            $user = new AuthKeyValueUser();
            $user->setConfiguration($this->getConfiguration());
            $user->setIdentifier($params['uri']);
            $user->setRoles($params[PROPERTY_USER_ROLES]);
            $user->setLanguageUi($params[PROPERTY_USER_UILG]);
            $user->setLanguageDefLg($params[PROPERTY_USER_DEFLG]);
            $user->setUserRawParameters($params);

            $session = new \common_session_DefaultSession($user);
            \common_session_SessionManager::startSession($session);

        } else {
            throw new core_kernel_users_InvalidLoginException();
        }

    }


}

