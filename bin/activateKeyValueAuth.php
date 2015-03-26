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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
use oat\oatbox\user\auth\AuthFactory;

$parms = $argv;
array_shift($parms);

if (count($parms) != 1) {
    echo 'Usage: '.__FILE__.' TAOROOT'.PHP_EOL;
    die(1);
}

$root = rtrim(array_shift($parms), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
$rawStart = $root.'tao'.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'raw_start.php';

require_once $rawStart;

$ext = common_ext_ExtensionsManager::singleton()->getExtensionById('generis');
$auths = $ext->getConfig(AuthFactory::CONFIG_KEY);

foreach ($auths as $authConfig) {
    if (!isset($authConfig['driver'])) {
        throw new common_Exception('Incomplete auth configuration');
    }
    if ($authConfig['driver'] == 'oat\authKeyValue\AuthKeyValueAdapter') {
        throw new common_Exception('AuthKeyValueAdapter already present');
    }
}

common_persistence_Manager::addPersistence('authKeyValue', array(
    'driver' => 'phpredis',
    'host' => '127.0.0.1',
    'port' => 6379
));

array_unshift($auth, array('driver' => 'oat\authKeyValue\AuthKeyValueAdapter'));
$ext->setConfig(AuthFactory::CONFIG_KEY, $auths);

echo 'activate'.PHP_EOL;
