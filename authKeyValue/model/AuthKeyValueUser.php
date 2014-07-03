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


    protected $identifier;

    /** @var  array $roles */
    protected $roles;


    protected $language = array(DEFAULT_LANG);

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
    public function setLanguage($languageUri)
    {
        $languageResource = new core_kernel_classes_Resource($languageUri);

        $languageCode = $languageResource->getUniquePropertyValue(new core_kernel_classes_Property(RDF_VALUE));
        if($languageCode) {
            $this->language = array((string)$languageCode);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
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
        switch ($property) {
            case PROPERTY_USER_DEFLG :
            case PROPERTY_USER_UILG :
                $returnValue = $this->getLanguage();
                break;
            case PROPERTY_USER_ROLES :
                $returnValue = $this->getRoles();
                break;
            default:

                if(isset($this->userRawParameters[$property]))
                    return array($this->userRawParameters[$property]);
                else {
                    common_Logger::d('Unkown property '.$property.' requested from '.__CLASS__);
                    $returnValue = array();
                }
        }
        return $returnValue;
    }


    public function refresh() {

    }


    public function initRoles(){
        $returnValue = array();
        // We use a Depth First Search approach to flatten the Roles Graph.
        foreach ($this->getPropertyValues(PROPERTY_USER_ROLES) as $roleUri){
            $returnValue[] = $roleUri;
            foreach (core_kernel_users_Service::singleton()->getIncludedRoles(new core_kernel_classes_Resource($roleUri)) as $role) {
                $returnValue[] = $role->getUri();
            }
        }
        return array_unique($returnValue);
    }

    public function getRoles() {
        return $this->roles;
    }

    public function setRoles(array $roles ) {
        $this->roles = $roles;

        return $this;
    }

} 