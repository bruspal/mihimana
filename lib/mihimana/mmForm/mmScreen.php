<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmForm
  @file : mmScreen.php
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

/**
 * Cette classe va interpreter un template avec les variables au format $nomVar 
 * le template
 * 
 */
class mmScreen extends mmForm {

    //Propriete
    public
            $variablesExtra = array(),
            $exclusionSetValue = array(),
            $listChampsFormule = array(),
            $ecran;
//  protected $htmlResult, $action, $method, $listeChampsTableEcran;
    protected
            $htmlResult,
            $listeChampsTableEcran,
            $destination,
            $appercu = false,
            $_autofocus_ = '';

    /**
     * Construit un nouvel objet ecran vide si aucun parametre n'est fournis. Si $nomEcranOuEcran est fournis l'enregistrement sera chargé dans la table EcranUtilisateur.<br />
     * Sinon si on fournis un objet du type.<br />
     * $donnees contients les données de la base de données<br />
     * $variableExtra est un tableau de varaibles ou un objet se comportant comme un tableau
     * @param type $nomEcranOuEcran
     * @param type $donnees
     * @param array $variablesExtra
     */
    public function __construct($nomEcran, $donnees = null, $variablesExtra = array()) {
        $this->ecran = Doctrine_Core::getTable('EcranUtilisateur')->find($nomEcran);
        if (!$this->ecran) {
            throw new mmExceptionControl("Inpossible de trouver l'ecran $nomEcran");
        }
        //destination de cet ecran, ecran ou impression
        $this->destination = $this->ecran['destination'];
        //On initialise le formulaire
        $this->action = url(MODULE_COURANT.'/'.ACTION_COURANTE);
        $this->method = 'post';
        //On stock les eventuelles variables supplémentaire
        foreach ($variablesExtra as $nomExtra => $extraCourant) {
            $this->addExtraVar($extraCourant, $nomExtra);
        }
        //on stock les eventuelles parametres fournis en POST/GET
        if (count($_REQUEST) > 0) {
            $requestTemp = new mmRequest();
            $requestTemp = $requestTemp->toArray();
            foreach ($requestTemp as $nomParam => $valParam) {
                if (is_array($valParam)) {
                    $this->addExtraVar($valParam);
                } else {
                    $this->addExtraVar(array($nomParam => $valParam));
                }
            }
        }
        //nouvel enregistrement ? ou chargé depuis la base ?
        if ($donnees != null) {
            $this->new = !$donnees->exists();
        } else {
            $this->new = true;
        }
        //fin intialisation des variables
        $this->chargeEcranUtilisateur($nomEcran, $donnees);

        //Lecture et execution de la declaration
        $sourceDeclaration = $this->ecran['declaration'];
        $exp = new mmExpression($sourceDeclaration, $this->getToutesVariables(), $this);
        $exp->calcul();
        $this->variablesExtra = array_merge($this->variablesExtra, $exp->getNvVariables());
    }

    public function metAJourDonneesVivantes(Doctrine_Record $document) {
        $this->new = $document->isNew();
        $this->chargeEcranUtilisateur($document);
    }

    public function addExtraVar($variable, $nomVar = '') {
        if (is_array($variable)) {
            $this->variablesExtra = array_merge($this->variablesExtra, $variable);
        } else {
            if ($variable instanceof Doctrine_Record || $variable instanceof Doctrine_Collection) {
                // c'est un enregistrement de base de données
                $this->variablesExtra[$nomVar] = $variable;
            } else {
                //dans tous les autres cas on stock la pair 'nomvar'=>valeur (identique au cas si dessous mais peut etre modifié par la suite)
                $this->variablesExtra[$nomVar] = $variable;
//        throw new mdExceptionDev ("mdScreen::addExtraVar: le parametre fournis doit etre un tableau, un Doctrine_Record ou une Doctrine_Collection");
            }
        }
//    $this->variablesExtra[] = $variable;
    }

    public function setDestination($destination = false) {
        if ($destination) {
            $this->destination = $destination;
        } else {
            $this->destination = $this->ecran['destination'];
        }
    }

    public function getDestination() {
        return $this->destination;
    }

    /**
     * Compile l'ecran avec les données
     * @return string
     */
    protected function compile($addFormMarkup = true) {


        $source = $this->ecran['template'];
        //On remplace les tag caché par des tags normaux
        $cible = str_replace(array('{@%', '%@}'), array('{%', '%}'), $source);
        //On vire les commentaires
        $cible = preg_replace('#/\*.*\*/#', '', $cible);
        //compilation de la structure
        $cible = $this->compileStructure($cible);

        //compilation des expressions
        $cible = $this->compileExpression($cible);

        //Lors du rendu en mode text, on encapsule.

        if ($this->ecran['mode_rendu'] == 'txt') {
            //on transforme le template lima en template html
            $cible = $this->compile_lima($cible);
        }

        //on compile les variables
        $cible = $this->compileVariables($cible);

        //on interdit les balise php en les supprimant
        $cible = preg_replace('#<\?.*\?>#', '', $cible);

        //rendu des bouton ajouter par defaut
        //TODO: virer les bouton apres coup
//    $cible = $cible.$this->renderButtons();
        //On enferme le rendu entre les balise <form>
        if ($addFormMarkup) {
            $cible = sprintf('<form method="%s" action="%s" id="%s" enctype="%s">%s</form>', $this->method, $this->action, $this->id, $this->enctype, $cible);
        }

        //javascript seulement
        if ($this->destination == 'scr') {
            $cible = $cible . $this->renderJavascript();
        }

        $this->htmlResult = $cible;
        return $cible;
    }

    protected function compileStructure($source) {
        //On recherche les bloc de controle
        $resultat = '';
        $index = 0;
        $longeurSource = strlen($source);
        $posOuvrant = strpos($source, '{%', $index);

        //Positionnement des variables de controle
        $nbSi = 0;
        $conditionSi = '';
        $resultatSi = false;
        $posDebutSi = false;
        $posFinSi = false;
        $posBlocSi = false; //indice du premier character du bloc si
        $posBlocFinsi = false; //position du dernier character d'un si
        $posBlocSinon = false;
        $blocSi = '';

        $nbPourChaque = 0;
        $nbPour = 0;
        $nbTantQue = 0;
        //on met a jour le resultat avec ce qui precede
//    if ($posOuvrant != false)
//    {
//      $resultat .= substr($source, $index, $posOuvrant-$index);
//      $index = $posOuvrant;
//    }
//    else
        if ($posOuvrant === false) {
            return $source;
        }

        while ($posOuvrant !== false && $index < $longeurSource) {
            $posFermant = strpos($source, '%}', $posOuvrant);
            if ($posFermant === false) {
                //on ne ferme pas
                mmUser::flashError('Bloc de commande: \'%}\' manquant');
                return $source;
            }
            //on complete avec ce qu'il y'a entre le pointeur et la nouvel ouverture
            if ($nbSi == 0 && $nbPourChaque == 0 && $nbPour == 0 && $nbTantQue == 0) {
                $resultat .= substr($source, $index, $posOuvrant - $index);
            }
            //on recupere l'expression entre les {% %}
            $debutExpression = $posOuvrant + 2;
            $longueurExpression = $posFermant - $debutExpression;
            $expression = substr($source, $debutExpression, $longueurExpression);
            $expression = trim(htmlspecialchars_decode($expression, ENT_QUOTES));

            //On analyse l'expression
            /*             * *****
             * SI
             * ***** */
            if (preg_match('#^\s*si\(#i', $expression)) {

                $conditionSi = $this->detectSiStructure($expression);
                if (!$conditionSi) {
                    //C'est un si d'expression. On ne fais juste qu'avancer jusqu'a la fin de l'expression
                    $resultat .= substr($source, $posOuvrant, ($posFermant + 2) - $posOuvrant);
                    $index = $posFermant + 2;
                } else {
                    $nbSi++;
                    if ($nbSi == 1) { //ouverture d'un premier si
                        $posDebutSi = $posOuvrant;
                        $posBlocSi = $posFermant + 2;
                        $conditionSi = strip_tags($conditionSi);
                        $exp = new mmExpression($conditionSi, $this->getToutesVariables(), $this);
                        $resultatSi = $exp->retVal($exp->calcul());
                    }
                    $index = $this->nettoieRetourChariot($source, $posFermant + 2);
                }
            }
            /*             * *****
             * SINON
             * ***** */ elseif (strtoupper($expression) == 'SINON') {
                //On a un sinon dans le bloc, on verifie que c'est le sinon du bloc principal
                if ($nbSi < 1) { //bloc deja fermé
                    throw new mmExceptionFormule("SINON trouvé sans SI ouvrant");
                }
                if ($nbSi == 1) {
                    if ($resultatSi) {
                        $posBlocSinon = $posOuvrant; //on se place juste sur le character devant le {% sinon %}
                    } else {
                        $posBlocSinon = $posFermant + 2;  //on se place juste sur le character derriere le {% sinon %}
                    }
                }
                $index = $this->nettoieRetourChariot($source, $posFermant + 2);
            }
            /*             * *****
             * FINSI
             * ***** */ elseif (strtoupper($expression) == 'FINSI') {
                //c'est une fermeture de bloc si
                if ($nbSi < 1) { //on ferme un bloc dejat fermé
                    throw new mmExceptionFormule("FINSI trouvé sans SI ouvrant");
                }
                if ($nbSi == 1) {
                    //On est dans le bloc principal et on le ferme
                    //on extrait le bloc de source correspondant
                    $posFinSi = $posFermant + 2;
                    $posBlocFinsi = $posOuvrant;
                    if ($resultatSi) { //le si renvoie VRAI
                        //traitement du VRAI
                        if ($posBlocSinon) { //On a un sinon dans l'expression on prend entre SI et SINON
                            $blocSi = substr($source, $posBlocSi, $posBlocSinon - $posBlocSi);
                        } else { //On a pas sinon dans l'expression on prend entre SI et FINSI
                            $blocSi = substr($source, $posBlocSi, $posBlocFinsi - $posBlocSi);
                        }
                    } else { //le SI renvoi FAUX
                        if ($posBlocSinon) { //on a un sinon dans l'expression on recupere entre le SINON et le FINSI
                            $blocSi = substr($source, $posBlocSinon, $posBlocFinsi - $posBlocSinon);
                        } else { //la condition est fausse et y'a pas de sinon
                            $blocSi = '';
                        }
                    }
                    //on trim les sauts de ligne avant et apres
//TODO: desactivé pour test          $blocSi = mdTrimNL($blocSi);
                    //On traitemaintenant le bloc si
                    if ($blocSi != '') { //on a un bloc avec du code dedans ? on reparse le bloc pour les eventuelle structures imbriquées.
                        $resRecursion = $this->compileStructure($blocSi);
                        $resRecursion = $this->compileExpression($resRecursion);
                        $resRecursion = $this->compileVariables($resRecursion);

                        $resultat .= $resRecursion;
                    }
                }
                $nbSi--;
                $index = $posFermant + 2;
            }
            /*             * *****
             * POURCHAQUE
             * ***** */ elseif (preg_match('#^\s*pourchaque\s*\(\s*\$(\w+)\s+dans\s+\$(\w+)\s*\)#i', $expression, $fragments)) {
                $nbPourChaque++;
                if ($nbPourChaque == 1) {
                    //on ouvre un pourchaque
                    $posDebutPourChaque = $posOuvrant;
                    $posDebutBlocPourChaque = $posFermant + 2;
                    $nomVarLu = $fragments[1];
                    $nomVarCreer = $fragments[2];
                }
                $index = $this->nettoieRetourChariot($source, $posFermant + 2);
            }
            /*             * *****
             * FINPOURCHAQUE
             * ***** */ elseif (strtoupper($expression) == 'FINPOURCHAQUE') {
                if ($nbPourChaque < 1) {
                    throw new mmExceptionFormule("FINPOURCHAQUE trouvé sans POURCHAQUE");
                }
                if ($nbPourChaque == 1) {
                    if (!isset($this->variablesExtra[$nomVarLu])) {
                        throw new mmExceptionFormule("POURCHAQUE: variable de référence $nomVarLu inconnue");
                    }
                    if (is_scalar($this->variablesExtra[$nomVarLu])) {
                        throw new mmExceptionFormule("POURCHAQUE: variable $nomVarLu de type inconnue. ca doit etre un tableau ou un assimilé aux tableaux");
                    }
                    //on est dans le bloc principal, on effectue donc le traitement
                    $blocPourChaque = mdTrimNL(substr($source, $posDebutBlocPourChaque, $posOuvrant - $posDebutBlocPourChaque));
                    //on genere le code de template qui va bien
                    foreach ($this->variablesExtra[$nomVarLu] as $nomCase => $valeur) {
                        //on met en place l'affectation
                        $this->variablesExtra[$nomVarCreer] = $this->variablesExtra[$nomVarLu][$nomCase];
                        //On a construit un bout de source il faut maintenant le compiler
                        //on traite les éventuels bloc imbriqués
                        $loopCompile = $this->compileStructure($blocPourChaque);
                        $loopCompile = $this->compileExpression($loopCompile);
                        $loopCompile = $this->compileVariables($loopCompile);
                        $resultat .= $loopCompile;
                    }
                }
                $nbPourChaque--;
                $index = $this->nettoieRetourChariot($source, $posFermant + 2);
            }
            /*             * *****
             * POUR
             * ***** */ elseif (preg_match('#\s*pour\(\s*\$(\w+)\s+de\s+(\d+)\s+a\s+(\d+)\s*\)#i', $expression, $fragments)) {
                $nbPour++;
                if ($nbPour == 1) {
                    $nomVarPourCreer = $fragments[1];
                    $valPourDebut = $fragments[2];
                    $valPourFin = $fragments[3];
                    if ($valPourDebut > $valPourFin) {
                        $incrementPour = -1;
                    } else {
                        $incrementPour = 1;
                    }
                    $debutBlocPour = $this->nettoieRetourChariot($source, $posFermant + 2);
                }
                $index = $this->nettoieRetourChariot($source, $posFermant + 2);
            }
            /*             * *****
             * FINPOUR
             * ***** */ elseif (strtoupper($expression) == 'FINPOUR') {
                if ($nbPour < 1) {
                    throw new mmExceptionFormule("FINPOUR trouvé sans POUR ouvert");
                }
                if ($nbPour == 1) {
                    //On traite le bloc
                    $blocPour = substr($source, $debutBlocPour, $posOuvrant - $debutBlocPour);
                    $i = $valPourDebut;
                    $continue = true;
                    while ($continue) {
                        $this->variablesExtra[$nomVarPourCreer] = $i;
                        $loopCompile = $this->compileStructure($blocPour);
                        $loopCompile = $this->compileExpression($loopCompile);
                        $loopCompile = $this->compileVariables($loopCompile);
                        $resultat .= $loopCompile;
                        $i = $i + $incrementPour;
                        if ($i == $valPourFin) {
                            $continue = false;
                        }
                    }
                }
                $nbPour--;
                $index = $this->nettoieRetourChariot($source, $posFermant + 2);
            } else {
                /*                 * *******
                 * Toute autre expression
                 * ******* */
                //ce n'est pas une structure de bloc, on copie tel quel
                $extraction = substr($source, $posOuvrant, ($posFermant + 2) - $posOuvrant);
                //on ne resoud l'expression que si on est pas dans une structure. Si on est dans une structure on delegue
                //la resolution à la recursion
                if ($nbSi == 0 && $nbPourChaque == 0 && $nbPour == 0 && $nbTantQue == 0) {
                    $extraction = $this->compileExpression($extraction);
                    $resultat .= $extraction;
                }
//        $resultat .= $extraction;
                $index = $posFermant + 2;
            }
            //on let a jour les indexs
//      $index = $this->nettoieRetourChariot($source, $posFermant+2);
            //on passe au bloc de commande suivant si il en reste
            $posOuvrant = strpos($source, '{%', $index);
        }
        //On a encore une des structure ouverte ?
        if ($nbSi > 0) {
            throw new mmExceptionFormule("FINSI manquant");
        }
        if ($nbTantQue > 0) {
            throw new mmExceptionFormule("FINTANTQUE manquant");
        }
        if ($nbPourChaque > 0) {
            throw new mmExceptionFormule("FINPOURCHAQUE manquant");
        }
        if ($nbPour > 0) {
            throw new mmExceptionFormule("FINPOUR manquant");
        }
        //on ajoute les eventuelles fragment de sources restant
        $resultat .= substr($source, $index);

        return $resultat;
    }

    /**
     * met a jour index de tel sorte que index pointe sur le premier charactere non retour a la ligne tiens compte des retour a la ligne HTML
     * @param type $source
     * @param type $index
     * @return int
     */
    protected function nettoieRetourChariot($source, $index) {
//    $test = substr($source, $index);
        //on elimine les eventuels espaces blanc (retour chariot, espace, tab et etc)
        while (preg_match('#\s#', @$source[$index])) {
            $index++;
//      $test = substr($source, $index);
        }
//    $test = substr($source, $index);
        //on elimine le <br />si celui ci commence ou suis immédiatement une sequence de séparateurs
        if (preg_match('#<^\s*br\s*/>#i', substr($source, $index), $fragBR)) {
            $index = $index + strlen($fragBR[0]);
//      $test = substr($source, $index);
        }
        //on retourne le nouvel index
        return $index;
    }

    protected function detectSiStructure($expression) {
        //on a un si, mais est-ce un si d'expression ou de structure ?
        $i = 0;
        $sousExp = '';
        $dansChaine = false;
        $abandon = false;
        $niveauParenthese = 0;
        $char = @$expression[$i];
        while ($char != '' && !$abandon) {
            switch ($char) {
                case '"';
                    if (@$expression[$i - 1] != '\\') {
                        //on entre ou on sort d'une chaine
                        $dansChaine = !$dansChaine;
                        $sousExp .= $char;
                    }
                    break;
                case '(':
                    if (!$dansChaine) {
                        $niveauParenthese++;
                    }
                    if ($niveauParenthese > 1) {
                        //on est dans des parenthese imbriquée dans le bloc principal, elle fait partie de la sous exp
                        $sousExp .= $char;
                    } else {
                        $sousExp = ''; //on viens d'ouvrir le si. on commence la sous exp
                    }
                    break;
                case ')':
                    if (!$dansChaine) {
                        $niveauParenthese--;
                    }
                    if ($niveauParenthese > 0) {
                        //on est dans des parenthese imbriquée dans le bloc principal, elle fait partie de la sous exp
                        $sousExp .= $char;
                    }
                    break;
                case '?':
                case ':':
                    //un des separateur du si d'expression ? dans ce cas la c'est un si d'expression et non pas un si de structure
                    if ($niveauParenthese == 1 && !$dansChaine) {
                        //dans ce cas la on a trouvé un ? ou : dans l'expression de niveau 1 (bloc principal du si) on delegue au solveur d'expression
                        $abandon = true;
                        break;
                    } else {
                        $sousExp .= $char;
                    }
                    break;
                default:
                    $sousExp .= $char;
                    break;
            }
            $i++;
            $char = @$expression[$i];
        }

        //si c'est un si de structure, on retourne la condition. sinon on retourne false
        if ($abandon) {
            return false;
        } else {
            return $sousExp;
        }
    }

    protected function compileExpression($source) {
        $resultat = '';
        $tailleSource = strlen($source);
        //premiere chose on cherche le marqueur d'ouverture '{%'
        $index = 0;
        $posOuvrant = strpos($source, '{%', $index);
        while ($posOuvrant !== false) {
            //On commence par verifier si on ferme bien
            $posFermant = strpos($source, '%}', $posOuvrant + 1);
            if ($posFermant === false) {
                //on ne ferme pas
                mmUser::flashError('Bloc de commande: \'%}\' manquant');
                return $source;
            }
            //on met a jour le resultat
            $resultat .= substr($source, $index, $posOuvrant - $index);
            // on extrait l'expression
            if ($source[$posOuvrant + 2] == '=') { //doit t'on afficher le resultat ?
                $affiche = true;
                $debutExpression = $posOuvrant + 3;
            } else {
                $affiche = false;
                $debutExpression = $posOuvrant + 2;
            }
            $longueurExpression = $posFermant - $debutExpression;
            $expression = substr($source, $debutExpression, $longueurExpression);
            //nettoyage de l'expression
            $expression = strip_tags($expression);
            $expression = trim(htmlspecialchars_decode($expression, ENT_QUOTES));

            //on resoud
            $exp = new mmExpression($expression, $this->getToutesVariables(), $this);
            $resExp = $exp->calcul();
            //on recupere les eventuels nouvelle variables
            $this->variablesExtra = array_merge($this->variablesExtra, $exp->getNvVariables());
            //on retransforme en html valide le resultat
            if (is_string($resExp[1])) {
                $resExp[1] = htmlspecialchars($resExp[1], ENT_NOQUOTES);
            }
            //on place le resultat de l'expression dans le resultat final si necessaire, sinon le bloc est effacé
            if ($affiche) {
                $resultat .= $resExp[1];
            }
            //on passe a la suite
            $index = $posFermant + 2;
            if ($index < $tailleSource) {
                $posOuvrant = strpos($source, '{%', $index);
            } else {
                $posOuvrant = false;
            }
        }
        //on termine par completer le resultat en ajoutant la source a partir du dernier '%}'
        $resultat .= substr($source, (int) $index);

        return $resultat;
    }

    protected function compile_lima($source) {
        /*
         * Application des balises de formatage non destructif
         */
        //Mis a jour des polices et etc
        $cible = $source;
        $contenu = explode("\n", $cible);
        $resultFinal = '';
        $hauteurLigne = 23;
        $indice = 0;
        $tableauFormatage = array();
        foreach ($contenu as $ligne) {
            //Preparation du formatage
            //generation de l'avant ligne
            $avantLigne = $this->generePrePostBalise($tableauFormatage, true);
            //mise a jour du tableau des balises
            $ligne = $this->MajTableauFormatage($ligne, $tableauFormatage);
            //generation de l'apres ligne
            $apresLigne = $this->generePrePostBalise($tableauFormatage, false);

            $chaineRendu = $ligne;
            // on formate au dernier niveau la ligne cad on enferme tout ca dans une 'ligne' html
            if (strpos($chaineRendu, '|c') === false) { //sauf en de rare exception, comme un fieldset
                $resultFinal .= sprintf("<div style=\"height: %dpx;\">%s%s%s</div>\n", $hauteurLigne, $avantLigne, $chaineRendu, $apresLigne);
//        $resultFinal .= sprintf('%s%s%s', $avantLigne, $chaineRendu, $apresLigne);  
            } else {
                $resultFinal .= $chaineRendu . "\n";
            }
        }
        //On interprete les balises simples
        $balise = array('|b+|', '|b-|', '|i+|', '|i-|', '|0+|', '|0-|', '|1+|', '|1-|', '|2+|', '|2-|');
        $baliseHtml = array('<strong>', '</strong>', '<em>', '</em>', '<span style="font-size: 0.7em">', '</span>', '<span style="font-size: 2em">', '</span>', '<span style="font-size: 2.5em">', '</span>');

        $resultFinal = str_replace($balise, $baliseHtml, $resultFinal);

        //on termine le formatage
        //Gestion des widgets de type liste
        $resultFinal = $this->analyseListe($resultFinal);

        $regex = '#\|(c)[+]([^\|]*)\|([^\|]*)\|(\1)([\-])\|#s';
        $resultFinal = preg_replace_callback($regex, array($this, '__callBackApplicationStyle'), $resultFinal);

//on vire tous les retour a la ligne
        $resultFinal = str_replace("\n", '', $resultFinal);

        //renvois du resultat final
        return $resultFinal;
    }

    public function analyseListe($source) {
        $tableauDesVariables = $this->getToutesVariables();
        $result = mmParseListe($source, $tableauDesVariables);
        return $result;
    }

    /**
     * Retourne un tableau contenant toutes les varibales de travail de l'écran (
     */
    public function getToutesVariables() {

//    return array_merge($this->variablesExtra, $this->getValues());
        if ($this->record) {
            return array_merge($this->getValues(), $this->variablesExtra, $this->record->toArray());
        } else {
            return array_merge($this->getValues(), $this->variablesExtra);
        }
    }

    /**
     * Retourne un tableau composé du nom de toutes les variables déclarées dans le template
     * @return array
     */
    public function getTemplateVarList() {
        $regex = '#[\$@](\w+)#'; //Expression pour la compilation des variables
        $listeVariables = array();
        $cible = preg_match_all($regex, $this->ecran['template'], $listeVariables);
        //on construit le tableau final des variable

        return $listeVariables[1];
    }

    protected function generePrePostBalise($tableauFormatage, $ouvrant = true) {
        $resultat = '';
        if ($ouvrant) {
            $marqueur = '+';
        } else {
            $tableauFormatage = array_reverse($tableauFormatage);
            $marqueur = '-';
        }
        foreach ($tableauFormatage as $lettreBalise => $poubelle) { //on a juste besoin de l'ordre des clé du tableau qui represente le tableau
            $resultat = $resultat . sprintf('|%s%s|', $lettreBalise, $marqueur);
        }
        return $resultat;
    }

    protected function genereBalise($lettre) {
        return sprintf();
    }

    protected function MajTableauFormatage($ligne, &$tableauFormatage) {
        //liste des lettres ou ensembles de lettres reconnus
        $listLettreBalise = array('b', 'i', 'f', '0', '1', '2');
        $resultat = '';

        foreach ($listLettreBalise as $lettreBalise) {
            ///////////////////////////////////////////////////
            //on commence par rechercher les balise ouvrante dans la ligne
            ///////////////////////////////////////////////////

            $balise = sprintf('|%s+|', $lettreBalise);
//      $masqueBalise = sprintf('#\|%s+\|#', $lettreBalise);
//      $positionsBalise = array();
            //on cherche les occurences balise
            $occurence = substr_count($ligne, $balise); //      $occurence = preg_match_all($masqueBalise, $ligne, $positionsBalise);

            if ($occurence > 0) { //On a au moins une balise ouvrante
                //deux cas: la balise a deja été ouverte ou pas
                if (isset($tableauFormatage[$lettreBalise]) && $tableauFormatage[$lettreBalise] > 0) { //On a deja une balise ouverte
                    //on a deja la balise ouverte on invalide toutes les balises ouverte avec message d'erreur
                    $resultat = str_replace($balise, sprintf('<span class="mdError">|<strong>%s</strong>+|</span>', $lettreBalise), $ligne);
                } else { //On ouvre la balise pour la première fois
                    unset($tableauFormatage[$lettreBalise]);  //On supprime la ligne du tableau pour faire le menage et pour assurer que pour la suite l'element prendra bien sa place en fin de tableau
                    $tableauFormatage[$lettreBalise] = 1;
                    if ($occurence > 1) {
                        //On a ouvert plus d'une balise dans la ligne, on marque les erreurs
                        $positionApres1erBalise = strpos($balise, $ligne) + 4;
                        $chaineAvecErreur = substr($ligne, $positionApres1erBalise);
                        $chaineAvecErreur = str_replace($balise, sprintf('<span class="mdError">|<strong>%s</strong>+|</span>', $lettreBalise), $chaineAvecErreur);
                        $resultat = substr($ligne, 0, $positionApres1erBalise) . $chaineAvecErreur;
                    } else {
                        $resultat = $ligne;
                    }
                }
            } else {
                //on ne touche pas a la ligne
                $resultat = $ligne;
            }
            ///////////////////////////////////////////////////
            //on continnu par rechercher les balises fermantes dans la ligne
            ///////////////////////////////////////////////////

            $ligne = $resultat; //on a fini le traitement sur la ligne, on repart de 'zero' en travaillante sur les balises fermantes cette fois.

            $balise = sprintf('|%s-|', $lettreBalise);
//      $masqueBalise = sprintf('#\|%s-\|#', $lettreBalise);
//      $positionsBalise = array();
            //on cherche les occurences balise
            $occurence = substr_count($ligne, $balise); //$occurence = preg_match_all($masqueBalise, $ligne, $positionsBalise);
            if ($occurence > 0) { //On a au moins une balise fermante
                //deux cas: la balise a deja été fermée ou pas
                if (!isset($tableauFormatage[$lettreBalise]) || (isset($tableauFormatage[$lettreBalise]) && $tableauFormatage[$lettreBalise] < 1)) {  //On a deja une balise ouverte
                    //on a deja la balise fermante on invalide toutes les balises fermante avec message d'erreur
                    $resultat = str_replace($balise, sprintf('<span class="mdError">|<strong>%s</strong>-|</span>', $lettreBalise), $ligne);
                } else { //On ouvre la balise pour la première fois
                    unset($tableauFormatage[$lettreBalise]);  //On supprime la ligne du tableau car on ferme la balise et on a plus besoin de la traiter
                    if ($occurence > 1) {
                        //On a fermé plus d'une balise dans la ligne, on marque les erreurs
                        $positionApres1erBalise = strpos($balise, $ligne) + 4;
                        $chaineAvecErreur = substr($ligne, $positionApres1erBalise);
                        $chaineAvecErreur = str_replace($balise, sprintf('<span class="mdError">|<strong>%s</strong>-|</span>', $lettreBalise), $chaineAvecErreur);
                        $resultat = substr($ligne, 0, $positionApres1erBalise) . $chaineAvecErreur;
                    } else {
                        $resultat = $ligne;
                    }
                }
            } else {
                $resultat = $ligne;
            }
        }
        //on a un resultat on le renvois. /!\ Le tableau $tableauFormatage etant passé par reference il est mis ajour dans la procedure appelante.
        return $resultat;
    }

    protected function compileVariables($source) {
        //on passe en mode text pur si besoin
//    $source = html_entity_decode($source, ENT_QUOTES);
        $source = str_replace('&quot;', '"', $source);

        //insertion des widgets
        $regex = '#([\\\\]?)([\$@])(\w+)((\[?"?[\w]*"?\]?)*)#'; //Expression pour la compilation des variables (le symbole @ ou $ suivie d'au moins un alphanum et _)
        //le travail est fait par la methode __callBackCompilationVariables. Cette methode ne fais que detecter les variables
        $cible = preg_replace_callback($regex, array($this, '__callBackCompilationVariables'), $source);

        return $cible;
    }

    protected function __compile_position($source) {
        $contenu = explode("\n", $source);
        $top = 0;
        $hauteurLigne = 25;
        $resultFinal = '';
        foreach ($contenu as $lignes) {
            /*
             * Traitement des cas a priorité critique
             */
            $ligneTemp = $lignes;
            $ligneFinale = '';
            $start = 0;
            $position = strpos($ligneTemp, '|r');
            if ($position === false) {
                //Pas trouvé on copie la ligne tel quel
                $ligneFinale = $ligneTemp;
            } else {
                //On a des ordres de retour dans la ligne, on decoupe
                $tranches = explode('|r', $ligneTemp);
                foreach ($tranches as $bloc) {
                    //pour chaque bloc
                    if ($bloc != '') {
                        //le bloc n'est pas vide on peut traiter (bloc vide arrive seulement si la ligne commence par un |rXXX
                        $positionSecondaire = strpos_multi($bloc, ' $|');
                        $decallage = substr($bloc, 0, $positionSecondaire);
                        if (is_numeric($decallage)) {
                            $decallage = (int) $decallage;
                            $ordre = false;
                        } else {
                            $ordre = $bloc[0];
                            $decallage = 0;
                        }

                        if (strlen($ligneFinale) < $decallage) {
                            //On reviens trop en arriere
                            User::flashWarning("Le retour effectué est plus grand que la longueur du text. le decallage sera interpreter comme une suppression d'espace");
                            $ordre = '-';
                        }

                        //on reviens en arriere si possible, sinon juste un virage des espace
                        if ($decallage > 0 and $ordre != '-') {
                            $fragment = substr($ligneFinale, strlen($ligneFinale) - $decallage);
                            //y'a t'il des caracteres dans la zone a reduire ? pour tester on supprime tous les espaces.
                            if (trim($fragment) == '') {
                                //la chaine sans espace est vide ? il n'y a pas de caractere
                                $ligneFinale = substr($ligneFinale, 0, strlen($ligneFinale) - $decallage);
                                $remplissage = substr($bloc, $positionSecondaire);
                            } else {
                                //Il y'a des caracteres dans la partie a supprimer
                                //on programme un retour arriere
                                $ordre = '-';
                            }
                        }

                        //on traite le cas de l'ordre du retour arriere
                        if ($ordre == '-') {
                            //on recule dans la ligne finale
//              $position = strlen($ligneFinale)-1;
//              while ($position > 0 && $ligneFinale[$position] == ' ')
//              {
//                $position--;
//              }
                            $ligneFInale = rtrim($ligneFinale);
                            $remplissage = substr($bloc, $positionSecondaire);
                        }
                    }
                }
            }

            //positionnement des champs
            $ligneTemp = $ligneFinale;
            $ligneResult = '';
            $start = 0;
            $position = strpos($ligneTemp, '$', $start);
            if ($position === false) {
                //y'a pas de variables dans la ligne
                $ligneResult = $lignes;
            } else {
                while ($position !== false) {
                    $posSecondaire = strpos($ligneTemp, ' ', $position);
                    if ($posSecondaire === false) {
                        //on est en fin de ligne
                        $posSecondaire = strlen($ligneTemp);
                    }
                    $longChaineVariable = $posSecondaire - $position; //longueur de la chaine resultat
                    $nomVar = substr($ligneTemp, $position, $longChaineVariable); //le +1 puis -1 sert a virer le '$'
                    //on recupere le widget
                    //$widget = $this[$nomVar];
                    //On a la variable que l'on souhaite afficher et les positions on fait le calcul
                    $precedent = substr($ligneTemp, $start, $position - $start);
                    $remplissage = str_repeat(' ', $longChaineVariable); //on remplace $xxxx par des espaces
                    $ligneResult .= sprintf('%s<span style="position: absolute;">%s</span>%s', $precedent, $nomVar, $remplissage);

                    $start = $posSecondaire;
                    $position = strpos($ligneTemp, '$', $start);
                }
            }

            //passage a la ligne suivante
            $top++; //$top = $top + 1;
            $resultFinal .= sprintf('<div style="height: %spx;">%s</div>', $hauteurLigne, $ligneResult);
        }
        return $resultFinal;
    }

    public function getRendu() {
        return $this->ecran['mode_rendu'];
    }

    public function render($addFormMarkup = true) {
        $this->compile($addFormMarkup);
        if (mmUser::superAdmin() && $this->destination == 'scr') {
            $btEdit = new mmWidgetButtonGoPage('Editer ecran ' . $this->name, url('pEcran/edit?ecran='.$this->name), false, '', array('style' => 'font-size: 10px'));
            $interface = $btEdit->render();
            $htmlEdit = sprintf('<div class="adminBox"><span onclick="$(\'div.adminBox > div\').fadeOut();$(\'#isa_%s\').show()">Menu ecran</span><div style="display: none;" id=isa_%s><span onclick="$(\'div.adminBox > div\').fadeOut()">fermer<span><br />%s</div></div>', $this->getId(), $this->getId(), $interface);
        } else {
            $htmlEdit = '';
        }
        if ($this->ecran['mode_rendu'] == 'txt') {
//      return '<pre style="line-height: 100px;">'.$this->htmlResult.'</pre>';
            return '<pre>' . $this->htmlResult . '</pre>' . $htmlEdit;
        } else {
            return $this->htmlResult . $htmlEdit;
        }
    }

    /**
     * Prepare l'ecran nommé $nomEcran et remplis les champs avec les valeurs de $record. $nameFormat permet de changer le nom de l'ecran par defaut
     * @param type $nomEcran
     * @param Doctrine_Record $record
     * @param type $nameFormat
     * @return type
     * @throws mmExceptionControl
     * @throws mmExceptionDev 
     */
    public function chargeEcranUtilisateur($nomEcran, $record = null, $nameFormat = '') {
        //initialisation
        //parametrage par defaut du formulaire
        $this->valid = true;
        $this->name = $nomEcran;
        $this->id = $this->name . '_id';
        $this->screen = $this->name;
        if (!$nameFormat) {
            $this->nameFormat = sprintf('%s[%%s]', $this->name);
        } else {
            $this->nameFormat = $nameFormat;
        }

        //cahrgement de l'ecran
        $ecran = Doctrine_Core::getTable('EcranUtilisateur')->find($nomEcran);
        if (!$ecran) {
            throw new mmExceptionControl("l'ecran '$ecran' n'existe pas");
        }
        //chargement du javascript
        if ($ecran['script'] != '' && $this->destination == 'scr') {
            $this->addJavascript('global', $ecran['script']);
        }
        //chargement de l'enregistrement
        $nomTable = $ecran['table_liee'];
        if ($record) { //a t'on fournis un enregistrement de données vivantes ?
            //Oui, on verifie qu'il est de la bonne classe.
            //table_liee
            if ($nomTable) {
                if (!is_a($record, $nomTable)) {
                    //ici l'enregistrement ne correspond pas a celui declaré dans l'ecran on genere une erreur
                    throw new mmExceptionDev("Le type de l'enregistrement fournis a l'écran n'est pas du type défini dans celui ci.");
                }
            }
        } else { //si l'enregistrement n'est pas fournis on essait d'utilise celui défini par le champ table_liee de l'ecran
            if ($nomTable) {
                //On a fournis de nom de table, on cree un enregistrement vide pour recuperer la structure
                $record = new $nomTable();
            } else {
                //On a pas fournis d'enregistrment et l'ecran n'a pas de table associé, on a un tableau vide
                $record = false;
            }
        }
        //On associe l'enregistrement a l'enregistrement interne de l'ecran
        $this->record = $record;

        //chargement de la description de la strture des champs
        if ($nomTable) { //On a une table lié defini dans l'enregistrement de la table ecranUtilisateur
            $champ = array();
            //On prend la description depuis la table utilisateur
            $descriptionsChamps = Doctrine_Core::getTable('ChampsTableUtilisateur')->createQuery()->
                    where('nom_table = ?', $nomTable)->
                    execute();
            foreach ($descriptionsChamps as $champCourant) {
                $champs[$champCourant['nom_champ']] = $champCourant;
            }
        } else {
            $champs = array();
        }

        //On prend la description depuis l'ecran et on ecrase les valeurs trouvée dans table par celles definie dans l'ecran
        $this->listeChampsTableEcran = array(); //Definition seulement presente dans le fichier ecran: utilisé par la compilation de type lima
        $descriptionsChamps = Doctrine_Core::getTable('ChampsEcranUtilisateur')->createQuery()->
                where('nom_ecran = ?', $nomEcran)->
                orderBy('numero_ordre')->
                execute();
        $listeChampARecup = array('type_widget', 'option_type_widget', 'libelle', 'formule_calcul', 'calcul_systematique');
        foreach ($descriptionsChamps as $champCourant) {
            //recuperation des valeurs par defaut si necessaire

            if (isset($champs[$champCourant['nom_champ']])) {
                $champDepuisTable = $champs[$champCourant['nom_champ']];

                foreach ($listeChampARecup as $nomChampCompare) {
                    if (trim($champCourant[$nomChampCompare]) == false) {
                        $champCourant[$nomChampCompare] = $champDepuisTable[$nomChampCompare];
                    }
                }
            }
            $champs[$champCourant['nom_champ']] = $champCourant;
            $this->listeChampsTableEcran[] = $champCourant['nom_champ'];
        }

        //chargement des infos specifique a l'ecran
//    $listeChampEcran = $ecran->
        //parametres des champs de données par defaut de la structure
        if ($record) {
            $fields = $record->getTable()->getColumns();
        } else {
            $fields = array();
        }
        //Pour chaque champs disponible on va creer et parametrer le widget associé
        foreach ($champs as $nomChamp => $descriptionChamp) {
            //on recupere la description par defaut du parametrage fournis
            if (isset($fields[$nomChamp])) {
                //Le champ existe dans une table SQL, il possede donc des propriete
                $field = $fields[$nomChamp];
            } else {
                //sinon on a pas de champs de la base equivalent
                $field = false;
            }

            //on regarde le type de chanp souhaité
            $typeWidget = $descriptionChamp['type_widget'];
            if ($typeWidget) {
                //On a un type defini dans type widget

                $widget = $this->creerWidgetDepuisTypeWidget($nomChamp, $descriptionChamp, $record);
            } else {
                //on a pas de type de widget indiqué. On va prendre le type par defaut
                if ($field) {
                    //le champ viens d'une table de la base. on se refere au type de donné pour choisir le widget
                    $widget = $this->creerWidgetDepuisTypeChamp($nomChamp, $field);
                } else {
                    //par defaut le widget est un simple widget de type input text
                    $widget = new mmWidgetText($nomChamp);
                }
            }
            $widget->setFromDb(false);
            //parametrage commun
            $widget->setNameFormat($this->nameFormat);
            //assignation de la valeur par defaut
            if ($record) {  //Si on a un enregistrement venant de la base
                try {
                    if (isset($record[$nomChamp])) {
                        $widget->setDbValue($record[$nomChamp]);
                        $widget->setFromDb(true);
                    }
                } catch (mmExceptionWidget $e) {
                    $this->valide = false;
                }
            }
            //mise en place du calcul des formules de calcul
            if ($descriptionChamp['formule_calcul'] != '') {
                //on a une formule de calcul a resoudre
                //sytematique ou non ? si oui ou que l'enregistrement est nouveau on calcul
                if ($descriptionChamp['calcul_systematique'] == true || $this->isNew()) {
                    $this->listChampsFormule[$nomChamp] = $descriptionChamp;
                    if ($descriptionChamp['calcul_systematique'] == true) {
                        $this->exclusionSetValue[] = $nomChamp;
                    }
                }
            }
            //assignation des max et min
            if ($descriptionChamp['val_min'] != '') {
                $widget->setMin($descriptionChamp['val_min']);
            }
            if ($descriptionChamp['val_max'] != '') {
                $widget->setMax($descriptionChamp['val_max']);
            }
            //Assignation du label, si il est definie
            if ($descriptionChamp['libelle'] == '') {
                $widget->setLabel(ucfirst(str_replace('_', ' ', $nomChamp)));
            } else {
                $widget->setLabel($descriptionChamp['libelle']);
            }
            //Parametres generaux
            if ($descriptionChamp['est_lecture_seule']) {
                //On passe le widget en lecture seule
                $widget->disable();
            }
            if ($descriptionChamp['info_bulle']) {
                $widget->setInfo($descriptionChamp['info_bulle']);
            }
            if (!empty($descriptionChamp['est_notnull'])) {
                $widget->addValidator('notnull');
            }
            if (!empty($descriptionChamp['jsclick'])) {
                $javascript = "$('#" . $widget->getId() . "').click(function(){" . $descriptionChamp['click'] . "});\n";
                $widget->addJavascript('__jsclick__', $javascript);
            }
            if (!empty($descriptionChamp['jsfocus'])) {
                $javascript = "$('#" . $widget->getId() . "').focus(function(){" . $descriptionChamp['focus'] . "});\n";
                $widget->addJavascript('__jsfocus__', $javascript);
            }
            if (!empty($descriptionChamp['jsblur'])) {
                $javascript = "$('#" . $widget->getId() . "').blur(function(){" . $descriptionChamp['blur'] . "});\n";
                $widget->addJavascript('__jsblur__', $javascript);
            }
            if (!empty($descriptionChamp['jschange'])) {
                $javascript = "$('#" . $widget->getId() . "').change(function(){" . $descriptionChamp['change'] . "});\n";
                $widget->addJavascript('__jschange__', $javascript);
            }
            if (!empty($descriptionChamp['jsdblclick'])) {
                $javascript = "$('#" . $widget->getId() . "').dblclick(function(){" . $descriptionChamp['dblclick'] . "});\n";
                $widget->addJavascript('__jsdblclick__', $javascript);
            }
            if (!empty($descriptionChamp['jsrclick'])) {
                $javascript = "$('#" . $widget->getId() . "').bind('contextmenu',function(){" . $descriptionChamp['rclick'] . ";return false;});\n";
                $widget->addJavascript('__jsrclick__', $javascript);
            }

            if ($field) {
                //Application des validateur par defaut inerant au type dans la base de données
                if (isset($field['primary']) && $field['primary']) {
                    $widget->addValidator('notnull');
                }
                if (isset($field['length']) && $widget instanceof mmWidgetText) {
                    $widget->addValidator('length_max', $field['length']);
                }
                if (isset($field['notnull']) && $field['notnull']) {
                    $widget->addValidator('notnull', $field['notnull']);
                    //        $widget->setLabel('<strong>*</strong> '.$widget->getLabel());
                }
                if (isset($field['default'])) {
                    $widget->setDefault($field['default']);
                }
            }
            //Affectation du widget
            //$this->widgetList[$nomChamp] = $widget;
            $this->addWidget($widget);
        }
        //mise a jour des widget en fonction des formule existante
        foreach ($this->listChampsFormule as $dataChamp) {
            $expression = new mmExpression($dataChamp['formule_calcul'], $this->getToutesVariables(), $this);
            $resExpression = $expression->calcul();
            $this->widgetList[$dataChamp['nom_champ']]->setValue($resExpression[1]);
        }
        return $this->valid;
    }

    protected function creerWidgetDepuisTypeWidget($fieldName, $description, $record = null) {
        // variables utiles
        $ouiNon = array('0' => 'Non', '1' => 'Oui');
        $typeWidget = $description['type_widget'];
        $optionsWidget = new mmOptions($description['option_type_widget']);
        //recuperation des attributs standard
        $style = '';
        $attributs = array();

        if ($optionsWidget->get('largeur', false)) {
            $largeur = $optionsWidget->get('largeur');
            if (is_numeric($largeur)) {
                $largeur = $largeur . 'ex';
            }
            $style .= "width: $largeur;";
        }
//    if ($optionsWidget->get('click', false)) $attributs['onclick'] = $optionsWidget->get('click');
        if ($style) {
            $attributs['style'] = $style;
        }

        /* faudra virer a terme */
        $optTaille = array('size' => $optionsWidget->get('largeur'));
        $optTailleNonInput = array('style' => 'width: ' . $optionsWidget->get('largeur') . 'em;');
        /* fin de faudra virer */
        $libelle = $description['libelle'];
        try {
            switch ($typeWidget) {
                case 'button':
                    $click = mmParseVariablesValue($optionsWidget->get('click', ''), $this->getToutesVariables());
                    if ($click) {
                        $attributs['onclick'] = addslashes($click);
                    }
                    $widget = new mmWidgetButton($fieldName, $libelle, $attributs);
                    break;
                case 'buttonGoPage':
                    //        $optionsWidget->setIfVide('style', 'width: '.$optionsWidget->get('largeur').'em;');
                    $url = mmParseVariablesValue($optionsWidget->get('url', ''), $this->getToutesVariables());
                    $widget = new mmWidgetButtonGoPage($libelle, $url, false, $fieldName, $attributs);
                    break;
                case 'buttonGoModule':
                    $module = mmParseVariablesValue($optionsWidget->get('module', ''), $this->getToutesVariables());
                    $action = mmParseVariablesValue($optionsWidget->get('action', ''), $this->getToutesVariables());
                    $parametres = mmParseVariablesValue($optionsWidget->get('parametres', ''), $this->getToutesVariables());
                    $remplace = $optionsWidget->get('remplace', false);
                    $widget = new mmWidgetButtonGoModule($libelle, $module, $action, $parametres, $remplace, $fieldName, $attributs);
                    break;
                case 'buttonAjaxPopup':
                    $url = mmParseVariablesValue($optionsWidget->get('url', ''), $this->getToutesVariables());
                    $widget = new mmWidgetButtonAjaxPopup($libelle, $url, $fieldName, $attributs);
                    break;
                case 'buttonGoModuleAjaxPopup';
                    $module = mmParseVariablesValue($optionsWidget->get('module', ''), $this->getToutesVariables());
                    $action = mmParseVariablesValue($optionsWidget->get('action', ''), $this->getToutesVariables());
                    $parametres = mmParseVariablesValue($optionsWidget->get('parametres', ''), $this->getToutesVariables());
                    $widget = new mmWidgetButtonModuleAjaxPopup($libelle, $module, $action, $parametres, $fieldName, $attributs);
                    break;
                case 'buttonHtmlPopup':
                    $url = mmParseVariablesValue($optionsWidget->get('url', ''), $this->getToutesVariables());
                    $widget = new mmWidgetButtonHtmlPopup($libelle, $url, $fieldName, $attributs);
                    break;
                case 'buttonGoModuleHtmlPopup';
                    $module = mmParseVariablesValue($optionsWidget->get('module', ''), $this->getToutesVariables());
                    $action = mmParseVariablesValue($optionsWidget->get('action', ''), $this->getToutesVariables());
                    $parametres = mmParseVariablesValue($optionsWidget->get('parametres', ''), $this->getToutesVariables());
                    $widget = new mmWidgetButtonModuleHtmlPopup($libelle, $module, $action, $parametres, $fieldName, $attributs);
                    break;
                case 'buttonSubmit':
                    $preSubmit = $optionsWidget->get('preSubmit', '');
                    //        $optionsWidget->setIfVide('style', 'width: '.$optionsWidget->get('largeur').'em;');
                    $widget = new mmWidgetButtonSubmit($libelle, $preSubmit, $fieldName, $attributs);
                    break;
                case 'buttonClose':
                    $widget = new mmWidgetButtonClose($libelle, $fieldName, $attributs);
                    break;
                case 'buttonPrec':
                    $cle = $optionsWidget->get('cle', '');
                    $action = $optionsWidget->get('action', '');
                    $widget = new mmWidgetButtonPrec($libelle, $cle, $action, $fieldName, $attributs);
                    break;
                case 'buttonNext':
                    $cle = $optionsWidget->get('cle', '');
                    $action = $optionsWidget->get('action', '');
                    $widget = new mmWidgetButtonNext($libelle, $cle, $action, $fieldName, $attributs);
                    break;
                case 'buttonSeqPrec':
                    $nomSequence = $optionsWidget->get('sequence', false);
                    $action = $optionsWidget->get('action', '');
                    $widget = new mmWidgetButtonSeqPrec($libelle, $nomSequence, $action, $fieldName, $attributs);
                    break;
                case 'buttonSeqNext':
                    $nomSequence = $optionsWidget->get('sequence', false);
                    $action = $optionsWidget->get('action', '');
                    $widget = new mmWidgetButtonSeqNext($libelle, $nomSequence, $action, $fieldName, $attributs);
                    break;
                case 'CKEditor':
                    //On supprime toutes les options
                    $optionsWidget->destroy();
                    $widget = new mmWidgetCKEditor($fieldName);
                    break;
                case 'TinyMce':
                    //On supprime toutes les options
                    $optionsWidget->destroy();
                    $widget = new mmWidgetTinyMce($fieldName);
                    break;
                case 'text':
                    $optionsWidget->setIfVide('size', $optionsWidget->get('largeur'));
                    $widget = new mmWidgetText($fieldName, '', $attributs);
                    break;
                case 'textArea':
                case 'blob':
                case 'clob':
                    $optionsLocale = array();
                    $optionsLocale['cols'] = $optionsWidget->get('cols', 20);
                    $optionsLocale['rows'] = $optionsWidget->get('lines', 3);
                    $widget = new mmWidgetTextArea($fieldName, '', $optionsLocale);
                    break;
                case 'integer':
                    $optionsWidget->setIfVide('width', $optionsWidget->get('largeur'));
                    $widget = new mmWidgetInteger($fieldName, 0, $attributs);
                    break;
                case 'decimal':
                    $optionsWidget->setIfVide('width', $optionsWidget->get('largeur'));
                    $widget = new mmWidgetFloat($fieldName, 0, $attributs);
                    break;
                case 'date':
                    //option: date du jour a la creation
                    $widget = new mmWidgetDate($fieldName, $attributs);
                    break;
                case 'timestamp':
                    //option date du jour a la creation
                    $widget = new mmWidgetTimestamp($fieldName, '', $attributs);
                    break;
                case 'time':
                    //option: heure courante a la création
                    $widget = new mmWidgetTime($fieldName, '', $attributs);
                    break;
                //      case 'blob':
                //      case 'clob':
                //        $optionsLocale = array();
                //        $optionsLocale['cols'] = $optionsWidget->get('cols');
                //        $optionsLocale['rows'] = $optionsWidget->get('lines', 3);
                //        $widget = new mdWidgetTextArea($fieldName, '', $optionsLocale);
                //        break;
                case 'boolean':
                    $widget = new mmWidgetSelect($fieldName, $ouiNon, '', $attributs);
                    break;
                case 'selectTable':
                    $tableAssociee = $optionsWidget->get('table');
                    $widget = new mmWidgetSelectTable($fieldName, $tableAssociee, '', $attributs);
                    break;
                case 'select':
                    $remplissage = $optionsWidget->get('contenu');
                    $widget = new mmWidgetSelect($fieldName, stringToArray($remplissage), '', $attributs);
                    break;
                case 'list':
                    $remplissage = $optionsWidget->get('contenu');
                    $widget = new mmWidgetList($fieldName, stringToArray($remplissage), '', $attributs);
                    break;
                case 'menu':
                    $nomMenu = $optionsWidget->get('nom');
//          $optionsWidget->destroy(); //on efface toutes les autres options
                    $widget = new mmMenu($nomMenu, $fieldName);
                    break;
                case 'recordList':
                    $condition = mmParseVariablesValue($optionsWidget->get('condition', ''), $this->getToutesVariables());
                    $action = mmParseVariablesValue($optionsWidget['action'], $this->getToutesVariables());
                    $widget = new mmWidgetRecordList($fieldName, $optionsWidget['table'], $action, $condition, $optionsWidget);
                    break;
                case 'selectFic':
                    $condition = mmParseVariablesValue($optionsWidget['condition'], $this->getToutesVariables());
                    $widget = new mmWidgetSelectFic($fieldName, $optionsWidget['table'], $optionsWidget['cle'], '', $optionsWidget['libelle'], $condition);
                    break;
                case 'textRech':
                    $widget = new mmWidgetTextSearch($fieldName);
                    break;
                case 'inputPopup':
                    $widget = new mmWidgetInputPopup($fieldName, $optionsWidget, '...', '', $attributs);
                    break;
                case 'hidden':
                    $widget = new mmWidgetHidden($fieldName, '', $attributs);
                    break;
                case 'recordCle':
                    $optionsWidget->setIfVide('cle', $fieldName);
                    $optionsWidget->setIfVide('cols', mmParseVariablesValue($optionsWidget['cols'], $this->getToutesVariables()));
                    $optionsWidget->setIfVide('actionListe', '?b=%s');
                    $optionsWidget->setIfVide('actionNouveau', '?b=-1');
                    $optionsWidget->setIfVide('table', $this->ecran['table_liee']);
                    $optionsWidget->setIfVide('auto', 0);
                    $widget = new mmWidgetRecordCle($fieldName, '', $optionsWidget, $attributs);
                    break;
                case 'imageSQL':
                    $widget = new mmWidgetImg($fieldName, '?module=pJpegSQL&id=' . $record['fged_1'], $optionsWidget);
                    break;
                case 'execScreen':
                    $nomEcran = mmParseVariablesValue($optionsWidget->get('nom', ''), $this->getToutesVariables());
                    //        $nomVariable = mdParseValeurVariables($optionsWidget->get('variable', false), $this->getToutesVariables());
                    $nomVariable = $optionsWidget->get('variable', false);
                    $indexForm = $optionsWidget->get('index', false);
                    if ($nomEcran) {
                        $widget = new mmWidgetScreen($fieldName, $nomEcran, $nomVariable, $indexForm);
                    } else {
                        mmUser::flashError("Widget Ecran externe $fieldName: un nom doit etre fournis");
                        $widget = new mmWidgetBlank($fieldName);
                    }
                    break;
                default:
                    //la c'est pas normal on flash l'admin et on continu en considerant le widget comme un champ text
                    //Widget inconnu ou pas encore codé
                    $widget = new mmWidgetText($fieldName, '', $optTaille);
                    User::flashSuperAdmin("$fieldName le type $typeWidget n'est pas reconnus");
                    break;
            }
        } catch (mmExceptionControl $e) {
            $widget = new mmWidgetText($fieldName, "(Err)$fieldName", array('style' => 'background-color: red;'));
            $widget->disable();
        }
        //Parametres generaux
        if ($description['est_lecture_seule']) {
            //On passe le widget en lecture seule
            $widget->disable();
        }
        return $widget;
    }

    protected function __callBackCompilationVariables($fragments) {
        if ($fragments[1] == '\\') {
            //on a echappé la variable dans ce cas on ne l'évalue pas
            return (substr($fragments[0], 1)); //on retire le / du resultat
        }

        if ($this->appercu) {
            if ($this->getRendu() == 'txt') {
                return sprintf('<span style="position: absolute;">ABCDEF</span>%s', $remplissage);
            } else {
                return "ABCDEF";
            }
        }

        //Recuperation des fragments. typiquement le fragment 1 correspond a la commande. Le reste corresponds au parametres et sera traité au cas par cas
        $commande = $fragments[2];

        //Si la variable existe dans le formulaire on effectu le rendu du champ
        switch ($commande) {
            case '$': //On effectue l'affichage le champ
                $nomVar = $fragments[3];
                if (isset($this[$nomVar])) { //C'est une variable venant du formulaire
                    if ($this->getRendu() == 'txt') {
                        //          $remplissage = str_repeat(' ', strlen($fragments[0]));
                        $remplissage = ' ';
                        if ($this->destination == 'scr' || true) { //TODO: on force le mode screen ici pour tester le positionnement absolu
                            $html = $this[$nomVar]->render() . $this[$nomVar]->renderErrors();
                            $html = sprintf('<span style="position: absolute;">%s</span>%s', $html, $remplissage);
                        } else {
                            $html = $this[$nomVar]->renderPdf();
                        }

                        return $html;
                    } else {
                        if ($this->destination == 'scr') {
                            $html = $this[$nomVar]->render() . $this[$nomVar]->renderErrors();
                        } else {
                            $html = $this[$nomVar]->renderPdf();
                        }
                        return $html;
                    }
                } else {
                    if (isset($this->variablesExtra[$nomVar])) {
                        try {
                            $valeur = mmParseVariablesValue($fragments[0], $this->variablesExtra);
                        } catch (mmExceptionControl $e) {
                            mmUser::flashError($e->getMessage());
                            return substr($fragments[0], 1);
                        }
                        /*
                          if (isset($this->variablesExtra[$nomVar]))
                          {
                          $valeur = $this->variablesExtra[$nomVar];
                          if ( ! is_scalar($valeur)) // si ce n'est pas un type simple, on traite les indices
                          {
                          $indice = explode(']', $fragments[4]); //des indice on fais un tableau de nom d'indices
                          array_pop($indice); //pour virer les dernier element du tableau on le pop
                          foreach($indice as $i)
                          {
                          $i = substr($i, 1);
                          if (is_numeric($i))
                          {
                          $i = (int)$i;
                          }
                          else
                          {
                          $i = str_replace('"', '', $i);
                          }
                          if (isset($valeur[$i]))
                          {
                          $valeur = $valeur[$i];
                          }
                          else
                          {
                          mdUser::flashError($fragments[0].": indice $i inconnu");
                          return $fragments[0];
                          }
                          }
                          }
                         */


                        if ($this->getRendu() == 'txt' && ($this->destination == 'scr' || true)) { //TODO: on force le mode screen ici pour tester le positionnement absolu
                            $remplissage = ' ';
                            $html = sprintf('<span style="position: absolute;">%s</span>%s', $valeur, $remplissage);
                        } else {
                            $html = $valeur;
                        }
                        return $html;
                        //<----- On a trouver la variables extra, on returne son html          
                    }
                    //Si on arrive ici c'est qu'on a ni variables standard, ni variable extra definie
                    mmUser::flashError("La variable $nomVar n'a pas été définie");
                    return $fragments[0];
                    //<----- la variable est indefinie, on sort ici        
                }
                break;
            case '@': //On affiche la valeur d'un champ du formulaire
                $nomVar = $fragments[3];
                if (isset($this[$nomVar])) { //C'est une variable venant du formulaire
                    return $this[$nomVar]->getValue();
                }
                if (isset($this->variablesExtra[$nomVar])) { //c'est une variable extra
                    return $this->variablesExtra[$nomVar];
                }
                break;
            default:
                return sprintf('<span class="mdError">La commande \'%s\' n\'a pas été définie</span>', $commande);
                break;
        }
    }

    function __callBackApplicationStyle($fragments) {
        //Recuperation des infos utiles
        $contenuAModifier = $fragments[3];
        $commande = $fragments[1];
        $parametre = $fragments[2];

        //Verification des erreurs
        $chaineErreur = '<span style="color: red; font-weight: bold;">' . $fragments[0] . '</span>';
        if ($commande != $fragments[4]) {
            User::flashWarning("Balise ouvrante differente de la balise fermante");
            return $chaineErreur;
        }
        if ($fragments[5] != '-') {
            User::flashWarning("Balise ouvrante non refermées");
            return $chaineErreur;
        }
        //on determine l'ordre
        switch ($commande) {
            case 'b':
                $resultat = '<strong>' . $contenuAModifier . '</strong>';
                break;
            case 'i':
                $resultat = '<em>' . $contenuAModifier . '</em>';
                break;
            case 'f':
                $resultat = sprintf('<span style="font-size: %spt">%s</span>', $parametre, $contenuAModifier);
                break;
            case 'c':
                if ($parametre) {
                    $resultat = sprintf('<fieldset><legend>%s</legend>%s</fieldset>', $parametre, $contenuAModifier);
                } else {
                    $resultat = sprintf('<fieldset>%s</fieldset>', $contenuAModifier);
                }
                break;
            default:
                User::flashWarning("balise {$fragments[1]} inconnue");
                return $chaineErreur;
                break;
        }
        return $resultat;
    }

    public function setAppercuOn() {
        $this->appercu = true;
    }

    public function setAppercuOff() {
        $this->appercu = false;
    }

    public function setFocus($idChamp) {
        $this->_autofocus_ = $idChamp;
    }

    public function resetFocus() {
        $this->_autofocus_ = '';
    }

    /*
     * Modification de setValues: on ne met a jour que les champs qui ne sont pas exclus de la liste
     */

    public function setValues($values = array()) {

//    foreach ($values as $nomVal => $valVal)
//    {
//      if (in_array($nomVal, $this->exclusionSetValue))
//      {
//        unset ($values[$nomVal]);
//      }
//    }
//    $newValues = array_flip(array_diff(array_flip($values), $this->exclusionSetValue));
        return parent::setValues($values);
    }

}