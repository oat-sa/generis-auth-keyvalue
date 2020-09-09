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

use common_Exception;
use common_persistence_AdvKeyValuePersistence;
use common_persistence_Manager;
use common_report_Report;
use oat\authKeyValue\AuthKeyValueAdapter;
use oat\authKeyValue\AuthKeyValueUserService;
use oat\oatbox\event\EventManager;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\model\event\UserRemovedEvent;
use oat\tao\model\event\UserUpdatedEvent;

class ActivateKeyValueAuthentication extends ScriptAction
{
    /** @var common_report_Report */
    private $report;

    protected function provideOptions()
    {
        return [
            'persistence' => array(
                'prefix' => 'p',
                'longPrefix' => 'persistence',
                'required' => false,
                'description' => 'Persistence key, which will be used for user cache.',
            ),
        ];
    }

    protected function provideDescription()
    {
        return 'Installation script for Key Value authentication setup.';
    }

    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints the usage info.'
        ];
    }

    /**
     * Register key value authentication service and event listeners
     * @throws common_Exception
     */
    protected function run()
    {
        $this->report = common_report_Report::createInfo(__CLASS__ . ' script started.');

        $service = new AuthKeyValueUserService([
            AuthKeyValueUserService::OPTION_PERSISTENCE => $this->getKeyValuePersistenceId(),
        ]);
        $this->getServiceManager()->register(AuthKeyValueUserService::SERVICE_ID, $service);
        $this->report->add(common_report_Report::createSuccess('AuthKeyValueUserService was registered.'));

        $this->registerEvent(UserUpdatedEvent::class, [AuthKeyValueUserService::SERVICE_ID, 'userUpdated']);
        $this->registerEvent(UserRemovedEvent::class, [AuthKeyValueUserService::SERVICE_ID, 'userRemoved']);

        $this->report->add(common_report_Report::createSuccess('User update/remove event listeners registered.'));

        return $this->report;
    }

    /**
     * Get the persistence id from option
     *
     * @return string
     * @throws common_Exception
     */
    protected function getKeyValuePersistenceId()
    {
        $persistenceId = $this->getOption('persistence');
        if (empty($persistenceId)) {
            $this->report->add(
                new common_report_Report(
                    common_report_Report::TYPE_WARNING,
                    'Persistence key was not provided to the script. Please configure default "'
                    . AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID . '" persistence.'
                )
            );
            return AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID;
        }

        /** @var common_persistence_Manager $persistenceManager */
        $persistenceManager = $this->getServiceLocator()->get(common_persistence_Manager::SERVICE_ID);
        $persistence = $persistenceManager->getPersistenceById($persistenceId);
        if (!$persistence instanceof common_persistence_AdvKeyValuePersistence) {
            throw new common_Exception(
                'Given persistence key, "' . $persistenceId . '", is not for an advanced key value persistence.'
            );
        }

        return $persistenceId;
    }

    /**
     * @param string $event
     * @param array $callback
     * @throws InvalidServiceManagerException
     * @throws common_Exception
     */
    private function registerEvent($event, $callback)
    {
        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
        $eventManager->attach($event, $callback);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }
}
