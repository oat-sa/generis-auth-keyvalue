<?php
/**
 * Created by PhpStorm.
 * User: christophemassin
 * Date: 4/07/14
 * Time: 10:49
 */

namespace oat\authKeyValue\test;

use oat\authKeyValue\model\AuthKeyValueAdapter;
use oat\authKeyValue\model\AuthKeyValueUser;
use GenerisPhpUnitTestRunner;
use common_session_SessionManager;
use common_persistence_AdvKeyValuePersistence;
use core_kernel_users_Service;
use common_Utils;

require_once dirname(__FILE__) . '/../../generis/test/GenerisPhpUnitTestRunner.php';

class AuthKeyValueAdapterTest extends GenerisPhpUnitTestRunner {


    protected $adapter;
    protected $login;
    protected $password;

    public function setUp() {

        $this->login = 'helloworld1';
        $this->password = 'password1';

        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);
        $user = $kvStore->getDriver()->hGetAll($this->login);

        if ( ! $user ){

            $uri = \common_Utils::getNewUri();
            $kvStore->getDriver()->hset($this->login,PROPERTY_USER_PASSWORD, '');
            $kvStore->getDriver()->hset($this->login,
                'parameters',
                json_encode(array(
                    "uri" => $uri,
                    "http://www.w3.org/2000/01/rdf-schema#label" => "Test taker 1",
                    "http://www.tao.lu/Ontologies/generis.rdf#userUILg" => "http://www.tao.lu/Ontologies/TAO.rdf#Langen-US",
                    "http://www.tao.lu/Ontologies/generis.rdf#userDefLg" => "http://www.tao.lu/Ontologies/TAO.rdf#Langen-US",
                    "http://www.tao.lu/Ontologies/generis.rdf#login" => $this->login,
                    "http://www.tao.lu/Ontologies/generis.rdf#password" => core_kernel_users_Service::getPasswordHash()->encrypt($this->password),
                    "http://www.tao.lu/Ontologies/generis.rdf#userRoles" =>
                        ["http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole"],
                    "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "Testtaker 1",
                    "http://www.tao.lu/Ontologies/generis.rdf#userLastName"=>"Family 047"
                ))
            );
        }

        $config = array('max_size_cached_element' => 10000);
        $this->adapter = new AuthKeyValueAdapter($config);
        $this->adapter->setCredentials($this->login,$this->password);
    }


    /**
     * @cover AuthKeyValueAdapter::authenticate
     */
    public function testAuthenticate()
    {
        $user = $this->adapter->authenticate();
        $this->assertEquals( $user->getPropertyValues(PROPERTY_USER_LOGIN), array($this->login));
    }


    /**
     * @cover AuthKeyValueAdapter::getConfiguration
     */
    public function testGetConfiguration()
    {
        $config = $this->adapter->getConfiguration();

        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('max_size_cached_element', $config);
        $this->assertEquals(10000, $config['max_size_cached_element']);
    }

    /**
     * @cover AuthKeyValueAdapter::setConfiguration
     */
    public function testSetConfiguration()
    {
        $this->adapter->setConfiguration( array('pika' => 'tchu'));
        $config = $this->adapter->getConfiguration();

        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('pika', $config);
        $this->assertEquals('tchu', $config['pika']);
    }



    public function tearDown()
    {
        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);

        $kvStore->getDriver()->del($this->login);
    }

}
 