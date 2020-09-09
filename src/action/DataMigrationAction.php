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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\authKeyValue\action;

use oat\oatbox\action\Action;
use oat\authKeyValue\helpers\OntologyDataMigration;

class DataMigrationAction implements Action
{
    /**
     * Call dataMigration process to move ontology user to KV storage
     *
     * @param $params
     * @return \common_report_Report
     */
    public function __invoke($params)
    {
        if (count($params) > 0) {
            $persistenceId = array_shift($params);
            OntologyDataMigration::cacheAllUsers($persistenceId);
        } else {
            OntologyDataMigration::cacheAllUsers();
        }
        return \common_report_Report::createSuccess(__('User migrated from ontology to KeyValue storage'));
    }
}
