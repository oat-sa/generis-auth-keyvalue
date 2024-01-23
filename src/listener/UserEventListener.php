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

namespace oat\authKeyValue\listener;

use common_exception_Error;
use core_kernel_classes_Resource;
use core_kernel_classes_Property;
use oat\authKeyValue\AuthKeyValueUserService;
use oat\authKeyValue\helpers\OntologyDataMigration;
use oat\generis\model\data\event\ResourceUpdated;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\event\UserRemovedEvent;
use oat\tao\model\event\UserUpdatedEvent;
use oat\generis\model\OntologyRdf;
use oat\tao\model\TaoOntology;

class UserEventListener extends ConfigurableService
{
    /**
     * @param UserUpdatedEvent|ResourceUpdated $event
     * @throws common_exception_Error
     */
    public function userUpdated(UserUpdatedEvent|ResourceUpdated $event)
    {
        if ($event instanceof UserUpdatedEvent) {
            $this->handleUserUpdatedEvent($event);
        } else {
            $this->handleResourceUpdatedEvent($event);
        }
    }
    
    private function handleUserUpdatedEvent(UserUpdatedEvent $event)
    {
        $eventData = $event->jsonSerialize();
        if (isset($eventData['uri'])) {
            OntologyDataMigration::cacheUser($eventData['uri']);
        }
    }
    
    private function handleResourceUpdatedEvent(ResourceUpdated $event)
    {
        $resource = $event->getResource();
        /** @var core_kernel_classes_Resource $userType */
        $userType = $resource->getOnePropertyValue(new core_kernel_classes_Property(OntologyRdf::RDF_TYPE));
        // check if the resource is a user
        if ($userType && $userType->getUri() === TaoOntology::CLASS_URI_TAO_USER) {
            OntologyDataMigration::cacheUser($resource->getUri());
        }
    }

    /**
     * @param UserRemovedEvent $event
     */
    public function userRemoved(UserRemovedEvent $event)
    {
        $eventData = $event->jsonSerialize();
        if (isset($eventData['uri'])) {
            $this->getAuthKeyValueUserService()->removeUserData($eventData['uri']);
        }
    }

    /**
     * @return AuthKeyValueUserService
     */
    protected function getAuthKeyValueUserService()
    {
        return $this->getServiceLocator()->get(AuthKeyValueUserService::SERVICE_ID);
    }
}
