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
use oat\generis\model\GenerisRdf;

class OntologyDataMigration
{

    public static function fromOntologyToKey($persistenceID = AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID)
    {
        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence($persistenceID);
        $service = tao_models_classes_UserService::singleton();
        $users = $service->getAllUsers();

        foreach ($users as $user) {
            $userParameterFormatedForDb = array();
            $userParameterFormatedForDbExtraParameters = array();
            $userParameterFormatedForDb['uri'] = $user->getUri();

            $userData = $user->getRdfTriples();

            foreach ($userData as $property) {
                switch ($property->predicate) {
                    case GenerisRdf::PROPERTY_USER_LOGIN :
                        $userParameterFormatedForDb[GenerisRdf::PROPERTY_USER_LOGIN] = $property->object;
                        $login = $property->object;
                        break;
                    case GenerisRdf::PROPERTY_USER_PASSWORD :
                        $userParameterFormatedForDb[GenerisRdf::PROPERTY_USER_PASSWORD] = $property->object;
                        $password = $property->object;
                        break;
                    case GenerisRdf::PROPERTY_USER_ROLES :
                        $userParameterFormatedForDb[GenerisRdf::PROPERTY_USER_ROLES][] = $property->object;
                        break;
                    case GenerisRdf::PROPERTY_USER_UILG :
                        $userParameterFormatedForDb[GenerisRdf::PROPERTY_USER_UILG] = $property->object;
                        break;
                    case GenerisRdf::PROPERTY_USER_DEFLG :
                        $userParameterFormatedForDb[GenerisRdf::PROPERTY_USER_DEFLG] = $property->object;
                        break;
                    case GenerisRdf::PROPERTY_USER_FIRSTNAME :
                        $userParameterFormatedForDb[GenerisRdf::PROPERTY_USER_FIRSTNAME] = $property->object;
                        break;
                    case GenerisRdf::PROPERTY_USER_LASTNAME :
                        $userParameterFormatedForDb[GenerisRdf::PROPERTY_USER_LASTNAME] = $property->object;
                        break;
                    default :
                        $userParameterFormatedForDbExtraParameters[$property->predicate] = $property->object;
                }
            }


            $kvStore->getDriver()->hSet(
                AuthKeyValueUserService::PREFIXES_KEY . ':' . $login,
                GenerisRdf::PROPERTY_USER_PASSWORD,
                $password
            );
            $kvStore->getDriver()->hSet(
                AuthKeyValueUserService::PREFIXES_KEY . ':' . $login,
                'parameters',
                json_encode($userParameterFormatedForDb)
            );

            foreach ($userParameterFormatedForDbExtraParameters as $key => $value) {
                $kvStore->getDriver()->set(AuthKeyValueUserService::PREFIXES_KEY . ':' . $login . ':' . $key, $value);
            }
        }
    }
}
