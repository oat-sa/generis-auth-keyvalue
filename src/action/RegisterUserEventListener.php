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

namespace oat\authKeyValue\action;

use common_persistence_Manager;
use common_report_Report;
use oat\authKeyValue\AuthKeyValueAdapter;
use oat\authKeyValue\listener\UserListener;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\event\UserRemovedEvent;
use oat\tao\model\event\UserUpdatedEvent;

class RegisterUserEventListener extends InstallAction
{
    /**
     * Register events to keep authentication cache up to date
     *
     * @param $params
     * @return common_report_Report
     */
    public function __invoke($params)
    {
        /** @var common_persistence_Manager $persistenceManager */
        $persistenceManager = $this->getServiceLocator()->get(common_persistence_Manager::SERVICE_ID);
        if (!$persistenceManager->hasPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID)) {
            return common_report_Report::createFailure(
                'Action failed. "' . AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID . '" persistence configuration is missing.'
            );
        }

        $this->registerEvent(UserUpdatedEvent::class, [UserListener::class, 'updateUser']);
        $this->registerEvent(UserRemovedEvent::class, [UserListener::class, 'removeUser']);

        return common_report_Report::createSuccess(
            'User event listener successfully configured to update key value authentication cache.'
        );
    }
}
