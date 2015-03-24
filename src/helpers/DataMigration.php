<?php
/**
 * Created by PhpStorm.
 * User: christophemassin
 * Date: 3/07/14
 * Time: 16:39
 */

namespace oat\authKeyValue\helpers;

use common_persistence_AdvKeyValuePersistence;
use oat\authKeyValue\AuthKeyValueAdapter;
use tao_models_classes_UserService;
use oat\authKeyValue\AuthKeyValueUserService;
use core_kernel_users_Service;

class DataMigration {

    public static function fromOntologyToKey (){


        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);
        $service = tao_models_classes_UserService::singleton();
        $users = $service->getAllUsers();

        foreach( $users as $user){

            $userParameterFormatedForDb = array();
            $userParameterFormatedForDbExtraParameters = array();
            $userParameterFormatedForDb['uri'] = $user->getUri();

            $userData = $user->getRdfTriples();

            foreach($userData as $property){

                switch($property->predicate){
                    case PROPERTY_USER_LOGIN :
                        $userParameterFormatedForDb[PROPERTY_USER_LOGIN] = $property->object;
                        $login = $property->object;
                        break;
                    case PROPERTY_USER_PASSWORD :
                        $userParameterFormatedForDb[PROPERTY_USER_PASSWORD] = $property->object;
                        $password = $property->object;
                        break;
                    case PROPERTY_USER_ROLES :
                        $userParameterFormatedForDb[PROPERTY_USER_ROLES][] = $property->object;
                        break;
                    case PROPERTY_USER_UILG :
                        $userParameterFormatedForDb[PROPERTY_USER_UILG] = $property->object;
                        break;
                    case PROPERTY_USER_DEFLG :
                        $userParameterFormatedForDb[PROPERTY_USER_DEFLG] = $property->object;
                        break;
                    case PROPERTY_USER_FIRSTNAME :
                        $userParameterFormatedForDb[PROPERTY_USER_FIRSTNAME] = $property->object;
                        break;
                    case PROPERTY_USER_LASTNAME :
                        $userParameterFormatedForDb[PROPERTY_USER_LASTNAME] = $property->object;
                        break;
                    default :
                        $userParameterFormatedForDbExtraParameters[$property->predicate] = $property->object;

                }

            }


            $kvStore->getDriver()->hSet(AuthKeyValueUserService::PREFIXES_KEY.':'.$login, PROPERTY_USER_PASSWORD, $password);
            $kvStore->getDriver()->hSet(AuthKeyValueUserService::PREFIXES_KEY.':'.$login, 'parameters', json_encode($userParameterFormatedForDb));

            foreach($userParameterFormatedForDbExtraParameters as $key => $value ) {
                $kvStore->getDriver()->set(AuthKeyValueUserService::PREFIXES_KEY.':'.$login.':'.$key, $value);
            }

        }

    }
} 