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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
namespace oat\authKeyValue\helpers;

use common_persistence_AdvKeyValuePersistence;
use oat\authKeyValue\model\AuthKeyValueAdapter;
use tao_models_classes_UserService;

/**
 * Helper to feed data into the key-value storage
 * 
 * @author Christophe Massin <christope@taotesting.com>
 */
class DataMigration {

    /**
     * Migrate users from the ontology to the key-value storage
     * 
     * @return number
     */
    public static function fromOntologyToKey (){

        $migrated = 0;

        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);
        $service = tao_models_classes_UserService::singleton();
        $users = $service->getAllUsers();


        foreach( $users as $user){

            $userParameterFormatedForDb = array();
            $userParameterFormatedForDb['uri'] = $user->getUri();

            $userData = $user->getRdfTriples();

            foreach($userData as $property){

                switch($property->predicate){
                    case PROPERTY_USER_LOGIN :
                        $userParameterFormatedForDb[PROPERTY_USER_LOGIN] = $property->object;
                        $login = $property->object;
                    case PROPERTY_USER_PASSWORD :
                        $userParameterFormatedForDb[PROPERTY_USER_PASSWORD] = $property->object;
                        $password = $property->object;
                        break;
                    case PROPERTY_USER_ROLES :
                        $userParameterFormatedForDb[PROPERTY_USER_ROLES][] = $property->object;
                        break;
                    default :
                        $userParameterFormatedForDb[$property->predicate] = $property->object;


                }

            }

            $kvStore->getDriver()->hSet($login, PROPERTY_USER_PASSWORD, $password);
            $kvStore->getDriver()->hSet($login, 'parameters', json_encode($userParameterFormatedForDb));
            $migrated++;
        }
        
        return $migrated;
    }

    public static function generateKeyValueUser()
    {
        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);

        $generationId = substr( md5(rand()), 0, 3);

        $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoGroups');


        for ($i = 0; $i < 1000; $i++) {
            $login = 'tt'.$i;
            $password = \core_kernel_users_AuthAdapter::getPasswordHash()->encrypt('pass'.$i);

            $uri = \common_Utils::getNewUri();

            $tt = array(
                'uri' => $uri,
                RDFS_LABEL => 'Test taker '.$i,
                PROPERTY_USER_UILG	=> 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
                PROPERTY_USER_DEFLG => 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
                PROPERTY_USER_LOGIN	=> $login,
                PROPERTY_USER_PASSWORD => $password,
                PROPERTY_USER_ROLES => array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'),
                PROPERTY_USER_FIRSTNAME => 'Testtaker '.$i,
                PROPERTY_USER_LASTNAME => 'Family '.$generationId
            );

            $kvStore->hset($login, PROPERTY_USER_PASSWORD, $password);
            $kvStore->hset($login, 'parameters', json_encode($tt) );

        }
    }

} 