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
  @file : mmWidgetBlank.php
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

class mmWidgetBlank extends mmWidget {

    public function __construct($name) {
        parent::__construct($name, '');
    }

    public function render() {
        return '';
    }

    public function renderPdf() {
        return '';
    }

    public function renderAdminMenu() {
        return '';
    }

    public function renderHelp() {
        return '';
    }

    public function renderErrors() {
        return '';
    }

    public function renderRow() {
        return '';
    }

    public function renderText() {
        return '';
    }

    public function renderTextRow() {
        return '';
    }

}