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

use oat\authKeyValue\AuthKeyValueUserService;
use oat\authKeyValue\helpers\OntologyDataMigration;
use oat\oatbox\service\ConfigurableService;
use oat\taoTestTaker\models\events\AbstractTestTakerEvent;
use oat\taoTestTaker\models\events\TestTakerRemovedEvent;

class TestTakerEventListener extends ConfigurableService
{
    public function testTakerUpdated(AbstractTestTakerEvent $event): void
    {
        $eventData = $event->jsonSerialize();
        if (isset($eventData['testTakerUri'])) {
            OntologyDataMigration::cacheUser($eventData['testTakerUri']);
        }
    }

    public function testTakerRemoved(TestTakerRemovedEvent $event): void
    {
        $eventData = $event->jsonSerialize();
        if (isset($eventData['testTakerUri'])) {
            $this->getAuthKeyValueUserService()->removeUserData($eventData['testTakerUri']);
        }
    }

    protected function getAuthKeyValueUserService(): AuthKeyValueUserService
    {
        return $this->getServiceLocator()->get(AuthKeyValueUserService::SERVICE_ID);
    }
}
