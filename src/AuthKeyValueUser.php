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
use oat\generis\model\OntologyRdf;
use oat\generis\model\GenerisRdf;
use oat\oatbox\service\ServiceManager;
use oat\generis\model\kernel\users\UserInternalInterface;

class AuthKeyValueUser extends common_user_User implements UserInternalInterface{

    /**
     * Max size of a property to store in the session in characters
     *
     * @var int
     */
    const DEFAULT_MAX_CACHE_SIZE = 1000;

    protected array $configuration;

    protected array $userRawParameters = array();

    protected array $userExtraParameters = array();

    protected string $identifier;

    /**
     * Array that contains the language code as a single string
     */
    protected array $languageUi = array(DEFAULT_LANG);

    /**
     * Array that contains the language code as a single string
     */
    protected array $languageDefLg = array(DEFAULT_LANG);

    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * Sets the language URI
     */
    public function setLanguageDefLg(string $languageDefLgUri)
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
     */
    public function getLanguageDefLg(): array
    {
        return $this->languageDefLg;
    }

    public function setUserExtraParameters(array $userExtraParameters)
    {
        $this->userExtraParameters = $userExtraParameters;
    }

    public function getUserExtraParameters(): array
    {
        return $this->userExtraParameters;
    }

    public function setUserRawParameters(array $userRawParameters): AuthKeyValueUser
    {
        foreach ($userRawParameters as $key => $value) {
            $this->userRawParameters[$key] = is_array($value) ? $value : array($value);
        }

        return $this;
    }

    public function getUserRawParameters(): array
    {
        return $this->userRawParameters;
    }

    public function getLanguageUiFromParams($params): array
    {
        if (isset($params[GenerisRdf::PROPERTY_USER_UILG])) {
            $languageResource = new core_kernel_classes_Resource($params[GenerisRdf::PROPERTY_USER_UILG]);
            $languageCode = $languageResource->getUniquePropertyValue(new core_kernel_classes_Property(OntologyRdf::RDF_VALUE));
            return [(string)$languageCode];
        }
        return [];
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getPropertyValues($property): ?array
    {
        $returnValue = array();

        $userParameters = $this->getUserRawParameters();

        if( !empty($userParameters) && array_key_exists($property, $userParameters))
        {
            try {
                $returnValue = match ($property) {
                    GenerisRdf::PROPERTY_USER_DEFLG => $this->getLanguageDefLg(),
                    GenerisRdf::PROPERTY_USER_UILG => $this->getLanguageUiFromParams($this->getKeyValueUserData()),
                    default => $userParameters[$property],
                };
            }  catch (\UnhandledMatchError $e) {
                $returnValue = $userParameters[$property];
            }
           
        } else {
            $extraParameters = $this->getUserExtraParameters();
            // the element has already been accessed
            if(!empty($extraParameters) && array_key_exists($property, $extraParameters)){
                $returnValue = array($extraParameters[$property]);
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

            if (!empty($returnValue)) {
                if (is_array($returnValue)) {
                    $current = current($returnValue);
                    $returnValue = $current;
                    if (is_string($current)) {
                        $returnValue = json_decode($current, true);
                    }
                }
                if (!is_array($returnValue)) {
                    $returnValue = [$returnValue];
                }
            }
        }

        return $returnValue;
    }

    /**
     * Function that will refresh the parameters.
     */
    public function refresh(): void
    {
        $this->setUserExtraParameters([]);
        $this->setUserRawParameters($this->getKeyValueUserData());
    }

    protected function getMaxCacheSize(): int
    {
        $config = $this->getConfiguration();
        return isset($config['max_size_cached_element']) ? $config['max_size_cached_element'] : self::DEFAULT_MAX_CACHE_SIZE;
    }

    protected function getAuthKeyValueUserService(): AuthKeyValueUserService
    {
        return ServiceManager::getServiceManager()->get(AuthKeyValueUserService::SERVICE_ID);
    }

    private function getKeyValueUserData(): array
    {
        $params = [];
        $login = current($this->getPropertyValues(GenerisRdf::PROPERTY_USER_LOGIN));
        
        if ($login) {
            $userData = $this->getAuthKeyValueUserService()->getUserData($login);
            if (isset($userData[AuthKeyValueUserService::USER_PARAMETERS])) {
                $params = json_decode($userData[AuthKeyValueUserService::USER_PARAMETERS],true);
            }
        }
        return $params;
    }
}
