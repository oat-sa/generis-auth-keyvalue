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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA ;
 */

namespace oat\authKeyValue\helpers;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\oatbox\service\ServiceManager;
use tao_models_classes_UserService;
use oat\authKeyValue\AuthKeyValueUserService;
use oat\generis\model\GenerisRdf;

class OntologyDataMigration
{

    public static function cacheAllUsers($persistenceId = null)
    {
        $service = tao_models_classes_UserService::singleton();
        $users = $service->getAllUsers();

        foreach ($users as $user) {
            self::cacheUser($user->getUri(), $persistenceId);
        }
    }

    public static function cacheUser($userUri, $persistenceId = null)
    {
        $userData = [];
        $userExtraData = [];
        $userData['uri'] = $userUri;

        $user = new core_kernel_classes_Resource($userUri);
        foreach ($user->getRdfTriples() as $rdfTriple) {
            switch ($rdfTriple->predicate) {
                case GenerisRdf::PROPERTY_USER_LOGIN :
                    $userData[GenerisRdf::PROPERTY_USER_LOGIN] = $rdfTriple->object;
                    $login = $rdfTriple->object;
                    break;
                case GenerisRdf::PROPERTY_USER_PASSWORD :
                    $userData[GenerisRdf::PROPERTY_USER_PASSWORD] = $rdfTriple->object;
                    $password = $rdfTriple->object;
                    break;
                case GenerisRdf::PROPERTY_USER_ROLES :
                    $userData[GenerisRdf::PROPERTY_USER_ROLES][] = $rdfTriple->object;
                    break;
                case GenerisRdf::PROPERTY_USER_UILG :
                    $userData[GenerisRdf::PROPERTY_USER_UILG] = $rdfTriple->object;
                    break;
                case GenerisRdf::PROPERTY_USER_DEFLG :
                    $userData[GenerisRdf::PROPERTY_USER_DEFLG] = $rdfTriple->object;
                    break;
                case GenerisRdf::PROPERTY_USER_FIRSTNAME :
                    $userData[GenerisRdf::PROPERTY_USER_FIRSTNAME] = $rdfTriple->object;
                    break;
                case GenerisRdf::PROPERTY_USER_LASTNAME :
                    $userData[GenerisRdf::PROPERTY_USER_LASTNAME] = $rdfTriple->object;
                    break;
                default :
                    $property = new core_kernel_classes_Property($rdfTriple->predicate);

                    if ($property->isMultiple()) {
                        $userExtraData[$rdfTriple->predicate][] = $rdfTriple->object;
                    } else {
                        $userExtraData[$rdfTriple->predicate] = $rdfTriple->object;
                    }
            }
        }

        $service = ServiceManager::getServiceManager()->get(AuthKeyValueUserService::SERVICE_ID);
        if (!empty($persistenceId)) {
            $service->setOption(AuthKeyValueUserService::OPTION_PERSISTENCE, $persistenceId);
        }
        $service->removeUserData($userUri);
        $service->storeUserData($userUri, $login, $password, $userData, $userExtraData);
    }
}
