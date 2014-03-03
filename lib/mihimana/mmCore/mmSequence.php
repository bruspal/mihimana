<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmCore
  @file : mmSequence.php
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

class mmSequence extends mmObject {

    protected
            $nomSequence,
            $sequence,
            $indexEcran,
            $taille;

    public function __construct($nomSequence, $sequence = false) {
        $this->nomSequence = $nomSequence;

        //On initialise la sequence depuis les parametres ou la session
        if (!isset($_SESSION['sequences'][$nomSequence]['sec'])) {
            if ($sequence !== false) {
                $_SESSION['sequences'][$nomSequence]['sec'] = $sequence;
                $this->sequence = $sequence;
                $_SESSION['sequences'][$nomSequence]['index'] = 0;
                $this->indexEcran = 0;
            } else {
                throw new mmExceptionControl("Séquence, il manque le tableau de séquence lors de l'initialisation");
            }
        } else {
            if ($sequence !== false) {
                $_SESSION['sequences'][$nomSequence]['sec'] = $sequence;
                $this->sequence = $sequence;
                $_SESSION['sequences'][$nomSequence]['index'] = 0;
                $this->indexEcran = 0;
            } else {
                $this->sequence = $_SESSION['sequences'][$nomSequence]['sec'];
                $this->indexEcran = $_SESSION['sequences'][$nomSequence]['index'];
            }
        }

        //Enregistrement des parametres de controle
        $this->taille = count($this->sequence);
    }

    /**
     * Renvois le nom de séquence suivant
     * @return type
     */
    public function getNomEcranCourant() {
        return $this->sequence[$this->indexEcran];
    }

    public function getNomEcran($pos) {
        if ($pos < $this->taille && $pos >= 0) {
            return $this->sequence[$pos];
        } else {
            throw new mmExceptionControl("Sequence : tentative de lecture au de la de l'index");
        }
    }

    public function getNext() {
        if ($this->indexEcran < $this->taille) {
            $this->indexEcran++;
        } else {
            $this->indexEcran = 0;
        }
        if (isset($this->sequence[$this->indexEcran])) {
            return $this->sequence[$this->indexEcran];
        } else {
            return false;
        }
    }

    public function getPrec() {
        if ($this->indexEcran == 0) {
            $this->indexEcran = $this->taille - 1;
        } else {
            $this->indexEcran--;
        }
        if (isset($this->sequence[$this->indexEcran])) {
            return $this->sequence[$this->indexEcran];
        } else {
            return false;
        }
    }

    public function getNextNumeric() {
        if ($this->indexEcran < $this->taille) {
            $this->indexEcran++;
        } else {
            $this->indexEcran = 0;
        }
        if (isset($this->sequence[$this->indexEcran])) {
            return $this->indexEcran;
        } else {
            return false;
        }
    }

    public function getPrecNumeric() {
        if ($this->indexEcran == 0) {
            $this->indexEcran = $this->taille - 1;
        } else {
            $this->indexEcran--;
        }
        if (isset($this->sequence[$this->indexEcran])) {
            return $this->indexEcran;
        } else {
            return false;
        }
    }

    public function isDernier() {
        if ($this->indexEcran == $this->taille - 1) {
            return true;
        } else {
            return false;
        }
    }

    public function isPremier() {
        if ($this->indexEcran == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getIndex() {
        return $this->indexEcran;
    }

    public function setIndex($nvIndex) {
        if ($nvIndex < 0) {
            $nvIndex = 0;
        }
        if ($nvIndex > $this->taille - 1) {
            $nvIndex = $this->taille - 1;
        }
    }

    public function update($numEcran) {
        $_SESSION['sequences'][$this->nomSequence]['index'] = $numEcran;
    }

    public function metAJourSequence($nomEcran) {
        $tempArray = array_flip($this->sequence);
        if (isset($tempArray[$nomEcran])) {
            $this->indexEcran = $tempArray[$nomEcran];
            $_SESSION['sequences'][$this->nomSequence]['index'] = $this->indexEcran;
        } else {
            throw new mmExceptionControl("Tentative de mettre a jour une sequence d'ecran avec un ecran non reconnu: $nomEcran.");
        }
    }

}
