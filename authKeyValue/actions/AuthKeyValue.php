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

namespace oat\authKeyValue\actions;

use common_persistence_AdvKeyValuePersistence;
use oat\authKeyValue\model\AuthKeyValueAdapter;
use tao_models_classes_UserService;
use core_kernel_classes_Class;
use tao_helpers_Scriptloader;
use tao_actions_form_Login;

/**
 * Sample controller
 *
 * @author Open Assessment Technologies SA
 * @package authKeyValue
 * @subpackage actions
 * @license GPL-2.0
 *
 */
class AuthKeyValue extends \tao_actions_CommonModule {

    /**
     * initialize the services
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * A possible entry point to tao
     */
    public function index() {

        //add the login stylesheet
        tao_helpers_Scriptloader::addCssFile(TAOBASE_WWW . 'css/login.css');

        $params = array();
        if ($this->hasRequestParameter('redirect')) {
            $redirectUrl = $_REQUEST['redirect'];

            if (substr($redirectUrl, 0,1) == '/' || substr($redirectUrl, 0, strlen(ROOT_URL)) == ROOT_URL) {
                $params['redirect'] = $redirectUrl;
            }
        }
        $myLoginFormContainer = new tao_actions_form_Login($params);
        $myForm = $myLoginFormContainer->getForm();

        if($myForm->isSubmited()){
            if($myForm->isValid()){
                $adapter = new AuthKeyValueAdapter(array());
                $adapter->setCredentials($myForm->getValue('login'), $myForm->getValue('password'));
                $adapter->authenticate();

                if ($this->hasRequestParameter('redirect')) {
                    $this->redirect($_REQUEST['redirect']);
                } else {
                    $this->redirect(_url('entry', 'Main','tao'));
                }
            }
        }

        $this->setData('form', $myForm->render());
        $this->setData('title', __("TAO Login"));
        if ($this->hasRequestParameter('msg')) {
            $this->setData('msg', htmlentities($this->getRequestParameter('msg')));
        }
        $this->setView('main/login.tpl', 'tao');
    }

    public function templateExample() {
        $this->setData('author', 'Open Assessment Technologies SA');
        $this->setView('sample.tpl');
    }
}