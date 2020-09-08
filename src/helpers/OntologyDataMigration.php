<?php
/**
 * Created by PhpStorm.
 * User: christophemassin
 * Date: 3/07/14
 * Time: 16:39
 */

namespace oat\authKeyValue\helpers;

use oat\authKeyValue\AuthKeyValueAdapter;
use tao_models_classes_UserService;
use oat\authKeyValue\AuthKeyValueUserService;
use oat\generis\model\GenerisRdf;

class OntologyDataMigration
{

    public static function migrateAllUsers($persistenceID = AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID)
    {
        $service = tao_models_classes_UserService::singleton();
        $users = $service->getAllUsers();

        foreach ($users as $user) {
            self::migrateUser($user, $persistenceID);
        }
    }

    public static function migrateUser($user, $persistenceID = AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID)
    {
        $service = new AuthKeyValueUserService($persistenceID);

        $userParameterFormatedForDb = [];
        $userParameterFormatedForDbExtraParameters = [];
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

        $service->removeUserData($login);
        $service->storeUserData(
            $login,
            $password,
            $userParameterFormatedForDb,
            $userParameterFormatedForDbExtraParameters
        );
    }
}
