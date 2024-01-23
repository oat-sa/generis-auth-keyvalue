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
use common_exception_Error;
use common_ext_ExtensionsManager;
use common_persistence_AdvKeyValuePersistence;
use common_persistence_Manager;
use common_report_Report;
use oat\authKeyValue\AuthKeyValueAdapter;
use oat\authKeyValue\AuthKeyValueUserService;
use oat\authKeyValue\listener\UserEventListener;
use oat\oatbox\event\EventManager;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\oatbox\user\auth\AuthFactory;
use oat\tao\model\event\UserRemovedEvent;
use oat\tao\model\event\UserUpdatedEvent;
use oat\generis\model\data\event\ResourceUpdated;

class ActivateKeyValueAuthentication extends ScriptAction
{
    /** @var common_report_Report */
    private $report;

    protected function provideOptions()
    {
        return [
            'persistence' => [
                'prefix' => 'p',
                'longPrefix' => 'persistence',
                'required' => false,
                'description' => 'Persistence key, which will be used for user cache.',
            ],
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

        $this->registerAuthKeyValueUserService();
        $this->registerAuthKeyValueAdapter();
        $this->registerUserEventListener();
        $this->registerTestTakerEventListener();

        return $this->report;
    }

    /**
     * @throws InvalidServiceManagerException
     * @throws common_exception_Error
     * @throws common_Exception
     */
    private function registerAuthKeyValueUserService()
    {
        $service = new AuthKeyValueUserService(
            [
                AuthKeyValueUserService::OPTION_PERSISTENCE => $this->getKeyValuePersistenceId(),
            ]
        );
        $this->getServiceManager()->register(AuthKeyValueUserService::SERVICE_ID, $service);
        $this->report->add(common_report_Report::createSuccess('AuthKeyValueUserService was registered.'));
    }

    private function registerAuthKeyValueAdapter()
    {
        $generisExtension = $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('generis');

        $auths = $generisExtension->getConfig(AuthFactory::CONFIG_KEY);
        foreach ($auths as $authConfig) {
            if (isset($authConfig['driver']) && $authConfig['driver'] === AuthKeyValueAdapter::class) {
                $this->report->add(common_report_Report::createInfo('AuthKeyValueAdapter already configured.'));
                return;
            }
        }

        array_unshift($auths, ['driver' => AuthKeyValueAdapter::class]);
        $generisExtension->setConfig(AuthFactory::CONFIG_KEY, $auths);
        $this->report->add(common_report_Report::createSuccess('AuthKeyValueAdapter configured successfully.'));
    }

    /**
     * @throws InvalidServiceManagerException
     * @throws common_Exception
     * @throws common_exception_Error
     */
    private function registerUserEventListener(): void
    {
        $this->registerEvent(UserUpdatedEvent::class, [UserEventListener::class, 'userUpdated']);
        $this->registerEvent(ResourceUpdated::class, [UserEventListener::class, 'userUpdated']);
        $this->registerEvent(UserRemovedEvent::class, [UserEventListener::class, 'userRemoved']);

        $this->report->add(common_report_Report::createSuccess('User update/remove event listeners registered.'));
    }

    /**
     * @throws common_exception_Error
     */
    private function registerTestTakerEventListener(): void
    {
        /** @var common_ext_ExtensionsManager $extManager */
        $extManager = $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
        if ($extManager->isInstalled('taoTestTaker')) {
            $action = $this->propagate(new RegisterTestTakerEventListener());
            $this->report->add($action([]));
        } else {
            $this->report->add(
                common_report_Report::createInfo(
                    'TAO TestTaker extension is not installed, test taker event listener not registered.'
                )
            );
        }
    }

    /**
     * Get the persistence id from option
     *
     * @return string
     * @throws common_Exception
     */
    protected function getKeyValuePersistenceId()
    {
        $persistenceId = $this->getPersistenceFromParameters();
        if ($persistenceId) {
            return $persistenceId;
        }

        return $this->getDefaultPersistence();
    }

    /**
     * @param string $event
     * @param array $callback
     * @throws InvalidServiceManagerException
     * @throws common_Exception
     */
    private function registerEvent($event, $callback): void
    {
        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
        $eventManager->attach($event, $callback);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }

    /**
     * @throws common_Exception
     */
    private function getPersistenceFromParameters()
    {
        $persistenceId = $this->getOption('persistence');
        if (empty($persistenceId)) {
            return null;
        }
        $persistence = $this->getPersistenceManager()->getPersistenceById($persistenceId);
        if (!$persistence instanceof common_persistence_AdvKeyValuePersistence) {
            throw new common_Exception(
                'Given persistence key, "' . $persistenceId . '", is not for an advanced key value persistence.'
            );
        }

        return $persistenceId;
    }

    private function getDefaultPersistence()
    {
        if (!$this->getPersistenceManager()->hasPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID)) {
            $this->report->add(
                new common_report_Report(
                    common_report_Report::TYPE_WARNING,
                    'Persistence key was not provided to the script. Please configure default "'
                    . AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID . '" persistence.'
                )
            );

            return AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID;
        }

        $persistence = $this->getPersistenceManager()->getPersistenceById(
            AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID
        );
        if (!$persistence instanceof common_persistence_AdvKeyValuePersistence) {
            throw new common_Exception(
                'Configuration found for default persistence "' . AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID . '", but it is incorrect, it must be advanced key value persistence.'
            );
        }

        return AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID;
    }

    /**
     * @return common_persistence_Manager
     */
    private function getPersistenceManager()
    {
        return $this->getServiceLocator()->get(common_persistence_Manager::SERVICE_ID);
    }
}
