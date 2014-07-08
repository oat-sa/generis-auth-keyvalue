<?php
/**
 * Created by PhpStorm.
 * User: christophemassin
 * Date: 4/07/14
 * Time: 10:49
 */

namespace oat\authKeyValue\test;

use oat\authKeyValue\model\AuthKeyValueUser;
use GenerisPhpUnitTestRunner;

require_once dirname(__FILE__) . '/../../generis/test/GenerisPhpUnitTestRunner.php';

class AuthKeyValueUserTest extends GenerisPhpUnitTestRunner {

    public function setUp() {
        $this->user = new AuthKeyValueUser();
    }


    public function testPropertyValue()
    {
        $languageProperty = 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US';

        $this->user->setLanguage($languageProperty);

        $lang = $this->user->getLanguage();

        $this->assertNotEmpty($lang);
        $this->assertInternalType('array', $lang);
        $this->assertEquals(array('en-US'), $this->user->getLanguage());
        $this->assertEquals(array(0 => 'en-US'), $this->user->getPropertyValues(PROPERTY_USER_DEFLG));
        $this->assertEquals(array(0 => 'en-US'), $this->user->getPropertyValues(PROPERTY_USER_UILG));

    }

    public function testRoles()
    {
        $this->user->setRoles(array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'));
        $this->assertEquals(array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'), $user->getRoles());
        $this->assertEquals(array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'), $user->getPropertyValues(PROPERTY_USER_ROLES));
    }


}
 