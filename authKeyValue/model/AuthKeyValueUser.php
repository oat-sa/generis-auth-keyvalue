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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
namespace oat\authKeyValue\model;

use common_user_User;
use core_kernel_classes_Resource;
use core_kernel_classes_Property;
use common_Logger;

/**
 * User retrieved from key-value storage
 * 
 * @author Christophe Massin <christope@taotesting.com>
 */
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