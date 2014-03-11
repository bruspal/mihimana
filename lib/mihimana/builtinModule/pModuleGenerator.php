<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: builtinModule
@file : pModuleGenerator.php
-------------------------------------

This file is part of Mihimana.

Mihimana is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Mihimana is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
------------------------------------------------------------------------------*/
class pModuleGenerator extends mmProg{
    
    public function preExecute(\mmRequest $request) {
        if ( ! mmUser::superAdmin()) {
            mmUser::flashError('Access interdit');
            $this->redirect(url('@home'));
        }
    }
    public function executeIndex (mmRequest $request) {
        $this->initForm();
        $datas = array();
        if (isset($request['sn'])) { //sn est fournis ? on préremplis le formulaire
            $datas['module'] = $datas['screen_name'] = $request['sn'];
        }
        $this->form->setValues($datas);
        echo $this->form;
    }
    
    public function executeGenerate (mmRequest $request) {
        $this->initForm();
        $this->form->setValues($request, true);
        if ($this->form->isValid()) {
            switch ($request['methode']) {
                case 'static':
                    $this->generateStatic($request);
                    break;
                case 'dynamic':
                    $this->generateDynamic($request);
                case 'extended':
                    echo '<h1>Pas implemente</h1>';
                default:
                    echo '<h1>Invalide</h1>';
                    break;
            }
            echo "<h1>All done !</h1>".new mmWidgetButtonClose();
        } else {
            echo $this->form;
        }
    }
    
    protected function initForm() {
        $form = new mmForm();
        $form->setAction('@module/generate');
        $form->addWidget(new mmWidgetText('module', '', array('placeholder'=>'module name')));
        $form->addWidget(new mmWidgetText('screen_name', '', array('placeholder' => 'screen name')));
        $form->addWidget(new mmWidgetSelect('methode', array('Static'=>'static', 'dynamic'=>'dynamic', 'Extended'=>'extend')));
        $form->addWidget(new mmWidgetButtonSubmit());
        $form->addWidget(new mmWidgetButtonClose());
        //validator
        $form['module']->addValidation('notnull');
        $form['screen_name']->addValidation('notnull');
        
        $this->form = $form;
    }
    
    /*
     * Protected
     */
    protected function generateStatic(mmRequest $request) {
        
    }
    
    protected function generateDynamic(mmrequest $request) {
        
    }
}
