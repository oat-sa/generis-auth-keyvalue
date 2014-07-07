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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
require_once dirname(__FILE__) .'/../includes/raw_start.php';

/**
 * Copy all tao users from the ontology to the key-value storage.
 * Existing users sharing the same login will be overwritten.
 * 
 * @author Christophe Massin <christope@taotesting.com>
 */
require_once dirname(__FILE__) .'/../includes/raw_start.php';
oat\authKeyValue\helpers\DataMigration::generateKeyValueUser();
echo 'Generated users directly in the key-value storage'.PHP_EOL;
