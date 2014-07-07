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

require_once dirname(__FILE__) . '/../../generis/test/GenerisPhpUnitTestRunner.php';

class AuthKeyValueAdapterTest extends GenerisPhpUnitTestRunner {


    protected $adapter;
    protected $login;
    protected $password;

    public function setUp() {

        $this->login = 'tt1';
        $this->password = 'pass1';

        $this->adapter = new AuthKeyValueAdapter($this->login,$this->password);
    }


    public function testAuthenticate()
    {

        $this->adapter->authenticate();
        $session = \common_session_SessionManager::getSession();

        $this->assertEquals( $session->getUserPropertyValues(PROPERTY_USER_LOGIN), array($this->login));
    }


}
 