<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: builtinModule
  @file : pTestFormule.php
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
  ------------------------------------------------------------------------------ */

class pTestFormule extends mmProgProcedural {

    public function principale($action = '', $parametres = null) {
        //test de decoupage
//    $exp = 'si(1 =18*15+2-(15/3)?10*(18+(15-8))/5:func(42.25, 18, "toto,tata,zubub"))+int(28.12)';//__chargeExpression('11532');
//    $exp = '(1+("2"+3)*4)+5*-6+8/3';
        $exp = '"2"+3+"toto"';
        $form = new mmForm();
        $form->addWidget(new mmWidgetTextArea('formule', '', array('cols' => 40, 'rows' => 8)));
        $form->addWidget(new mmWidgetTextArea('variables', '', array('cols' => 40, 'rows' => 8)));
        $form->addWidget(new mmWidgetSelect('interprete', array('0' => 'Non', '1' => 'Oui')));
        $form->addWidget(new mmWidgetSelect('debug', array('0' => 'Non', '1' => 'Oui')));
        $form->addWidget(new mmWidgetButtonSubmit());
        $form->addWidget(new mmWidgetButtonHtmlPopup('aide', 'aide/aideFormule.php'));
        if (count($_POST) > 0) {
            $vars = stringToArray($_POST['variables']);
            $form['variables']->setValue($_POST['variables']);
            $form['formule']->setValue($_POST['formule']);
            $form['interprete']->setValue($_POST['interprete']);
            $form['debug']->setValue($_POST['debug']);
            $exp = $_POST['formule'];
            echo $form->start() . $form . "</form>";
            $calc = new mmExpression($exp, $vars, $_POST['interprete']);
            $calc->debug($_POST['debug']);
            $resultat = $calc->calcul();
            echo "<h1>resultat final= {$resultat[1]}({$resultat[0]})</h1>";
        } else {
            echo $form->start() . $form . "</form>";
        }
    }

}