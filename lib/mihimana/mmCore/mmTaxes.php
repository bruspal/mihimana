<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: mmCore
@file : mmTaxes.php
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



class mmTaxes extends mmObject {

    protected
            $fictax,
            $calcule,
            $resultat;

    public function __construct($numTaxes = false) {
        if ($numTaxes === false) {
            $this->fictax = new fictax();
            $this->fictax['pt04'] = 'P';
            $this->fictax['pt03'] = 0;
            $this->resultat = array('P' => 0, 'F' => 0, 'libelle' => '');
        } else {
            $this->fictax = Doctrine_Core::getTable('fictax')->createQuery()->
                    where('pt01 = ?', $numTaxes)->
                    fetchOne();
            if ($this->fictax === false) {
                throw new mmExceptionControl("la taxes dont le code est $numTaxes n'a pas été trouvé");
            }
            $this->resultat = $this->calculTauxTaxe();
        }
    }

    /**
     * retourne le taux de taxe sous forme d'un tableau
     * 
     * @return type ('P' => taux en %, 'F' => taux fixe, 'libelle' => libelle de la taxe)
     */
    public function calculTauxTaxe() {
        $result = array('P' => 0, 'F' => 0, 'libelle' => '');

        //taxes calculee
        if ($this->fictax['pt04'] == 'C') {
            $idTaxes = explode('+', $this->fictax['pt03']);
            //pour chaque code taxe on fais la somme
            foreach ($idTaxes as $idTaxe) {
                $taxes = new mmTaxes((int) $idTaxes);
                $resTaxes = $taxes->calculTauxTaxe();
                $result['P'] += $resTaxes['P'];
                $result['F'] += $resTaxes['F'];
                $result['libelle'] .= ' + ' . $resTaxes['libelle'];
            }
        }
        //taxe %
        if ($this->fictax['pt04'] == 'P') {
            $result['P'] = $this->fictax['pt03'];
            $result['libelle'] = sprintf("%01.2f %%", $result['P'] * 100);
        }
        //taxe fixe
        if ($this->fictax['pt04'] == 'F') {
            $result['F'] = $this->fictax['pt03'];
            $result['libelle'] = sprintf("%01.2f", $result['F']);
        }

        return $result;
    }

    /**
     * Retourne le montants des taxes applique a $montantHT
     * @param type $montantHT
     * @return int Montant des taxes
     */
    public function calculMontantTaxes($montantHT) {
        $montantTaxes = 0;
        $taux = $this->calculTauxTaxe();

        $montantTaxes = $montantHT * $taux['P'];
        $montantTaxes += $taux['F'];

        return $montantTaxes;
    }

    public function calculMontantTTC($montantHT) {
        return $montantHT + $this->calculMontantTaxes($montantHT);
    }

    /**
     * Cette methode retourne la partie fixe d'une taxe si elle existe, si $taxesUnique = true retourne uniquement le montant des taxes non remboursable 
     */
    public function getPartieFixe($taxesUnique = false) {
        switch ($this->fictax['pt04']) {
            case 'P':
                return 0;
                break;
            case 'F':
                if ($taxesUnique) {
                    if ($this->fictax['pt06']) {
                        return $this->fictax['pt03'];
                    } else {
                        return 0;
                    }
                } else {
                    return $this->fictax['pt03'];
                }
                break;
            case 'C':
                $total = 0;
                $listTaxes = explode('+', $this->fictax['pt03']);
                foreach ($listTaxes as $taxes) {
                    $ssTaxes = Doctrine_Core::getTable('Taxes')->find($taxes);
                    if (!$ssTaxes) {
                        throw new mmExceptionData("Le code taxes $taxes est inconnu");
                    }
                    $total += $ssTaxes->getPartieFixe($taxesUnique);
                }
                return $total;
                break;
            default:
                throw new mmExceptionData("Type de taxes {$this->fictax['pt04']} inconnu");
                break;
        }
    }

}

?>
