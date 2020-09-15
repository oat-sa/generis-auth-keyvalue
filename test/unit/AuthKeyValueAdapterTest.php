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

use core_kernel_users_InvalidLoginException;
use oat\authKeyValue\AuthKeyValueAdapter;
use oat\authKeyValue\AuthKeyValueUser;
use oat\authKeyValue\AuthKeyValueUserService;
use oat\generis\model\GenerisRdf;
use oat\generis\test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AuthKeyValueAdapterTest extends TestCase
{
    /** @var AuthKeyValueAdapter|MockObject */
    private $object;

    /** @var AuthKeyValueUserService|MockObject */
    private $authServiceMock;

    public function setUp(): void
    {
        $this->object = $this->createPartialMock(AuthKeyValueAdapter::class, ['getAuthKeyValueUserService']);
        $this->authServiceMock = $this->createMock(AuthKeyValueUserService::class);
        $this->object->method('getAuthKeyValueUserService')->willReturn($this->authServiceMock);
    }

    public function testAuthenticate_WhenRecordNotFound_ThenExceptionThrown(): void
    {
        $this->expectException(core_kernel_users_InvalidLoginException::class);
        $this->authServiceMock->method('getUserData')->willReturn([]);
        $this->object->authenticate();
    }

    public function testAuthenticate_WhenPasswordHashDoesntMatch_ThenExceptionThrown(): void
    {
        $this->expectException(core_kernel_users_InvalidLoginException::class);
        $this->authServiceMock->method('getUserData')->willReturn($this->getUserFixture());
        $this->object->setCredentials('userLogin', 'hash');
        $this->object->authenticate();
    }

    public function testAuthenticate_WhenAuthenticationSuccessful_ThenUserObjectReturned(): void
    {
        $this->authServiceMock->method('getUserData')->willReturn($this->getUserFixture());
        $this->object->setCredentials('userLogin', 'Test123!@#');
        $this->assertInstanceOf(AuthKeyValueUser::class, $this->object->authenticate());
    }

    private function getUserFixture(): array
    {
        return [
            GenerisRdf::PROPERTY_USER_PASSWORD => 'l11RHSykKx63ac651acfe385b65ed01d10bab1f5d85131e39137c4db7af1c70b5f10636b37',
            AuthKeyValueUserService::USER_PARAMETERS => json_encode([
                'uri' => 'testUserUri',
            ])
        ];
    }
}
