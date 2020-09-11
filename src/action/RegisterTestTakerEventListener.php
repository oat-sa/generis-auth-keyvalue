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

declare(strict_types=1);

namespace oat\authKeyValue\action;

use common_Exception;
use oat\authKeyValue\AuthKeyValueUserService;
use oat\authKeyValue\listener\TestTakerEventListener;
use oat\oatbox\extension\InstallAction;
use oat\taoTestTaker\models\events\TestTakerImportedEvent;
use oat\taoTestTaker\models\events\TestTakerRemovedEvent;
use oat\taoTestTaker\models\events\TestTakerUpdatedEvent;

class RegisterTestTakerEventListener extends InstallAction
{
    /**
     * @param $params
     * @throws common_Exception
     */
    public function __invoke($params)
    {
        $listener = new TestTakerEventListener();
        $this->registerService(TestTakerEventListener::SERVICE_ID, $listener);
        $this->registerEvent(TestTakerUpdatedEvent::class, [TestTakerEventListener::SERVICE_ID, 'testTakerUpdated']);
        $this->registerEvent(TestTakerImportedEvent::class, [TestTakerEventListener::SERVICE_ID, 'testTakerUpdated']);
        $this->registerEvent(TestTakerRemovedEvent::class, [TestTakerEventListener::SERVICE_ID, 'testTakerRemoved']);

        return \common_report_Report::createSuccess('Test taker update/import/remove event listeners registered.');
    }
}
