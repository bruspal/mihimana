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
@file : mmExpression.php
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



//
// On inclus les eventuelles fonction utilisateurs
//
$fichierPlugins = APPLICATION_DIR . '/lib/expressionCustomFunction.php';
// En mettant les fonction utilisateur dans ce fichier ca étend le langage par des fonctions spécifique a un contexte particulier
include $fichierPlugins;

class mmExpression extends mmVarHolder {

    protected
            $interpreterVar,
            $expression = '',
            $operateurs = array('+', '-', '*', '/', '=', '^', '<', '>', '<=', '>=', '<>', 'ET', 'OU', 'NON'),
            $priorite = array('+' => 2, '-' => 2, '*' => 3, '/' => 3, '^' => 3, '=' => 0, '<' => 0, '>' => 0, '<=' => 0, '>=' => 0, '<>' => 0, 'ET' => 1, 'OU' => 1, 'NON' => 1),
            $constantes = array(
                'JOUR', //nombre de jours depuis le 01/01/1970
                'MOIS', //nombre de mois depuis le 01/01/1970
                'AN', //nombre d'années depuis le 01/01/1970
                'VRAI',
                'FAUX'
                    ),
            $fonctions = array(
                'int', //int($valeur): retourne la $valeur tronqué a l'entier [int(67.89) => 67]
                'chaine', //chaine($nombre): converti un nombre en chaine
                'func', // fonction de test, concat $p1, $p2, $3 : func($p1,$p2,$p3)
                'rd', //rd($decimal, $valeur): retourne la valeur arrondis au $decimal inferieur [rd(10, 28) => 20]
                'ru', //rd($decimal, $valeur): retourne la valeur arrondis au $decimal supérieur [ru(10, 28) => 30]
                'min', //min($val1, ..., $valN): retourne la plus petite valeur parmis les parametres [min(1,2,3) => 1]
                'max', //min($val1, ..., $valN): retourne la plus grande valeur parmis les parametres [min(1,2,3) => 3]
                'table', //table("nom dans la table", "cle de recherche"[, date?]): retourne la valeur identifié par clé de recherche dans la table de parametre 'nom de table'
                'formate', //formate ("format style PHP", variable1, ..., variablen): formate les variables, retourne une chaine);
                'date', //date(nbJour): retourne une date litteral a partir de l'entier nbJour
                'nbJour', //nbJour(date): retourne le nombre de jour entre la date date et le 01/01/1970
                'dateMoins', //dateMoins(date1, date2): effectue la soustraction de jour entre date1 et date2, date 2 peux etre une date ou un entier
                'datePlus', //datePlus(date1, date2): effectue l'addition de jour entre date1 et date2, date 2 peux etre une date ou un entier
                'jour', //jour($date): retourne un nombre representant le jour de la date, $date peux etre une date ou un nombre
                'mois', //mois($date): retourne un nombre representant le mois de la date, $date peux etre une date ou un nombre
                'an', //an($date, $decimal): retourne un nombre representant le mois de la date, $date peux etre une date ou un nombre. $decimal peux valoir 2 ou 4 pour choisir un resultat sur 2 ou 4 digits
                'age', //age($date1, [$date2]): Retourne l'age(nbr) en année entre la $date1 et la $date2. le resultat sera toujours positif, si $date2 est omis c'est la date du jour qui est utilisé
                'existe', //existe($nomVar): retourne vrai si la variable $nomVar existe, faux sinon
                'chargeEnregistrement', //charge un enregistrement: chargeEnregistrement($critere) si c'est bon retourne un enregistrement sinon retourne faux
                'chargeCollection', ////charge une collection d'enregistrement: chargeCollection($critere[, $tri]) si c'est bon retourne une collection sinon retourne faux
                'desactive', //desactive le widget du form contenant la formule
                'desactiveEcran', //Desactive l'ensemble du formulaire, 
                'active', //desactive le widget du form contenant la formule
                'devise', //afficher un nombre sous forme de devise: devise($valeur[[, $decimal = 2], $symbole = '')
                'estNouveau', //retourne VRAI si l'ecran travail sur un enregistrement non encore enregistré, sinon renvois FAUX
                    ),
            $debug = false,
            $variablesAffectation = array(), //Ensemble des variables affectée durant le traitrement de la formule
            $containerForm = null; // mdForm ou mdScreen Contenant l'expression

    public function __construct($expression, $variables = array(), mmForm $containerForm = null, $interpreterVar = false) {
        $this->expression = $expression;
        $this->variables = $variables;
        $this->interpreterVar = $interpreterVar;
        $this->varUser = array();
        $this->containerForm = $containerForm;
    }

    public function debug($debug) {
        $this->debug = $debug;
    }

    public function varsDynamique() {
        $this->interpreterVar = true;
    }

    public function varsStatique() {
        $this->interpreterVar = false;
    }

    public function calcul($expression = false) {
        //parametrage des differents comportement
        // Calcul une expression
        if ($expression === false) {
            $expression = $this->expression;
        }
        $expression = trim($expression);
        //on vire les commentaire
        $expression = preg_replace('#/\*.*\*/#', '', $expression);
        //dans le cas d'une expression vide on retourne le type void
        if (trim($expression) == '') {
            return array('void', 0);
        }
        //On transforme l'expression en tableau
//    $expression = str_replace(';', "\n", $expression);
//    $expression = str_replace("\r", '', $expression);
//    $expression = explode("\n", $expression);
        $expression = str_replace(array("\n", '&nbsp;'), ' ', $expression);
//    $expression = strip_tags($expression);
        $expression = str_replace("\r", '', $expression);
        $expression = explode(";", $expression);
        foreach ($expression as $ligneExp) {
            $ligneExp = trim($ligneExp);
            if ($ligneExp == '') {
                //si c'est une ligne vide on ne traite pas
                continue;
            }
            if ($this->debug)
                echo "<ul><li><h3>Calcul de $ligneExp</h3>";
            //on remplace ce qu'il y'a a remplacer en auto: les variables
            $this->expInit = $ligneExp;
            $expInit = $ligneExp;
            //    $ligneExp = preg_replace_callback('#([^\\\\]?)\$(\w+)#', array($this, '__callbackExecuteVariables'), $ligneExp); //pour test
            if ($this->debug)
                echo "<h2>$ligneExp</h2>";
            try {
                $pileRpn = $this->creerPile($ligneExp);
                $resultat = $this->calculPile($pileRpn);
                if ($this->debug)
                    echo "<h4><strong>{$expInit} => {$resultat[1]}({$resultat[0]})</strong></h4></li></ul>";
            } catch (mmExceptionFormule $e) {
                mmUser::flashError($e->getMessage());
                $trace = $e->getTrace();
                if ($this->debug == true) {
                    throw $e;
                } else {
                    return array('err', 0);
                }
            }
        }
        //On retourne le resultat de la derniere ligne evalué
        return $resultat;
    }

    protected function executeAffectation($fragments) {
        $nomVar = $fragments[1];
        $chaineIndices = $fragments[2];
        $valeur = $fragments[3];
        $resultat = array('void', 0);
        //execution de la partie droite
        $resultat = $this->calcul($valeur);
        if ($chaineIndices == '') {
            //variables simple
            //la premiere chose: on détruit l'éventuelle variable existante
            unset($this->variables[$nomVar]);
            $this->variables[$nomVar] = $this->retVal($resultat);
            $this->variablesAffectation[$nomVar] = $this->retVal($resultat);
        } else {
            //c'est une variable de type tableau, on decompose et on compose
            $indices = explode(']', $chaineIndices);
            array_pop($indices); //on vire la derniere case qui contient rien
            $nbIndice = count($indices);
            $valeurFinale = array();
            $pointeur = & $valeurFinale;
            //On utilise pointeur comme un pointeur classique. dans l'algo on construit iterativement le tableau de variables en pointant
            // sur la cellule qu'on est en train de traiter.
            foreach ($indices as $ind) {
                $ind = substr($ind, 1);
                $ind = str_replace('"', '', $ind);
                if (is_numeric($ind) && (int) $ind == $ind) { //on detecte qu'on est bien en presence d'un nombre entier
                    $ind = (int) $ind;
                }

                //on prepare l'affectation
                //c'est unz chaine ou un decimal. dans ce cas la on considere que c'est une chaine
                //si jamais on a une chaine vide c'est un indicage automatique
                if ($ind !== '') {
                    //indice nommé
                    if (!isset($pointeur[$ind])) {
                        //l'indice n'est pas initialisé on initialise
                        $pointeur[$ind] = array();
                    }
                    //On passe a l'indice suivant en mettant a jour le pointeur
                    $pointeur = & $pointeur[$ind];
                } else {
                    //indice non nommé, on insert a la suite
                    $pointeur[] = array();
                    $pointeur = & $pointeur[count($pointeur) - 1];
                }
            }
            //On arrive ici, le terrain est préparé pour l'affectation finale
            $pointeur = $this->retVal($resultat);
            if (isset($this->variables[$nomVar])) {
                $this->variables[$nomVar] = array_merge($this->variables[$nomVar], $valeurFinale); // c'est le tableau construit grace a pointeur
                $this->variablesAffectation[$nomVar] = $this->variables[$nomVar];
            } else {
                $this->variables[$nomVar] = $valeurFinale; // c'est le tableau construit grace a pointeur
                $this->variablesAffectation[$nomVar] = $this->variables[$nomVar];
            }
        }
        //on retourne la valeur calculé de l'affectation
        return $resultat;
    }

    protected function executeVariable($fragments) {
        $nomVar = $fragments[1];
        if (isset($this->variables[$nomVar])) {
            $type = 'str';
            $valeur = @$this->variables[$nomVar];
            if (is_null($valeur)) {
                if ($this->debug)
                    echo "<div style='color: red;'>valeur $nomVar = null. 0 utilisé</div>";
                mmUser::flashDebug("valeur $nomVar = null. 0 utilisé");
                return array('nbr', 0);
            }
            if (is_scalar($valeur)) { // c'est un type simple
                //on traite ici le cas des date mysql
                if (preg_match('#(\d{4})-(\d{2})-(\d{2})#', $valeur, $fragDate)) {
                    //c'est une date mysql au format on transforme en format interne
                    $valeur = $fragDate[3] . '-' . $fragDate[2] . '-' . $fragDate[1];
                    $type = 'dte';
                }
                if ($this->interpreterVar) {
                    return $this->calcul($valeur);
                } else {
//          if ( ! is_numeric($valeur))
//          {
//            $valeur = '"'.addcslashes($valeur, '"\\').'"';
//          }
//          else
                    if (is_numeric($valeur) || is_bool($valeur)) {
                        $type = 'nbr';
                    }
                    return array($type, $valeur);
                }
            } elseif ($valeur instanceof Doctrine_Record && empty($fragments[2])) {
                return array('rec', $valeur);
            } elseif ($valeur instanceof Doctrine_Collection && empty($fragments[2])) {
                return array('col', $valeur);
            } else {
                $indice = explode(']', $fragments[2]); //des indice on fais un tableau de nom d'indices
                array_pop($indice); //pour virer les dernier element du tableau on le pop
                foreach ($indice as $i) {
                    $i = substr($i, 1);
                    if (is_numeric($i) && (int) $i == $i) {
                        $i = (int) $i;
                        $type = 'nbr';
                    } else {
                        $i = str_replace('"', '', $i);
                    }
                    if (isset($valeur[$i])) {
                        $valeur = $valeur[$i];
                        if (is_scalar($valeur)) {
                            //on traite ici le cas des date mysql
                            if (preg_match('#(\d{4})-(\d{2})-(\d{2})#', $valeur, $fragDate)) {
                                //c'est une date mysql au format on transforme en format interne
                                $valeur = $fragDate[3] . '-' . $fragDate[2] . '-' . $fragDate[1];
                                $type = 'dte';
                            }
                            return array($type, $valeur);
                        }
                    } else {
                        mmUser::flashError($fragments[0] . ": indice $i inconnu");
                        return array('nbr', 0);
                    }
                }
            }
        } else {
            mmUser::flashError("$this->expInit: variable \$$nomVar inconnue. valeur 0 utilisée ");
            return array('nbr', 0);
        }
    }

    protected function __callbackExecuteVariables($fragments) {
        $nomVar = $fragments[2];
        if (isset($this->variables[$nomVar])) {
            $valeur = $this->variables[$nomVar];
            //on traite ici le cas des date mysql
            if (preg_match('#(\d{4})-(\d{2})-(\d{2})#', $valeur, $fragDate)) {
                //c'est une date mysql au format on transforme en format interne
                $valeur = $fragDate[3] . '-' . $fragDate[2] . '-' . $fragDate[1];
            }
            if (is_scalar($valeur)) { // c'est un type simple
                if ($this->interpreterVar) {
                    return $fragments[1] . $this->retVal($this->calcul($valeur));
                } else {
                    if (!is_numeric($valeur)) {
                        $valeur = '"' . addcslashes($valeur, '"\\') . '"';
                    }
                    return $fragments[1] . $valeur;
                }
            } else {
                mmUser::flashError("$this->expInit: variable \$$nomVar de type incompatible. valeur 0 utilisée ");
                return $fragments[1] . "0";
            }
        } else {
            mmUser::flashError("$this->expInit: variable \$$nomVar inconnue. valeur 0 utilisée ");
            return $fragments[1] . "0";
        }
    }

    public function getNvVariables() {
        return $this->variablesAffectation;
    }

    protected function creerPile($expression) {
        $pile = new mmStack();
        $pileOperateur = new mmStack();
        $index = 0;
        $charCurseur = false;
        $attendOperateur = false;

        $longueur = strlen($expression);

        //construction du masque de selection
        $regex = '#^('; //debut puis la chaine commence par ce qui est entre ( et le )#i de la fin
//    $regex.= '\[\s*(\w+)\s*\]|'; // une sous formule: un [suivie de 0 a n separateur, suivie de 1 a n alphanum + _, suivie de 0 a n separateur, suivie de ]. on capture le nom de la formule
        $regex.= '\$\w+((\[?"?[\w]*"?\]?)*)\s*:=.*|'; //c'est une affectation: un '$' suivie d'une chaine alphanume +'_' suivie de 0 a N [blabla]
        $regex.= '\$\w+(\[?"?[\w]*"?\]?)*|'; //c'est une variable: un '$' suivie d'une chaine alphanume +'_' suivie de 0 a N [blabla]
        $regex.= '\[|'; // une sous formule: on commence par [
        $regex.= '[a-z_]\w*\s*\(|'; //les fonctions: une lettre ou _ suivie de 0 a n alphanum ou _ suivie de 0 ou n séparateurs suivie d'une '('
        //on construit la liste des constantes
        foreach ($this->constantes as $const) {
            $regex.="$const|";
        }
        $regex.= '\(|'; //on a une parenthese ouvrante
        $regex.= '\d+(?:\.\d*)?|'; //au moins un chiffre '\d', suivie eventuellement d'un point suivie de 0 a n chiffre '(\.\d*)' ceci 0 ou 1 fois '?'. le ?: sert a definir une parenthese non capturante. ou... '|'
        $regex.= '\.\d+|'; //un point '\.' suivie d'au moins 1 chiffre '\d+'. ou '|'...
        $regex.= '"\d{2}-\d{2}-\d{4}"|'; //une date litteral " suivie de 2 chiffres suivie de . suivie de 2 chiffre suivie de . suivie de 4 chiffre suivie de "
//    $regex.= '"[^"]*"|'; //une guillemet '"' suivie de 0 a N fois n'importequoi sauf une guillemet '[^"]*', suivie d'une guillemet. ou '|'...
        $regex.= '"|'; //une guillemet '"' 
        $regex.= 'et|ou|non|<>|>=|<=|>|<|=|'; //un operateur logique
        $regex.= '\+|-|\*|/|=|^'; //operateur arythmetique
        $regex.= ')#i'; //fin, on fait une recherche insensible a la case argument 'i'

        while ($index < $longueur) {
            //on vire éventuel les espace blanc
            while ($expression[$index] == ' ') {
                $index++;
            }

            $charCurseur = $expression[$index];
            //on place les elements dans fragments, et sa position dans $posOp
            $tranche = substr($expression, $index);
            $posOp = preg_match($regex, $tranche, $fragments);
            //on recupere la valeur
            if ($posOp) {
                $valeur = $fragments[0];
                //On traite le cas de l'operateur
                if (in_array($valeur, $this->operateurs)) {
                    //C'est un operateur
                    //dans ce cas la on vérifie si il est attendu ou non
                    if (!$attendOperateur) {
                        //on attend pas un operateur mais on en a un c'est peut etre une erreur
                        switch (strtoupper($valeur)) {
                            case '-': //C'est une negation, on empile le signe de negation
                                $pile->push(array('op', '~'));
                                break;
                            case 'NON': // C'est un non logique, c'est logique de l'avoir la, on empile
                                $pileOperateur->push('NON');
                                break;
                            default: // Dans tous les autres cas c'est une erreur
                                throw new mmExceptionFormule("$expression: erreur de syntaxe, $valeur mal placé");
                                break;
                        }
//            $attendOperateur = false;
                    } else {
                        //on attend un operateur, on verifie si ce n'est pas une erreur
                        if (strtoupper($valeur) == 'NON') {
                            throw new mmExceptionFormule("$expression: operateur '$valeur' non attendu");
                        }
                        //on empile l'opérateur
                        //algorythme basé sur shunting-yard: http://en.wikipedia.org/wiki/Shunting-yard_algorithm
                        $opTop = $pileOperateur->top();
                        while ($opTop !== null && $this->priorite[$opTop] > $this->priorite[$valeur]) {
                            $aEmpiler = $pileOperateur->pop();
                            $pile->push(array('op', $aEmpiler));
                            $opTop = $pileOperateur->top();
                        }
                        $pileOperateur->push($valeur);
                        $attendOperateur = false; //on a traité l'operateur. on attend maintenant autre chose
                    }
                }
                //c'est une sous expression
                elseif ($valeur == '[') {
                    if (!$attendOperateur) {
                        $index++;
                        $nomFormule = '';
                        $char = @$expression[$index];
                        while ($char != '' && $char != ']') {
                            if (preg_match('#[a-z0-9_]#', $char)) { // est-ce un caractere alphanumeric + '_'
                                $nomFormule .= $char;
                            } else {
                                throw new mmExceptionFormule("$expression: charactere invalide dans le nom d'une sous expression");
                            }
                            $index++;
                            $char = @$expression[$index];
                        }
                        //on fais la vérification d'erreur
                        if ($char == '') {
                            throw new mmExceptionFormule("$expression: ']' manquant");
                        }
                        if ($nomFormule == '') {
                            throw new mmExceptionFormule("$expression: nom de sous formule manquant");
                        }
                        //on execute l'expression trouvé
                        if ($this->debug)
                            echo "<h1>Charge la formule [$nomFormule]</h1>";
                        $sousExp = $this->chargeExpression($nomFormule);
                        if ($this->debug)
                            echo "calcul de l'expression [$sousExp]<br />";
                        $resultat = $this->calcul($sousExp);
                        $pile->push($resultat);
                        $attendOperateur = true;
                        $valeur = ''; //on utilise pas la valeur pour avancer dans la chaine
                        $index++; // on est sur le ] on passe au suivant
                    }
                    else {
                        throw new mmExceptionFormule("$expression: operateur attendu mais sous expression fournies");
                    }
                }
                //c'est une affectation
                elseif (preg_match('#\$(\w+)((?:\[?"?[\w]*"?\]?)*)\s*:=(.*)#', $valeur, $fragAffect)) {
                    if (!$attendOperateur) {
                        $resultat = $this->executeAffectation($fragAffect);
                        $pile->push($resultat);
                        $attendOperateur = true;
                    } else {
                        throw new mmExceptionFormule("$expression: operateur attendu mais variable fournies");
                    }
                }
                //c'est une variables
                elseif (preg_match('#\$(\w+)((\[?"?[\w]*"?\]?)*)#', $valeur, $fragVar)) {
                    if (!$attendOperateur) {
                        $resultat = $this->executeVariable($fragVar);
                        $pile->push($resultat);
                        $attendOperateur = true;
                    } else {
                        throw new mmExceptionFormule("$expression: operateur attendu mais variable fournies");
                    }
                }
                //c'est une fonction
                elseif (preg_match('#([a-z_]\w*\s*)\(#i', $valeur, $fragFonc)) {
                    if (!$attendOperateur) {
                        $nomFonction = trim($fragFonc[1]);
                        //on traite au cas par cas
                        if (strtoupper($nomFonction) == 'SI') {
                            //On traite le cas particulier du SI
                            $resultat = $this->executeSi($expression, $index);
                            $pile->push($resultat);
                            $attendOperateur = true;
                            $valeur = '';
                        } elseif (TRUE) {// on fais une vérification sur l'existance plustot que sur la présence dans le tableau, c'est plus souple. Anciennement : in_array($nomFonction, $this->fonctions))
                            //On recupère les parametres
                            $index += strlen($valeur);
                            $tableauParams = $this->prepareParams($expression, $index);
                            if ($this->debug) {
                                $chaineParams = '';
                                foreach ($tableauParams as $temp) {
                                    $chaineParams .= ',' . $temp[1];
                                }
                                $chaineParams = substr($chaineParams, 1);
                                echo "<h4>execute: $nomFonction($chaineParams)</h4>";
                            }
                            //est-ce une fonction qui existe ?
                            if (function_exists('fFunc_' . $nomFonction)) {
                                //on execute la fonction est on place le résultat dans $resultat
                                $resultat = call_user_func_array('fFunc_' . $nomFonction, $tableauParams);
                            } else {
                                // ce n'est pas une fonction. est-ce une methode ?
                                if (method_exists($this, 'fFunc_' . $nomFonction)) {
                                    //on execute la method est on place le résultat dans $resultat
                                    $resultat = call_user_func_array(array($this, 'fFunc_' . $nomFonction), $tableauParams);
                                } else {
                                    //Ni l'un ni l'autre ? c'est une erreur
                                    throw new mmExceptionFormule("$expression: fonction $nomFonction inconnue");
                                }
                            }
                            $pile->push($resultat);
                            $attendOperateur = true;
                        } else {
                            throw new mmExceptionFormule("$expression: fonction $nomFonction inconnue");
                        }
                        $valeur = '';
                    } else {
                        throw new mmExceptionFormule("{$expression}: une opératuer est attendu, la fonction $valeur a été fournis");
                    }
                }
                //c'est une parenthese ouvrante
                elseif ($valeur == '(') {
                    if (!$attendOperateur) {
                        //on traite le contenu de la parenthese: attention index vaut la nouvelle position du curseur apres execution
                        $resultat = $this->executeBloc($expression, $index);
                        $pile->push($resultat);
                        $attendOperateur = true;
                        $valeur = ''; //index deja a jour, pas la peine defaire avancer
                    } else {
                        throw new mmExceptionFormule("{$expression}: une opératuer est attendu, une '(' a été fournis");
                    }
                }
                //c'est une constante
                elseif (in_array($valeur, $this->constantes)) {
                    //on resoud
                    if (!$attendOperateur) {
                        switch ($valeur) {
                            case 'JOUR':
                                mmUser::flashSuperAdmin("Attention jour compte au 01/01/1970");
                                $nbJours = floor(mktime() / 86400);
                                $pile->push(array('nbr', $nbJours));
                                break;
                            case 'VRAI':
                                $pile->push(array('nbr', 1));
                                break;
                            case 'FAUX':
                                $pile->push(array('nbr', 0));
                                break;
                            default:
                                throw new mmExceptionFormule("Constante '$valeur' non trouvé");
                                break;
                        }
                        $attendOperateur = true;
                    } else {
                        throw new mmExceptionFormule("Un opérateur est attendu or la constante '$valeur' à été fournie");
                    }
                }
                //c'est une chaine
                elseif ($valeur == '"') {
                    if (!$attendOperateur) {
                        $chaine = $this->extraitChaine($expression, $index);

                        //on empile
                        $pile->push(array('str', $chaine));
                        $attendOperateur = true;
                        //on passe a la suite
                        $valeur = '';
                    } else {
                        throw new mmExceptionFormule("$expression: un opérateur est attendu hors une chaine a été trouvée");
                    }
                }
                //c'est une date
                elseif (preg_match("#\d{2}-\d{2}-\d{4}#", $valeur)) {
                    if (!$attendOperateur) {
                        $pile->push(array('dte', str_replace('"', '', $valeur)));
                        $attendOperateur = true;
                    } else {
                        throw new mmExceptionFormule("$expression: date trouvée alors qu'on attend un opérateur");
                    }
                }
                //c'est un nombre
                elseif (is_numeric($valeur)) {
                    //on empile si c'est valide
                    if (!$attendOperateur) {
                        // si jamais on a une negation c'est le moment
                        $last = $pile->top();
                        if ($last[0] == 'op' && $last[1] == '~') {
                            //on retire le '~' de la pile
                            $pile->pop();
                            //on met la valeur inversée
                            $pile->push(array('nbr', $valeur * -1));
                        } else {
                            $pile->push(array('nbr', $valeur));
                        }
                        $attendOperateur = true;
                    } else {
                        throw new mmExceptionFormule("$expression: nombre trouvé alors qu'on attend un opérateur");
                    }
                } else { //C'est rien de tout ce que c'était avant ? c'est une erreur
                    throw new mmExceptionFormule("$valeur: erreur de syntaxe dans l'expression");
                }

                //On passe a la suite
                $index += strlen($valeur);
            } else {
                //on a un bloc non reconnus
                throw new mmExceptionFormule("$expression: erreur de syntaxe dans l'expression. Charactere non reconnus");
            }
        }
        // On met en place les éventuel operateur restant
        while ($pileOperateur->size() > 0) {
            $aEmpiler = $pileOperateur->pop();
            $pile->push(array('op', $aEmpiler));
        }
        // on a fini, on retourne la pile
        return $pile;
    }

    protected function calculPile($pile) {
        $pileCalcul = new mmStack();

        foreach ($pile->pile as $jeton) {
            $type = $jeton[0];
            $valeur = $jeton[1];

            switch ($type) {
                case 'nbr': //on a un nombre
                case 'str': //une chaine
                case 'dte': //ou une date
                case 'rec': //un enregistrement
                case 'col': //une collection d'enregistrement
                case 'arr': //un tableau
                    //pour ces type on ne fais qu'empiler
//          if ($this->debug) echo  "empile: $valeur<br />";
                    $pileCalcul->push($jeton);
                    break;
                case 'op': //on a un operateur
                    //on depile les deux operateur, l'operateur 2 est sur le dessus de la pile
                    $operande2 = $pileCalcul->pop();
                    $operande1 = $pileCalcul->pop();
                    if ($operande1 === null || $operande2 === null || $operande1[0] == 'op' || $operande2[0] == 'op') {
                        //une des deux operande est invalide
                        throw new mmExceptionFormule("Opérateur orphelin $valeur");
                    }
                    if ($this->debug)
                        echo "trouve $valeur => calcul: {$operande1[1]} $valeur {$operande2[1]} = ";
                    //maintenant on traite
                    switch (strtoupper($valeur)) {
                        case '+':
                            $resultat = op_plus($operande1, $operande2);
                            $pileCalcul->push($resultat);
                            break;
                        case '-':
                            $resultat = op_moins($operande1, $operande2);
                            $pileCalcul->push($resultat);
                            break;
                        case '*':
                            $resultat = op_mult($operande1, $operande2);
                            $pileCalcul->push($resultat);
                            break;
                        case '/':
                            $resultat = op_div($operande1, $operande2);
                            $pileCalcul->push($resultat);
                            break;
                        case 'ET':
                            $resultat = op_et($operande1, $operande2);
                            $pileCalcul->push($resultat);
                            break;
                        case 'OU':
                            $resultat = op_ou($operande1, $operande2);
                            $pileCalcul->push($resultat);
                            break;
                        case '=':
                            $resultat = op_eqt($operande1, $operande2);
                            $pileCalcul->push($resultat);
                            break;
                        case '<>':
                            $resultat = op_dif($operande1, $operande2);
                            $pileCalcul->push($resultat);
                            break;
                        case '<':
                            $resultat = op_lt($operande1, $operande2);
                            $pileCalcul->push($resultat);
                            break;
                        case '<=':
                            $resultat = op_let($operande1, $operande2);
                            $pileCalcul->push($resultat);
                            break;
                        case '>':
                            $resultat = op_gt($operande1, $operande2);
                            $pileCalcul->push($resultat);
                            break;
                        case '>=':
                            $resultat = op_get($operande1, $operande2);
                            $pileCalcul->push($resultat);
                            break;
                        default:
                            throw new mmExceptionFormule("operateur $valeur inconnu.");
                            break;
                    }
                    if ($this->debug)
                        echo " {$resultat[1]}<br />";
                    //fin de traitement des operateurs
                    break;

                default:
                    break;
            }
        }

        if ($pileCalcul->size() != 1) {
            throw new mmExceptionFormule("incohérence dans la formule. il reste des élément dans la pile de calcul");
        }
        $final = $pileCalcul->pop();
        return $final;
    }

    public function extraitChaine($expression, &$index) {
        $index++;
        $echapChar = false;
        $char = @$expression[$index];
        $continue = true;
        $chaine = '';
        while ($char != '' && $continue) {
            if ($char == '\\' && !$echapChar) {
                $echapChar = true;
            }
            if ($echapChar) {
                //on passe au suivant
                $index++;
                $char = @$expression[$index];
                $chaine .= $char;
                $index++;
                $char = @$expression[$index];
                $echapChar = false;
            } else {
                if ($char == '"') {
                    //on sort;
                    $continue = false;
                } else {
                    $chaine .= $char;
                    $index++;
                    $char = @$expression[$index];
                }
            }
        }
        //vérification des erreurs
        if ($char == '') {
            throw new mmExceptionFormule("$expression: \" manquant");
        }
        //on passe a la suite
        $index++;

        //on retourne la chaine sans les " ";
        return $chaine;
    }

    public function retVal($jeton) {
        if (is_array($jeton)) {
            switch ($jeton[0]) {
                case 'str':
                    return '"' . addcslashes($jeton[1], '"\\') . '"';
                    break;
                case 'dte':
                    return '"' . $jeton[1] . '"';
                    break;
                default:
                    return $jeton[1];
                    break;
            }
        } else {
            throw new mmExceptionFormule("Recuperation de valeur: un jeton est attendu");
        }
    }

    public function chargeExpression($nomExpression, $table = 'ficfor', $cle = 'pf01', $colExpression = 'pf02') {
        $enr = Doctrine_Core::getTable($table)->createQuery()->
                select($colExpression)->
                where("$cle = ?", $nomExpression)->
                fetchOne();
        if ($enr) {
            return $enr[$colExpression];
        } else {
            //formule inexistante
            throw new mmExceptionFormule("la formule $formule est introuvable");
        }
    }

    protected function executeSi($expression, &$index) {
        $continue = true;
        $nbOuvre = 0;
        $sousExp = '';
        $condition = '';
        $dansCondition = true;
        $vrai = '';
        $dansVrai = false;
        $faux = '';
        $dansFaux = false;
        $char = @$expression[$index];
        while ($char != '' && $continue) {
            if ($char == '"') {
                $sousExp .= '"' . addcslashes($this->extraitChaine($expression, $index), '"\\') . '"';
                $char = @$expression[$index];
            } else {
                switch ($char) {
                    case '(':
                        if ($nbOuvre > 0) {
                            //on est deja dans le bloc () principal
                            $sousExp .= '(';
                        } else {
                            //on commence la sous exp
                            $sousExp = '';
                        }
                        $nbOuvre++;
                        break;
                    case ')':
                        $nbOuvre--;
                        if ($nbOuvre > 0) {
                            //on est deja dans le bloc () principal
                            $sousExp .= ')';
                        } else {
                            //c'est fini on ferme le if
                            if ($dansFaux) {
                                $faux = $sousExp;
                                $continue = false;
                            } else {
                                throw new mmExceptionFormule("$expression: erreur dans la structure du if");
                            }
                        }
                        break;
                    case '?':
                        if ($nbOuvre == 1) {
                            if ($dansCondition) {
                                $condition = $sousExp; // on recupere la condition
                                $dansCondition = false; //Maintenant on se met a attendre le vrai
                                $dansVrai = true;
                                $sousExp = '';
                            } else {
                                throw new mmExceptionFormule("$expression: ? mal placé trouvé");
                            }
                        } else {
                            $sousExp .= $char;
                        }
                        break;
                    case ':':
                        if (@$expression[$index + 1] == '=') { // ce n'est pas un : mais un := donc on ne traite pas
                            $sousExp .= ':';
                        } else {
                            if ($nbOuvre == 1) {
                                if ($dansVrai) {
                                    $vrai = $sousExp;
                                    $dansVrai = false;
                                    $dansFaux = true;
                                    $sousExp = '';
                                } else {
                                    throw new mmExceptionFormule("$expression: ? mal placé trouvé");
                                }
                            } else {
                                $sousExp .= $char;
                            }
                        }
                        break;
                    default:
                        //dans tous les autres cas on concatene
                        $sousExp .= $char;
                        break;
                }
                $index++;
                $char = @$expression[$index];
            }
        }
        // on verifie les erreurs
        if ($nbOuvre > 0) {
            throw new mmExceptionFormule("$expression: ) manquant");
        }
        if (trim($condition) == '') {
            throw new mmExceptionFormule("$expression: condition manquante");
        }
        if (trim($vrai) == '') {
            throw new mmExceptionFormule("$expression: clause VRAI manquante");
        }
        if (trim($faux) == '') {
            throw new mmExceptionFormule("$expression: clause FAUX manquante");
        }

        //Maintenant on interprete
        $resCondition = $this->retVal($this->calcul($condition));
        if ($resCondition) {
            $resultat = $this->calcul($vrai);
        } else {
            $resultat = $this->calcul($faux);
        }

        //on renvois le resultat
        return $resultat;
    }

    protected function executeBloc($expression, &$index, $marqueur = array('(', ')')) {
        $ouvrant = $marqueur[0];
        $fermant = $marqueur[1];

        $continue = true;
        $dansChaine = false;
        $sousExp = '';
        $nbOuvre = 0;
        $char = @$expression[$index];
        while ($char != '' && $continue) {
            if ($char == '"') {
                $dansChaine = !$dansChaine;
            }
            if ($dansChaine) {
                //On est dans une chaine, on traite rien
                $sousExp .= $char;
            } else {
                switch ($char) {
                    case $ouvrant:
                        if ($nbOuvre > 0) {
                            $sousExp.=$ouvrant;
                        }
                        $nbOuvre++;
                        break;
                    case $fermant:
                        $nbOuvre--;
                        if ($nbOuvre > 0) {
                            $sousExp .= $fermant;
                        } else {
                            //derniere parenthese, on quite
                            $continue = false;
                        }
                        break;
                    default:
                        $sousExp .= $char;
                        break;
                }
            }
            //caractere suivant
            $index++;
            $char = @$expression[$index];
        }
        //on verifie les erreurs
        if ($dansChaine) {
            throw new mmExceptionFormule("$expression: chaine non fermé");
        }
        if ($nbOuvre > 0) {
            throw new mmExceptionFormule("$expression: ) manquante");
        }
        //on execute la sous expression
        $resultat = $this->calcul($sousExp);
        //on renvoie si c'est OK
        return $resultat;
    }

    protected function prepareParams($expression, &$index) {
        //on decoupe les parametres
        $tempParams = array();
        $valParam = '';
//    $chaineAnalyse = '';
//    $dansChaine = false;
        $continue = true;
        $nbOuvre = 1;
        $parametres = $expression;
        $char = @$parametres[$index];
        $tranche = substr($expression, $index);
        while ($char != '' && $continue) {
            if ($char == '"') {
                $valParam .= '"' . addcslashes($this->extraitChaine($expression, $index), '"\\') . '"';
                $char = @$expression[$index];
            } else {
                switch ($char) {
                    case ',':
                        if ($nbOuvre == 1) {
                            //on est dans la parenthese principale on place le parametre dans la liste
                            $tempParams[] = trim($valParam);
                            $valParam = '';
                        } else {
                            //sinon c'est une virgule qui nous concerne pas
                            $valParam .= $char;
                        }
                        break;
                    case '(':
                        $nbOuvre++;
                        if ($nbOuvre == 1) {
                            //on ouvre la parenthese principal, on commence un nouveau parametre
                            $valParam = '';
                        } else {
                            //c'est une parenthese interne, on la conserve
                            $valParam .= '(';
                        }
                        break;
                    case ')':
                        if ($nbOuvre == 1) {
                            // parenthese fermante final
                            $tempParams[] = trim($valParam);
                            $valParam = '';
                            $continue = false;
                        } else {
                            // sinon c'est une parenthése interne, on la conserve
                            $valParam .= ')';
                        }
                        $nbOuvre--;
                        break;
                    default:
                        $valParam .= $char;
                        break;
                }
                $index++;
                $char = @$parametres[$index];
                $tranche = substr($expression, $index);
            }
        }
        //On verifie les erreurs
        if ($nbOuvre != 0) {
            throw new mmExceptionFormule("{$expression} : parenthese fermante manquante ou parenthése surnuméraire");
        }
        //on ajoute d'eventuel reste de parametres
        if (trim($valParam) != '') {
            $tempParams[] = trim($valParam);
        }
        //on execute maintenant l'interpretation des parametres
        $tableauParams = array();
        foreach ($tempParams as $expParam) {
            $resultatExp = $this->calcul($expParam);
            if ($resultatExp[0] != 'void') {
                $tableauParams[] = $resultatExp;
            }
//      $retourEval = $this->calcul($expParam);
//      $tableauParams[] = $retourEval[1];
//      $tableauParams[] = $this->retVal($this->calcul($expParam));
        }
        //on retourne la chaine qui a été analysé pour que l'appelant retrouve ses petits
        return $tableauParams;
    }

    /*
     * Ensemble des méthodes correspondant a des fonction du parseur. Ce sont des méthode car ce sont des fonction qui ont besoin d'avoir des références au object et éléments d'interface
     */

    protected function fFunc_desactive($nomWidget, $hardDisable = array('nbr', 1)) {
        if ($this->containerForm != null) {
            if ($nomWidget[0] != 'str') {
                throw new mmExceptionFormule('desactive($nomWidget[, $hardDisable): $nomWidget doit être une chaine');
            }
            $chNom = $nomWidget[1];
            $hard = (int) $hardDisable[1];
            if (isset($this->containerForm[$chNom])) {
                $this->containerForm[$chNom]->disable($hard);
                return array('nbr', 1); // renvois VRAI
            } else {
                //le widget n'existe pas en renvoit FAUX
                return array('nbr', 0);
            }
        } else {
            return array('nbr', 0);
        }
    }

    protected function fFunc_active($nomWidget) {
        if ($this->containerForm != null) {
            if ($nomWidget[0] != 'str') {
                throw new mmExceptionFormule('active($nomWidget[, $hardDisable): $nomWidget doit être une chaine');
            }
            $chNom = $nomWidget[1];
            if (isset($this->containerForm[$chNom])) {
                $this->containerForm[$chNom]->enable();
                return array('nbr', 1); // renvois VRAI
            } else {
                //le widget n'existe pas en renvoit FAUX
                return array('nbr', 0);
            }
        }
    }

    protected function fFunc_desactiveForm($saufBouton = array('nbr', 1)) {
        if ($this->containerForm != null) {
            $sb = $saufBouton[1];
            $this->containerForm->disable($sb);
            return array('nbr', 1);
        } else {
            return array('nbr', 0);
        }
    }

    protected function fFunc_ajouteScript($script) {
        if ($this->containerForm != null) {
            $nomScript = generateRandomString();
            $this->containerForm->addJavascript($nomScript, $script[1]);
            return array('nbr', 1);
        } else {
            return array('nbr', 0);
        }
    }

    //fonction de controle sur les variable
    protected function fFunc_existe($nomVar) {
        if ($nomVar[0] == 'str') {
            if (isset($this->variables[$nomVar[1]])) {
                //renvois VRAI
                return array('nbr', 1);
            } else {
                //renvois faux
                return array('nbr', 0);
            }
        } else {
            throw new mmExceptionFormule('existe($nomVar): $nomVar doit etre une chaine');
        }
    }

    //fonction qui vérifie si l'enregistrement de l'ecran est nouveau ou non
    protected function fFunc_estNouveau() {
        $nv = (int) $this->containerForm->isNew();
        return array('nbr', $nv);
    }

}

/*
 * Fonction de manipulation des données
 */

/**
 * Cette fonction converti un boolean en entier, si le parametre n'est pas un booleen il n'est pas modifié
 * @param type $valeur
 * @return type
 */
function expBool2int($valeur) {
    if ($valeur[0] == 'bol') {
        if ($valeur[1] == 0) {
            return array('int', 0);
        } else {
            return array('int', 1);
        }
    } else {
        return $valeur;
    }
}

function extInt2bool($valeur) {
    
}

/*
 * Ensemble des formules utilisateurs
 */

function op_plus($val1, $val2) {
    if ($val1[0] == 'nbr' && $val2[0] == 'nbr') {
        //si les parametre sont des nombre on additionne
        return array('nbr', $val1[1] + $val2[1]);
    } elseif ($val1[0] == 'dte' && ($val2[0] == 'dte' || $val2[0] == 'nbr')) {
        return fFunc_date(fFunc_datePlus($val1, $val2));
    } else {
        //sinon on fais une concatenation
        return array('str', $val1[1] . $val2[1]);
    }
}

function op_moins($val1, $val2) {
    if ($val1[0] == 'dte') {
        $val1 = fFunc_nbJour($val1);
    }

    if ($val2[0] == 'dte') {
        $val2 = fFunc_nbJour($val2);
    }

    if ($val1[0] == 'nbr' && $val2[0] == 'nbr') {
        //si les parametres sont des nombre on soustraits
        return array('nbr', $val1[1] - $val2[1]);
    } elseif ($val1[0] == 'dte' && ($val2[0] == 'dte' || $val2[0] == 'nbr')) {
        return fFunc_date(fFunc_dateMoins($val1, $val2));
    } else {
        //sinon c'est une erreur
        throw new mmExceptionFormule("Tentative de soustraction sur des valeurs non numeric: {$val1[1]} - {$val2[1]}");
    }
}

function op_mult($val1, $val2) {
    if ($val1[0] == 'nbr' && $val2[0] == 'nbr') {
        //si les parametres sont des nombre on multipli
        return array('nbr', $val1[1] * $val2[1]);
    } else {
        //sinon c'est une erreur
        throw new mmExceptionFormule("Tentative de multiplication sur des valeurs non numeric: {$val1[1]} * {$val2[1]}");
    }
}

function op_div($val1, $val2) {
    if ($val1[0] == 'nbr' && $val2[0] == 'nbr') {
        //si les parametres sont des nombre on soustraits
        if ($val2[1] == 0) {
            throw new mmExceptionFormule("Division par zéro");
        }
        return array('nbr', $val1[1] / $val2[1]);
    } else {
        //sinon c'est une erreur
        throw new mmExceptionFormule("Tentative de division sur des valeurs non numeric: {$val1[1]} / {$val2[1]}");
    }
}

function op_et($val1, $val2) {
    $gauche = $val1[1];
    $droite = $val2[1];
    return array('nbr', (int) ($gauche && $droite));
}

function op_ou($val1, $val2) {
    $gauche = $val1[1];
    $droite = $val2[1];
    return array('nbr', (int) ($gauche || $droite));
}

function op_dif($val1, $val2) {
    $gauche = $val1[1];
    $droite = $val2[1];
    return array('nbr', (int) ($gauche != $droite));
}

function op_eqt($val1, $val2) {
    $gauche = $val1[1];
    $droite = $val2[1];
    return array('nbr', (int) ($gauche == $droite));
}

function op_lt($val1, $val2) {
    $gauche = $val1[1];
    $droite = $val2[1];
    return array('nbr', (int) ($gauche < $droite));
}

function op_let($val1, $val2) {
    $gauche = $val1[1];
    $droite = $val2[1];
    return array('nbr', (int) ($gauche <= $droite));
}

function op_gt($val1, $val2) {
    $gauche = $val1[1];
    $droite = $val2[1];
    return array('nbr', (int) ($gauche > $droite));
}

function op_get($val1, $val2) {
    $gauche = $val1[1];
    $droite = $val2[1];
    return array('nbr', (int) ($gauche >= $droite));
}

function fFunc_int($param) {
    return array('nbr', (int) $param[1]);
}

function fFunc_chaine($nombre) {
    if ($nombre[0] != 'nbr') {
        throw new mmExceptionFormule('chaine($nombre): $nombre doit etre un nombre');
    }
    return array('str', $nombre[1]);
}

function fFunc_func($param1, $param2, $param3) {
    $param3 = str_replace('"', '', $param3);
    return array('str', "$param1 $param2 $param3");
}

function fFunc_rd($decimal, $valeur) {
    if ($decimal[0] != 'nbr' || $valeur[0] != 'nbr') {
        throw new mmExceptionFormule("rd($decimal[1], $valeur[1]): l'expression n'accepte ue des arguments numerique");
    }
    $resultat = floor($valeur[1] / $decimal[1]);
    $resultat = $resultat * $decimal[1];
    return array('nbr', $resultat);
}

function fFunc_ru($decimal, $valeur) {
    if ($decimal[0] != 'nbr' || $valeur[0] != 'nbr') {
        throw new mmExceptionFormule("ru($decimal[0], $valeur[0]): l'expression n'accepte que des arguments numerique");
    }
    $resultat = ceil($valeur[1] / $decimal[1]);
    $resultat = $resultat * $decimal[1];
    return array('nbr', $resultat);
}

function fFunc_min() {
    $listArgs = func_get_args();
    if (count($listArgs) == 0) {
        throw new mmExceptionFormule("max(): aucuns argument fournis");
    }

    $argsFonction = array();
    foreach ($listArgs as $arg) {
        $argsFonction[] = $arg[1];
    }
    $resultat = min($argsFonction);
    if (is_numeric($resultat)) {
        return array('nbr', $resultat);
    } else {
        return array('str', $resultat);
    }
}

function fFunc_max() {
    $listArgs = func_get_args();
    if (count($listArgs) == 0) {
        throw new mmExceptionFormule("max(): aucuns argument fournis");
    }

    $argsFonction = array();
    foreach ($listArgs as $arg) {
        $argsFonction[] = $arg[1];
    }
    $resultat = max($argsFonction);
    if (is_numeric($resultat)) {
        return array('nbr', $resultat);
    } else {
        return array('str', $resultat);
    }
}

function fFunc_date($entierDate = false) {
    //retourne la depuis depuis le 01/01/1970 et le $entierDate jour
    if ($entierDate === false) {
        $entierDate = array('void', 0);
    }
    if ($entierDate[0] != 'nbr' && $entierDate[0] != 'void') {
        throw new mmExceptionFormule("jour(): cette fonction n'accepte qu'un argument numérique.");
    }
    //on fabrique la date
    if ($entierDate[0] == 'void' || $entierDate == false) {
        $seconde = time();
    } else {
        $seconde = $entierDate[1] * 86400;
    }
    return array('dte', date("d-m-Y", $seconde));
}

function fFunc_nbJour($date = false) {
    if ($date === false) {
        $date = fFunc_date();
    }
    if ($date[0] != 'dte' && $date[0] != 'void') {
        throw new mmExceptionFormule("jour(): cette fonction n'accepte qu'un argument date.");
    }
    //on fabrique la date
    if ($date[0] == 'void') {
        $date = fFunc_date();
    }
    $chaineDate = $date[1];
    list($jour, $mois, $an) = explode('-', $chaineDate);
    $seconde = mktime(0, 0, 0, $mois, $jour, $an);
    $jour = ceil($seconde / 86400);
    return array('nbr', $jour);
}

function fFunc_dateMoins($date1, $date2) {
    //verifications
    if ($date1[0] != 'dte') {
        throw new mmExceptionFormule("dateMoins(): le premiers argument doit etre une date");
    }

    if ($date2[0] == 'dte') {
        $date2 = fFunc_nbJour($date2);
    }

    if ($date2[0] != 'nbr') {
        throw new mmExceptionFormule('dateMoins(): le second argument doit etre une date ou un nombre');
    }
    //traitement
    $date1 = fFunc_nbJour($date1);
    $resultat = $date1[1] - $date2[1];

    return array('nbr', $resultat);
}

function fFunc_datePlus($date1, $date2) {
    //verifications
    if ($date1[0] != 'dte') {
        throw new mmExceptionFormule("dateMoins(): le premiers argument doit etre une date");
    }

    if ($date2[0] == 'dte') {
        $date2 = fFunc_nbJour($date2);
    }

    if ($date2[0] != 'nbr') {
        throw new mmExceptionFormule('dateMoins(): le second argument doit etre une date ou un nombre');
    }
    //traitement
    $date1 = fFunc_nbJour($date1);
    $resultat = $date1[1] + $date2[1];

    return array('nbr', $resultat);
}

function fFunc_an($date, $decimal = array('nbr', 4)) {
    if ($date[0] == 'nbr') {
        $date = fFunc_date($date);
    }
    if ($date[0] != 'dte') {
        throw new mmExceptionFormule('an($date, $decimal): l\'argument $date doit etre une date');
    }
    if ($decimal[0] != 'nbr' || ($decimal[1] != 2 && $decimal[1] != 4)) {
        throw new mmExceptionFormule('an($date, $decimal): l\'argument $decimal doit etre un nombre valant 2 ou 4');
    }

    list($jour, $mois, $an) = explode("-", $date[1]);
    if ($decimal[1] == 2) {
        $an = substr($an, 2);
    }

    return array('nbr', $an);
}

function fFunc_mois($date) {
    if ($date[0] == 'nbr') {
        $date = fFunc_date($date);
    }
    if ($date[0] != 'dte') {
        throw new mmExceptionFormule('mois($date): l\'argument $date doit etre une date');
    }
    list($jour, $mois, $an) = explode("-", $date[1]);

    return array('nbr', $mois);
}

function fFunc_jour($date) {
    if ($date[0] == 'nbr') {
        $date = fFunc_date($date);
    }
    if ($date[0] != 'dte') {
        throw new mmExceptionFormule('mois($date): l\'argument $date doit etre une date');
    }
    list($jour, $mois, $an) = explode("-", $date[1]);

    return array('nbr', $jour);
}

function fFunc_bissextile($date) {
    if ($date[0] != 'dte') {
        throw new mmExceptionFormule("bissextile(): l'argument doit etre une date");
    }
}

function fFunc_age($date1, $date2 = false) {
    if ($date1[0] == 'dte') {
        $date1 = fFunc_nbJour($date1);
    }
    if ($date2 === false) {
        $date2 = fFunc_nbJour();
    }
    if ($date2[0] == 'dte') {
        $date2 = fFunc_nbJour($date2);
    }
    //La difference en jours
    $diff = (abs($date2[1] - $date1[1]));

    $anneeTS = fFunc_an(array('nbr', $diff));
    //Correction de la date vu que le pivot est au 01-01-1970
    $age = $anneeTS[1] - 1970;

    //On retourne l'age sous forme d'un nombre
    return array('nbr', $age);
}

//fonction sur les chaine
function fFunc_formate() {
    $listeArgs = func_get_args();
    if (count($listeArgs) < 2) {
        throw new mmExceptionFormule("formate(): nombre incorrecte d'arguments parametres, il faut au moins le format et une variable");
    }
    $argsFonction = array();
    foreach ($listeArgs as $arg) {
        $argsFonction[] = $arg[1];
    }
    return array('str', call_user_func_array('sprintf', $argsFonction));
}

function fFunc_devise($valeur, $decimal = array('nbr', MM_DEVISE_DECIMAL), $symbole = array('str', MM_DEVISE_SYMBOL)) {
    $dec = (int) $decimal[1];
    $sym = $symbole[1];
    $format = "%0.{$dec}f%s";
    $resultat = sprintf($format, $valeur[1], $sym);
    return array('str', $resultat);
}

//fonction d'acces a la base de données
function fFunc_table($nomTable, $cle) {
    $enreg = Doctrine_Core::getTable('Tables')->createQuery()->
            select('valeur')->
            where('id_table = ? AND nom = ?', array($nomTable[1], $cle[1]))->
            fetchOne();
    if ($enreg == false) {
        throw new mmExceptionFormule("fonction table(): aucun enregistrement correspondant trouvé pour {$nomTable[1]}");
    }
    $valeur = $enreg['valeur'];
    if (is_numeric($valeur)) {
        return array('nbr', $valeur);
    } else {
        return array('str', $valeur);
    }
}

function fFunc_indice($code, $date = false) {
    if ($date === false) {
        $date = fFunc_date();
    }
    $dateUS = mmDateFr2Us($date[1]);
    $indice = new mmIndice($code, $dateUS);
    $valIndice = $indice->getValeur();
    if ($valIndice === false) {
        throw new mmExceptionFormule("fonction indice(): l'indice $code est inconnu");
    }
    return array('nbr', $valIndice);
}

/*
 * Fonction a activer et tester
 */

function fFunc_chargeEnregistrement($table, $condition, $tri = false) {
    //initialisation et vérification
    if ($table[0] != 'str') {
        throw new mmExceptionFormule('chargeEnregistrement($table, $condition[, $tri]): $table doit etre une chaine de characters');
    }
    $table = $table[1];

    if ($condition[0] != 'str') {
        throw new mmExceptionFormule('chargeEnregistrement($table, $condition[, $tri]): $condition doit etre une chaine de characters');
    }
    $condition = $condition[1];

    if ($tri !== false) {
        if ($tri[0] != 'str') {
            throw new mmExceptionFormule('chargeEnregistrement($table, $condition[, $tri]): $tri doit etre une chaine de characters');
        }
        $tri = $tri[1];
    }

    try {
        $res = Doctrine_Core::getTable($table)->createQuery()->
                where($condition);
        if ($tri != '') {
            $res = $res->orderBy($tri);
        }
        $res = $res->fetchOne();
        if (!$res) {
            //On a pas d'enregistrement, on renvoix FAUX
            return array('nbr', 0);
        } else {
            //On a un enregistrement, on renvois cette ressource
            return array('rec', $res);
        }
    } catch (Exception $e) {
        //une erreur d'execution, on informe l'utilisateur et on renvois 'FAUX'
        mmUser::flashError("Une erreur d'execution s'est produite lors du chargement d'un enregistrement<br />" . $e->getMessage());
        return array('nbr', 0);
    }
}

function fFunc_chargeCollection($table, $condition, $tri = '') {
    if ($table[0] != 'str') {
        throw new mmExceptionFormule('chargeCollection($table, $condition[, $tri]): $table doit etre une chaine de characters');
    }
    $table = $table[1];

    if ($condition[0] != 'str') {
        throw new mmExceptionFormule('chargeCollection($table, $condition[, $tri]): $condition doit etre une chaine de characters');
    }
    $condition = $condition[1];

    if ($tri !== false) {
        if ($tri[0] != 'str') {
            throw new mmExceptionFormule('chargeCollection($table, $condition[, $tri]): $tri doit etre une chaine de characters');
        }
        $tri = $tri[1];
    }

    try {
        $res = Doctrine_Core::getTable($table)->createQuery()->
                where($condition);
        if ($tri != '') {
            $res = $res->orderBy($tri);
        }
        $res = $res->execute();
        if ($res->count == 0) {
            //On a pas d'enregistrement, on renvoix FAUX
            return array('nbr', 0);
        } else {
            //On a un enregistrement, on renvois cette ressource
            return array('col', $res);
        }
    } catch (Exception $e) {
        //une erreur d'execution, on informe l'utilisateur et on renvois 'FAUX'
        mmUser::flashError("Une erreur d'execution s'est produite lors du chargement d'une collection<br />" . $e->getMessage());
        return array('nbr', 0);
    }
}

/*
 * Fonction de gestion des droits
 */

function fFunc_superAdmin() {
    return array('nbr', (int) mmUser::superAdmin());
}
