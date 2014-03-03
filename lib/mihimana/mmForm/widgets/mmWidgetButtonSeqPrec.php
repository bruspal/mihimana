<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmForm/widgets
  @file : mmWidgetButtonSeqPrec.php
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

class mmWidgetButtonSeqPrec extends mmWidgetButtonGoPage {

    public function __construct($libelle, $nomSeq, $actionClick = '', $name = '', $attributs = array()) {
        if ($libelle == '') {
            $libelle = 'Pr&eacute;c&eacute;dent';
        }
        $sequence = new mmSequence($nomSeq);
        $estPremier = $sequence->isPremier();
        $ecran = $sequence->getPrecNumeric();
        if ($actionClick == '') {
            $url = sprintf('?module=%s&action=%s&e=%d', MODULE_COURANT, ACTION_COURANTE, $ecran);
        } else {
            $url = sprintf($actionClick, $ecran);
        }

        parent::__construct($libelle, $url, false, $name, $attributs);

        if ($estPremier) {
            $this->disable(false);
        }
    }

}