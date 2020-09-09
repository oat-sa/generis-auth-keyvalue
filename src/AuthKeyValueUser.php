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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

/**
 * Authentication user for key value db access
 *
 * @author christophe massin
 * @package authKeyValue

 */


namespace oat\authKeyValue;
use common_user_User;
use core_kernel_classes_Resource;
use core_kernel_classes_Property;
use common_Logger;
use Exception;
use oat\generis\model\OntologyRdf;
use oat\generis\model\GenerisRdf;
use oat\oatbox\service\ServiceManager;

class AuthKeyValueUser extends common_user_User {

    /**
     * Max size of a property to store in the session in characters
     *
     * @var int
     */
    const DEFAULT_MAX_CACHE_SIZE = 1000;

    /** @var  array of configuration */
    protected $configuration;

    /**
     * @var array
     */
    protected $userRawParameters = array();

    /**
     * @var array
     */
    protected $userExtraParameters = array();

    /**
     * @var string
     */
    protected $identifier;

    /**
     * Array that contains the language code as a single string
     *
     * @var array
     */
    protected $languageUi = array(DEFAULT_LANG);

    /**
     * Array that contains the language code as a single string
     *
     * @var array
     */
    protected $languageDefLg = array(DEFAULT_LANG);

    /**
     * @param array $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Sets the language URI
     *
     * @param string $languageDefLgUri
     */
    public function setLanguageDefLg($languageDefLgUri)
    {
        $languageResource = new core_kernel_classes_Resource($languageDefLgUri);

        $languageCode = $languageResource->getUniquePropertyValue(new core_kernel_classes_Property(OntologyRdf::RDF_VALUE));
        if($languageCode) {
            $this->languageDefLg = array((string)$languageCode);
        }

        return $this;
    }

    /**
     * Returns the language code
     *
     * @return array
     */
    public function getLanguageDefLg()
    {
        return $this->languageDefLg;
    }

    /**
     * @param array $userExtraParameters
     */
    public function setUserExtraParameters(array $userExtraParameters)
    {
        $this->userExtraParameters = $userExtraParameters;
    }

    /**
     * @return array
     */
    public function getUserExtraParameters()
    {
        return $this->userExtraParameters;
    }

    /**
     * @param array $userRawParameters
     * @return AuthKeyValueUser
     */
    public function setUserRawParameters(array $userRawParameters)
    {
        foreach ($userRawParameters as $key => $value) {
            $this->userRawParameters[$key] = is_array($value) ? $value : array($value);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getUserRawParameters()
    {
        return $this->userRawParameters;
    }


    /**
     * @param mixed $language
     */
    public function setLanguageUi($languageUri)
    {
        $languageResource = new core_kernel_classes_Resource($languageUri);

        $languageCode = $languageResource->getUniquePropertyValue(new core_kernel_classes_Property(OntologyRdf::RDF_VALUE));
        if($languageCode) {
            $this->languageUi = array((string)$languageCode);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getLanguageUi()
    {
        return $this->languageUi;
    }


    /**
     * @return string
     */
    public function getIdentifier(){
        return $this->identifier;
    }

    /**
     * @param $identifier
     * @return $this
     */
    public function setIdentifier($identifier){
        $this->identifier = $identifier;

        return $this;
    }


    /**
     * @param $property string
     * @return array|null
     */
    public function getPropertyValues($property)
    {
        $returnValue = array();

        $userParameters = $this->getUserRawParameters();

        if( !empty($userParameters) && array_key_exists($property, $userParameters))
        {
            switch ($property) {
                case GenerisRdf::PROPERTY_USER_DEFLG :
                    $returnValue = $this->getLanguageDefLg();
                    break;
                case GenerisRdf::PROPERTY_USER_UILG :
                    $returnValue = $this->getLanguageUi();
                    break;
                default:
                    $returnValue = $userParameters[$property];
            }
        } else {
            $extraParameters = $this->getUserExtraParameters();
            // the element has already been accessed
            if(!empty($extraParameters) && array_key_exists($property, $extraParameters)){
                if(!is_array($extraParameters[$property])){
                    $returnValue = array($extraParameters[$property]);
                } else {
                    $returnValue = $extraParameters[$property];
                }

            } else {
                // not already accessed, we are going to get it.
                $login = reset($userParameters[GenerisRdf::PROPERTY_USER_LOGIN]);
                $value = $this->getAuthKeyValueUserService()->getUserParameter($login, $property);

                if (!empty($value)) {
                    if( strlen(base64_encode(serialize($value))) < $this->getMaxCacheSize() ) {
                        $extraParameters[$property] = $value;
                        $this->setUserExtraParameters($extraParameters);
                    }
                    $returnValue = array($value);
                }
            }
        }

        return $returnValue;
    }


    /**
     * Function that will refresh the parameters.
     */
    public function refresh() {
        $this->setUserExtraParameters(null);

        $userData = $this->getAuthKeyValueUserService()->getUserData(
            $this->getPropertyValues(GenerisRdf::PROPERTY_USER_LOGIN)
        );

        $params = json_decode($userData[AuthKeyValueUserService::USER_PARAMETERS],true);
        $this->setUserRawParameters($params);
    }

    protected function getPersistenceId() {
        $config = $this->getConfiguration();
        return isset($config[AuthKeyValueAdapter::OPTION_PERSISTENCE])
            ? $config[AuthKeyValueAdapter::OPTION_PERSISTENCE]
            : AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID;
    }

    protected function getMaxCacheSize() {
        $config = $this->getConfiguration();
        return isset($config['max_size_cached_element']) ? $config['max_size_cached_element'] : self::DEFAULT_MAX_CACHE_SIZE;
    }

    /**
     * @return AuthKeyValueUserService
     */
    protected function getAuthKeyValueUserService()
    {
        return ServiceManager::getServiceManager()->get(AuthKeyValueUserService::SERVICE_ID);
    }
}
