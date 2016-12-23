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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\authKeyValue\helpers;

use common_persistence_AdvKeyValuePersistence;
use oat\authKeyValue\AuthKeyValueAdapter;
use oat\authKeyValue\AuthKeyValueUserService;
use core_kernel_users_Service;

/**
 * 
 * @author bout
 *
 */
class DataGeneration {

    /**
     * Function that will generate key value user in redis database
     */
    public static function generateKeyValueUser()
    {
        $generationId = substr( md5(rand()), 0, 3);

        $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoGroups');


        for ($i = 0; $i < 1000; $i++) {
            $login = 'tt'.$i;
            $password = core_kernel_users_Service::getPasswordHash()->encrypt('pass'.$i);

            $tt = array(
                RDFS_LABEL => 'Test taker '.$i,
                PROPERTY_USER_UILG	=> 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
                PROPERTY_USER_DEFLG => 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
                PROPERTY_USER_LOGIN	=> $login,
                PROPERTY_USER_PASSWORD => $password,
                PROPERTY_USER_ROLES => array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'),
                PROPERTY_USER_FIRSTNAME => 'Testtaker '.$i,
                PROPERTY_USER_LASTNAME => 'Family '.$generationId
            );
            
            self::createUser($tt);
        }
    }
    
    public static function createUser($data = array(), $lang = null, $uri = null)
    {
        if (!isset($data[PROPERTY_USER_LOGIN]) || !isset($data[PROPERTY_USER_PASSWORD])) {
            throw new \common_exception_InconsistentData('Cannot add user without login or password');
        }

        if(is_null($lang)){
            $lang = DEFAULT_LANG;
        }
        $login = $data[PROPERTY_USER_LOGIN];
        $password = $data[PROPERTY_USER_PASSWORD];
        
        $defaultData = array(
            RDFS_LABEL => 'Test taker',
            PROPERTY_USER_UILG	=> 'http://www.tao.lu/Ontologies/TAO.rdf#Lang'.$lang,
            PROPERTY_USER_DEFLG => 'http://www.tao.lu/Ontologies/TAO.rdf#Lang'.$lang,
            PROPERTY_USER_ROLES => array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'),
        );
        
        $data = array_merge($defaultData, $data);
        
        $data['uri'] = (empty($uri)) ? \common_Utils::getNewUri() : $uri;
        
        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);
        $kvStore->hset(AuthKeyValueUserService::PREFIXES_KEY.':'.$login, PROPERTY_USER_PASSWORD, $password);
        $kvStore->hset(AuthKeyValueUserService::PREFIXES_KEY.':'.$login, 'parameters', json_encode($data) );
		return $data;
    }

} 
