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

    }


    public function testPropertyValue()
    {
        $user = new AuthKeyValueUser();
        $languageProperty = 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US';

        $user->setLanguage($languageProperty);

        $lang = $user->getLanguage();

        $this->assertNotEmpty($lang);
        $this->assertInternalType('array', $lang);
        $this->assertEquals(array('en-US'), $user->getLanguage());
        $this->assertEquals(array(0 => 'en-US'), $user->getPropertyValues(PROPERTY_USER_DEFLG));
        $this->assertEquals(array(0 => 'en-US'), $user->getPropertyValues(PROPERTY_USER_UILG));


        $user->setRoles(array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'));
        $this->assertEquals(array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'), $user->getRoles());
        $this->assertEquals(array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'), $user->getPropertyValues(PROPERTY_USER_ROLES));





    }


}
 