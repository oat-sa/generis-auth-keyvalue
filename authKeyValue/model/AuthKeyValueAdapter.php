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
use common_persistence_AdvKeyValuePersistence;
use common_user_auth_Adapter;
use helpers_PasswordHash;
use core_kernel_users_InvalidLoginException;
use common_session_BasicSession;


class AuthKeyValueAdapter implements common_user_auth_Adapter
{
    CONST LEGACY_ALGORITHM = 'md5';
    CONST LEGACY_SALT_LENGTH = 0;

    /**
     * Returns the hashing algorithm defined in generis configuration
     * 
     * @return helpers_PasswordHash
     */
    public static function getPasswordHash() {
        return new helpers_PasswordHash(
            defined('PASSWORD_HASH_ALGORITHM') ? PASSWORD_HASH_ALGORITHM : self::LEGACY_ALGORITHM,
            defined('PASSWORD_HASH_SALT_LENGTH') ? PASSWORD_HASH_SALT_LENGTH : self::LEGACY_SALT_LENGTH
        );
    }


    private $username;
    private $password;

    /**
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

	/**
     * (non-PHPdoc)
     * @see common_user_auth_Adapter::authenticate()
     */
    public function authenticate() {

       $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence("keyValueUser");
        // login will always be unique due to redis and his unique keys access system.
        $userData = $kvStore->getDriver()->hGetAll($this->username);

        $hashing = $this->getPasswordHash();

        if( isset($userData[PROPERTY_USER_PASSWORD]) && $hashing->verify($this->password, $userData[PROPERTY_USER_PASSWORD]))
        {
            // user is authentified, create the user for the session

            $userParameters = json_decode($userData['parameters']);
            $params = get_object_vars($userParameters);
            $user = new AuthKeyValueUser();
            $user->setIdentifier($userParameters->uri);
            $user->setRoles($params[PROPERTY_USER_ROLES]);
            $user->setLanguage($params[PROPERTY_USER_UILG]);
            $user->setUserRawParameters($params);


            $session = new \common_session_DefaultSession($user);
            \common_session_SessionManager::startSession($session);

        } else {
            throw new core_kernel_users_InvalidLoginException();
        }

    }
}