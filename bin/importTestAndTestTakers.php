<?php

use oat\authKeyValue\AuthKeyValueUserService;
use oat\authKeyValue\helpers\DataGeneration;
use oat\taoGroups\models\GroupsService;

//----------- Set parameters
$parms = $argv;
array_shift($parms);
if (count($parms) < 3 || count($parms) > 4) {
    echo 'Usage: '.__FILE__.' TAOROOT CSVFILE TEST_PACKAGE [LANG] [GROUPURI]'.PHP_EOL;
    die(1);
}
$root = rtrim(array_shift($parms), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
$csvfile = array_shift($parms);
$test_package = array_shift($parms);
$lang = empty($parms) ? null : array_shift($parms);
$groupUri = empty($parms) ? null : array_shift($parms);

//----------- Bootstrap
$rawStart = $root.'tao'.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'raw_start.php';
if (!file_exists($rawStart)) {
    echo 'Tao not found at "'.$rawStart.'"'.PHP_EOL;
    die(1);
}
require_once $rawStart;
require_once $root.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

common_ext_ExtensionsManager::singleton()->getExtensionById('taoDeliveryRdf');
common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');

//----------- Check files
if (!file_exists($csvfile)) {
    echo 'Csv file not found at "' . $csvfile . '"' . PHP_EOL;
    die(1);
}
if (!file_exists($test_package)) {
    echo 'Test package file not found at "' . $test_package . '"' . PHP_EOL;
    die(1);
}

//----------- Create GROUP
if (is_null($groupUri)) {
    $label = 'Group ' . uniqid();
    $groupClass = new \core_kernel_classes_Class(TAO_GROUP_CLASS);
    $group = $groupClass->createInstanceWithProperties(
        array(
            RDFS_LABEL => $label
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

//----------- Create "redis" table
/** @var \common_persistence_SqlPersistence $persistence */
$persistence = \common_persistence_Manager::getPersistence('default');

try{
    /** @var \common_persistence_sql_pdo_mysql_SchemaManager $schemaManager */
    $schemaManager = $persistence->getDriver()->getSchemaManager();
    /** @var \Doctrine\DBAL\Schema\Schema $schema */
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
        echo 'Table named redis created.' . PHP_EOL;
    }

} catch(\Doctrine\DBAL\Schema\SchemaException $e) {
    \common_Logger::i('Database Schema already up to date.');
}

//----------- Import test takers from CSV
$expected = array(
    'login' => PROPERTY_USER_LOGIN,
    'password' => PROPERTY_USER_PASSWORD,
);
$keys = array_keys($expected);
$userService = new AuthKeyValueUserService();

$row = 1;
if (($handle = fopen($csvfile, "r")) !== false) {
    echo 'Importing test takers...';
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
            $toAdd[PROPERTY_USER_PASSWORD] = core_kernel_users_Service::getPasswordHash()->encrypt(
                $toAdd[PROPERTY_USER_PASSWORD]
            );

            if ($userService->getUserData($toAdd[PROPERTY_USER_LOGIN]) != false) {
                echo 'User "' . $toAdd[PROPERTY_USER_LOGIN] . '" already exists.' . PHP_EOL;
            } else {
                $userData = DataGeneration::createUser($toAdd, $lang);

                try {
                    $persistence->insert(
                        'redis',
                        array(
                            'subject' => $userData['uri'],
                            'predicate' => PROPERTY_USER_LOGIN,
                            'object' => $userData[PROPERTY_USER_LOGIN]
                        )
                    );
                    // insert user data to statements as well to be able to show the user label on the results page
                    $persistence->insert(
                        'statements',
                        array(
                            'modelid' => 1,
                            'subject' => $userData['uri'],
                            'predicate' => RDFS_LABEL,
                            'object' => $userData[PROPERTY_USER_LOGIN],
                            'l_language' => 'en-US'
                        )
                    );
                } catch (PDOException $e) {
                    echo 'please make sure that called redis exists with subject,predicate,object' . "\n";
                    echo 'insert as first line : ' . $userData['uri'] . " , " . PROPERTY_USER_LOGIN . " , " . $userData[PROPERTY_USER_LOGIN];
                    die(1);
                }
            }
        }
        $row++;
    }
    fclose($handle);

    echo PHP_EOL . $row . ' test takers imported.' . PHP_EOL;
}

//----------- Import test from ZIP package
echo 'Importing test...' . PHP_EOL;
$report =   \taoQtiTest_models_classes_QtiTestService::singleton()->importMultipleTests(
                new \core_kernel_classes_Class("http://www.tao.lu/Ontologies/TAOTest.rdf#Test"),
                $test_package
            );

foreach($report as $r) {
    $test = $r->getData()->rdfsResource;
}

$label = __("Benchmark test");
$deliveryClass = new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAODelivery.rdf#AssembledDelivery');
$report = \oat\taoDeliveryRdf\model\SimpleDeliveryFactory::create($deliveryClass, $test, $label);
$delivery = $report->getData();
$property = new \core_kernel_classes_Property(PROPERTY_GROUP_DELVIERY);
$group->setPropertyValue($property, $delivery);

echo 'Test successfully imported.' . PHP_EOL;
