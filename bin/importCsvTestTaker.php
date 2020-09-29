<?php

use oat\authKeyValue\AuthKeyValueUserService;
use oat\authKeyValue\helpers\DataGeneration;
use oat\oatbox\service\ServiceManager;
use oat\taoGroups\models\GroupsService;
use oat\generis\model\OntologyRdfs;
use oat\generis\model\GenerisRdf;
use oat\tao\model\TaoOntology;

$parms = $argv;
array_shift($parms);

if (count($parms) < 2 || count($parms) > 4) {
    echo 'Usage: ' . __FILE__ . ' TAOROOT CSVFILE [LANG] [GROUPURI]' . PHP_EOL;
    die(1);
}

$root = rtrim(array_shift($parms), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$csvfile = array_shift($parms);
$lang = empty($parms) ? null : array_shift($parms);
$groupUri = empty($parms) ? null : array_shift($parms);

$rawStart = $root . 'tao' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'raw_start.php';

if (!file_exists($rawStart)) {
    echo 'Tao not found at "' . $rawStart . '"' . PHP_EOL;
    die(1);
}
require_once $rawStart;

if (!file_exists($csvfile)) {
    echo 'Csv file not found at "' . $csvfile . '"' . PHP_EOL;
    die(1);
}

if (is_null($groupUri)) {
    $label = 'Group ' . uniqid();
    $groupClass = new \core_kernel_classes_Class(TaoOntology::GROUP_CLASS_URI);
    $group = $groupClass->createInstanceWithProperties(
        array(
            OntologyRdfs::RDFS_LABEL => $label
        )
    );
    echo 'Group "' . $label . '" created.' . PHP_EOL;
    $groupUri = $group->getUri();
} else {
    $group = new core_kernel_classes_Resource($groupUri);
    if (!$group->exists()) {
        echo 'Group "' . $groupUri . '" not found.' . PHP_EOL;
        die(1);
    }
}


$persistence = \common_persistence_Manager::getPersistence('default');
try{
    $schemaManager = $persistence->getDriver()->getSchemaManager();
    $schema = $schemaManager->createSchema();

    if(!$schema->hastable('redis')){
        $fromSchema = clone $schema;
        $tableResults = $schema->createtable('redis');
        $tableResults->addOption('engine', 'MyISAM');
        $tableResults->addColumn('subject', 'string', ['length' => 255]);
        $tableResults->addColumn('predicate', 'string', ['length' => 255]);
        $tableResults->addColumn('object', 'string', ['length' => 255]);

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
        foreach ($queries as $query) {
            $persistence->exec($query);
        }
    }

} catch(\Doctrine\DBAL\Schema\SchemaException $e) {
    \common_Logger::i('Database Schema already up to date.');
}


$expected = array(
    'login' => GenerisRdf::PROPERTY_USER_LOGIN,
    'password' => GenerisRdf::PROPERTY_USER_PASSWORD,
);
$keys = array_keys($expected);
$userService = ServiceManager::getServiceManager()->get(AuthKeyValueUserService::SERVICE_ID);

$row = 1;
if (($handle = fopen($csvfile, "r")) !== false) {
    while (($data = fgetcsv($handle, 1000, ";")) !== false) {
        if ($row === 1) {
//            if (json_encode($data) != json_encode(array_keys($expected))) {
//                echo 'Expected data in the format "'.implode('","', $expected).'".'.PHP_EOL;
//                die(1);
//            }
        } else {
            $toAdd = array(
                GroupsService::PROPERTY_MEMBERS_URI => array($groupUri)
            );
            foreach ($data as $pos => $value) {
                $toAdd[$expected[$keys[$pos]]] = $value;
            }

            // encode password
            $toAdd[GenerisRdf::PROPERTY_USER_PASSWORD] = core_kernel_users_Service::getPasswordHash()->encrypt(
                $toAdd[GenerisRdf::PROPERTY_USER_PASSWORD]
            );

            if ($userService->getUserData($toAdd[GenerisRdf::PROPERTY_USER_LOGIN]) != false) {
                echo 'User "' . $toAdd[GenerisRdf::PROPERTY_USER_LOGIN] . '" already exists.' . PHP_EOL;
            } else {
                $userData = DataGeneration::createUser($toAdd, $lang);

                try {
                    $persistence->insert(
                        'redis',
                        array(
                            'subject' => $userData['uri'],
                            'predicate' => GenerisRdf::PROPERTY_USER_LOGIN,
                            'object' => $userData[GenerisRdf::PROPERTY_USER_LOGIN]
                        )
                    );
                } catch (PDOException $e) {
                    echo 'please make sure that called redis exists with subject,predicate,object' . "\n";
                    echo 'insert as first line : ' . $userData['uri'] . " , " . GenerisRdf::PROPERTY_USER_LOGIN . " , " . $userData[GenerisRdf::PROPERTY_USER_LOGIN];
                    die(1);
                }
            }


        }
        $row++;
    }
    fclose($handle);
}
