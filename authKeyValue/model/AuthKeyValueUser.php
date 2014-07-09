<?php
/**
 * Created by PhpStorm.
 * User: christophemassin
 * Date: 1/07/14
 * Time: 13:22
 */

namespace oat\authKeyValue\model;
use common_user_User;
use core_kernel_classes_Resource;
use core_kernel_classes_Property;
use common_Logger;

class AuthKeyValueUser extends common_user_User {

    protected $userRawParameters;

    protected $userExtraParameters = array();

    protected $identifier;

    /** @var  array $roles */
    protected $roles;


    protected $languageUi = array(DEFAULT_LANG);

    protected $languageDefLg = array(DEFAULT_LANG);

    /**
     * @param array $languageDefLg
     */
    public function setLanguageDefLg($languageDefLg)
    {
        $languageResource = new core_kernel_classes_Resource($languageDefLg);

        $languageCode = $languageResource->getUniquePropertyValue(new core_kernel_classes_Property(RDF_VALUE));
        if($languageCode) {
            $this->languageDefLg = array((string)$languageCode);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getLanguageDefLg()
    {
        return $this->languageDefLg;
    }

    /**
     * @param mixed $userExtraParameters
     */
    public function setUserExtraParameters($userExtraParameters)
    {
        $this->userExtraParameters = $userExtraParameters;
    }

    /**
     * @return mixed
     */
    public function getUserExtraParameters()
    {
        return $this->userExtraParameters;
    }



    /**
     * @param mixed $userRawParameters
     */
    public function setUserRawParameters($userRawParameters)
    {
        $this->userRawParameters = $userRawParameters;

        return $this;
    }

    /**
     * @return mixed
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

        $languageCode = $languageResource->getUniquePropertyValue(new core_kernel_classes_Property(RDF_VALUE));
        if($languageCode) {
            $this->languageUi = array((string)$languageCode);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLanguageUi()
    {
        return $this->languageUi;
    }


    public function getIdentifier(){
        return $this->identifier;
    }

    public function setIdentifier($identifier){
        $this->identifier = $identifier;

        return $this;
    }


    public function getPropertyValues($property)
    {
        $returnValue = null;

        $userParameters = $this->getUserRawParameters();

        if(array_key_exists($property, $userParameters))
        {
            switch ($property) {
                case PROPERTY_USER_DEFLG :
                    $returnValue = $this->getLanguageDefLg();
                    break;
                case PROPERTY_USER_UILG :
                    $returnValue = $this->getLanguageUi();
                    break;
                case PROPERTY_USER_ROLES :
                    $returnValue = $this->getRoles();
                    break;
                default:
                    $returnValue = array($userParameters[$property]);
            }
        }
        else {
            $extraParameters = $this->getUserExtraParameters();
            // the element has already been accessed
            if(!empty($extraParameters) && array_key_exists($property, $extraParameters)){
                $returnValue = array($extraParameters[$property]);
            }
            // not already accessed, we are going to get it.
            else {
                $serviceUser = new AuthKeyValueUserService();
                $key = AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID.':'.$userParameters[PROPERTY_USER_LOGIN].':'.$property ;
                $parameter = $serviceUser->getUserParameter($key, $property);
                $extraParameters[$property] = $parameter;

                $this->setUserExtraParameters($extraParameters);

                $returnValue = array($parameter);
            }

        }

        return $returnValue;
    }


    public function refresh() {

    }


    public function getRoles() {
        return $this->roles;
    }

    public function setRoles(array $roles ) {
        $this->roles = $roles;

        return $this;
    }

} 