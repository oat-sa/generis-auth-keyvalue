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

use oat\authKeyValue\AuthKeyValueUser;
use oat\authKeyValue\AuthKeyValueUserService;
use oat\generis\model\GenerisRdf;
use oat\generis\test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AuthKeyValueUserTest extends TestCase
{
    /** @var AuthKeyValueUser|MockObject */
    private $authUser;

    /** @var AuthKeyValueUserService|MockObject */
    private $authUserService;

    public function setUp(): void
    {
        $this->authUser = $this->createPartialMock(AuthKeyValueUser::class, ['getAuthKeyValueUserService']);
        $this->authUser->setUserRawParameters(
            [
                GenerisRdf::PROPERTY_USER_LOGIN => 'testUserUri',
                'propertyUri' => 'propertyValue'
            ]
        );
        $this->authUserService = $this->createMock(AuthKeyValueUserService::class);
        $this->authUser->method('getAuthKeyValueUserService')->willReturn($this->authUserService);
    }

    public function testGetPropertyValues_WhenPropertyNotFound_ThenEmptyArrayReturned()
    {
        $this->assertEquals([], $this->authUser->getPropertyValues('testUri'));
    }

    public function testGetPropertyValues_WhenRawParamsContainProperty_ThenValueIsReturned()
    {
        $this->assertEquals(['propertyValue'], $this->authUser->getPropertyValues('propertyUri'));
    }

    public function testGetPropertyValues_WhenExtraParamAccessedSecondTime_ThenItsReturnedFromCache()
    {
        $this->authUserService
            ->expects($this->once())
            ->method('getUserParameter')
            ->willReturn(json_encode('extraValue'));
        $this->assertEquals(['extraValue'], $this->authUser->getPropertyValues('extraProperty'));
        $this->assertEquals(['extraValue'], $this->authUser->getPropertyValues('extraProperty'));
    }

    public function testGetPropertyValues_WhenExtraParamValueSizeExceedsLimit_ThenItsReadFromPersistence()
    {
        $this->authUser->setConfiguration(['max_size_cached_element' => 1]);
        $this->authUserService
            ->expects($this->exactly(2))
            ->method('getUserParameter')
            ->willReturn(json_encode('extraValue'));
        $this->assertEquals(['extraValue'], $this->authUser->getPropertyValues('extraProperty'));
        $this->assertEquals(['extraValue'], $this->authUser->getPropertyValues('extraProperty'));
    }

    public function testRefresh_WhenInitiated_ThenLocalCacheIsCleared()
    {
        $this->authUserService->method('getUserData')->willReturn([
            GenerisRdf::PROPERTY_USER_PASSWORD => 'passwordHash',
            AuthKeyValueUserService::USER_PARAMETERS => json_encode([
                'uri' => 'testUserUri',
            ]),
        ]);
        $this->authUser->setUserExtraParameters(['testExtraParam' => 'value']);
        $this->authUser->refresh();
        $this->assertEmpty($this->authUser->getUserExtraParameters());
    }

    public function testGetPropertyValues_WithParamAsArray_ThenItsReturnedFromCache()
    {
        $data = ['extraValue1', 'extraValue2'];

        $this->authUserService
            ->expects($this->once())
            ->method('getUserParameter')
            ->willReturn(json_encode($data));
        $this->assertEquals($data, $this->authUser->getPropertyValues('extraProperty'));
        $this->assertEquals($data, $this->authUser->getPropertyValues('extraProperty'));
    }

    public function testGetPropertyValues_WithParamAsArray_ThenItsReadFromPersistence()
    {
        $data = ['extraValue1', 'extraValue2'];

        $this->authUser->setConfiguration(['max_size_cached_element' => 1]);
        $this->authUserService
            ->expects($this->exactly(2))
            ->method('getUserParameter')
            ->willReturn(json_encode($data));
        $this->assertEquals($data, $this->authUser->getPropertyValues('extraProperty'));
        $this->assertEquals($data, $this->authUser->getPropertyValues('extraProperty'));
    }
}
