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
  @file : mmWidgetSelectFic.php
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

class mmWidgetSelectFic extends mmWidgetSelect {

    protected
            $values,
            $table,
            $colLibelle;
    /**
     * Create a new select widget based on data from the database
     * @param mixed $name if is a string represent the widget name. If an instance of mmWidget return the new widget based on $name properties
     * @param string $table table name in the database
     * @param string $key column (in table) used as select value
     * @param string $value default value
     * @param string $label column (in table) used as option label
     * @param string $condition WHERE condition to select subset of results
     * @param type $attributes extra attribute @mmWidgetSelect
     * @return \mmWidgetBlank|\mmWidgetSelectFic
     * @throws Doctrine_Exception
     */

    public function __construct($name, $table, $key, $value = '', $label = '', $condition = '', $attributes = array()) {

        //verification des information fournis
        if ($table == '') {
            mmUser::flashError("$name: parametre <b>Table=nom table</b> manquant");
            return new mmWidgetBlank($name);
        }

        try {
            $values = $this->recupValues($table, $key, $label, $condition);
        } catch (Doctrine_Exception $e) {
            if ($name instanceof mmWidget) {
                $fieldName = $name->getName();
            } else {
                $fieldName = $name;
            }
            $code = $e->getCode();
            switch ($code) {
                case 0:
                    mmUser::flashError("$fieldName erreur d'acces aux données: la table $table n'existe pas.");
                    break;
                case 42:
                    mmUser::flashError("$fieldName nom de colonne inconnue dans le parametre cle ou libelle");
                    break;
                case 42000:
                    mmUser::flashError("$fieldName erreur de parametrage de la cle");
                    break;
                default:
                    mmUser::flashError("Erreur inconnue code $code");
                    if (DEBUG) {
                        throw $e;
                    }
                    break;
            }
            $values = array();
        }
        $this->table = $table;

        parent::__construct($name, $values, $value, $attributes);
        if (get_class($this) == 'mmWidgetSelectFic') {
            unset($this->attributes['size']);
        }

        return $this;
    }

    public function render($extraAttributes = array(), $replace = false) {
        $result = parent::render($extraAttributes, $replace);
        return $result;
    }

    protected function recupValues($table, $cle, $colLibelle = '', $condition = '') {
        $result = array('' => '-');
        //verification des parametres
        if ($cle == '') {
            $cle = mmSQL::getCleUnique($table, true);
        }
        if ($condition == '') {
            $condition = '1';
        }
        if ($colLibelle == '') {
            $colLibelle = $cle;
        }
        $select = $cle . " AS cle," . mmParseSqlConcat($colLibelle, $table);
        $resultatBase = Doctrine_Core::getTable($table)->createQuery()->
                select($select)->
                where($condition)->
                fetchArray();
        $nomLibelle = mmParseSqlConcat($colLibelle, $table, true);
        foreach ($resultatBase as $ligne) {
            $result[$ligne['cle']] = $ligne[$nomLibelle];
        }

        return $result;
    }

}