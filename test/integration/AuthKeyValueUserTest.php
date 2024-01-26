<?php
/**
 * Created by PhpStorm.
 * User: christophemassin
 * Date: 4/07/14
 * Time: 10:49
 */

namespace oat\authKeyValue\test\integration;

use oat\authKeyValue\AuthKeyValueUser;
use oat\generis\model\GenerisRdf;
use oat\generis\test\GenerisPhpUnitTestRunner;

class AuthKeyValueUserTest extends GenerisPhpUnitTestRunner {

    protected AuthKeyValueUser $user;

    public function setUp(): void
    {
        $this->user = new AuthKeyValueUser();

        $this->user->setUserRawParameters(
            array(
                "uri" => "http://192.168.33.22/transferAll/test.rdf#i140473436657255010",
                "http://www.w3.org/2000/01/rdf-schema#label" => "Test taker 1",
                "http://www.tao.lu/Ontologies/generis.rdf#userUILg" => "http://www.tao.lu/Ontologies/TAO.rdf#Langen-US",
                "http://www.tao.lu/Ontologies/generis.rdf#userDefLg" => "http://www.tao.lu/Ontologies/TAO.rdf#Langen-US",
                "http://www.tao.lu/Ontologies/generis.rdf#login" => "tt1",
                "http://www.tao.lu/Ontologies/generis.rdf#password" => "JGXEkjgSvAd978b110dffe22d243a2d18e4afe747d82cb6d1863470afc2016b18ecb3173fb",
                "http://www.tao.lu/Ontologies/generis.rdf#userRoles" =>
                    ["http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole"],
                "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "Testtaker 1",
                "http://www.tao.lu/Ontologies/generis.rdf#userLastName"=>"Family 047"
            )
        );

        $this->user->setConfiguration( array('max_size_cached_element' => 10000 ) );
    }

    public function tearDown(): void
    {
        $this->user = new AuthKeyValueUser();
    }


    /**
     * @cover AuthKeyValueUser::getConfiguration
     */
    public function testGetConfigurationKey()
    {
        $config = $this->user->getConfiguration();

        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('max_size_cached_element', $config);
        $this->assertEquals(10000, $config['max_size_cached_element']);
    }


    /**
     * @cover AuthKeyValueUser::setLanguageUi
     * @cover AuthKeyValueUser::getLanguageUi
     * @cover AuthKeyValueUser::setLanguageDefLg
     * @cover AuthKeyValueUser::getLanguageDefLg
     */
    public function testLanguage()
    {
        $languageProperty = 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US';

        $this->user->setLanguageDefLg($languageProperty);

        $langDefLg = $this->user->getLanguageDefLg();
        $this->assertNotEmpty($langDefLg);
        $this->assertInternalType('array', $langDefLg);
        $this->assertEquals(array('en-US'), $this->user->getLanguageDefLg());
    }


    /**
     * @cover AuthKeyValueUser::getPropertyValues
     */
    public function testPropertyValue()
    {
        $this->assertEquals(array(0 => 'en-US'), $this->user->getPropertyValues(GenerisRdf::PROPERTY_USER_DEFLG));
    }


    /**
     * @cover AuthKeyValueUser::getPropertyValues
     */
    public function testLazyLoadForMail(){

        $this->user->setUserExtraParameters(array(GenerisRdf::PROPERTY_USER_MAIL => 'test_email'));

        $this->assertNotEmpty($this->user->getUserExtraParameters());
        $this->assertArrayHasKey(GenerisRdf::PROPERTY_USER_MAIL, $this->user->getUserExtraParameters());
    }


    /**
     * @cover AuthKeyValueUser::getPropertyValues
     */
    public function testLazyLoadForMultiParams(){

        $array = $this->user->getUserExtraParameters();


        // check array is currently empty
        $this->assertEmpty($array);
        $this->user->setUserExtraParameters(array('property' => array('property1', 'property2', 'property3')));

        $this->assertNotEmpty($this->user->getUserExtraParameters());
        $this->assertArrayHasKey('property', $this->user->getUserExtraParameters());
        $this->assertEquals( array('property1', 'property2', 'property3') ,$this->user->getPropertyValues('property'));
    }

}
