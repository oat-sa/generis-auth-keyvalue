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

namespace oat\authKeyValue\test\unit;

use common_persistence_AdvKeyValuePersistence;
use oat\authKeyValue\AuthKeyValueUserService;
use oat\generis\test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AuthKeyValueUserServiceTest extends TestCase
{
    /** @var AuthKeyValueUserService|MockObject */
    private $service;

    /** @var common_persistence_AdvKeyValuePersistence|MockObject */
    private $persistenceMock;


    public function setUp(): void
    {
        $this->service = $this->createPartialMock(AuthKeyValueUserService::class, ['getPersistence']);
        $this->persistenceMock = $this->createMock(common_persistence_AdvKeyValuePersistence::class);
        $this->service->method('getPersistence')->willReturn($this->persistenceMock);
    }

    public function testStoreUserData_WhenLoginOrPasswordEmpty_ThenNoRecordStored(): void
    {
        $this->persistenceMock->expects($this->never())->method('hSet');
        $this->service->storeUserData('testUri', 'testLogin', '', []);
        $this->service->storeUserData('testUri', '', 'testPasswordHash', []);
    }

    public function testStoreUserData_WhenParametersProvided_ThenRecordsStoredInPersistence(): void
    {
        $this->persistenceMock->expects($this->exactly(4))->method('hSet');
        $this->service->storeUserData(
            'testUri',
            'testLogin',
            'testPasswordHash',
            ['property1' => 'value'],
            ['extra-property1' => 'value', 'extra-property2' => 'value']
        );
    }

    public function testRemoveUserData_WhenLoginNotFoundByUri_ThenNoRecordsRemoved(): void
    {
        $this->persistenceMock->method('get')->willReturn(false);
        $this->persistenceMock->expects($this->never())->method('del');
        $this->service->removeUserData('testUri');
    }

    public function testRemoveUserData_WhenLoginFoundByUri_ThenAuthRecordsRemoved(): void
    {
        $this->persistenceMock->method('get')->willReturn('testLogin');
        $this->persistenceMock->expects($this->exactly(3))->method('del');
        $this->service->removeUserData('testUri');
    }
}
