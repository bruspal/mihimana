<?php
/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: builtinModule/templates
  @file : html_pEcran_editEcran.php
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


$ecran = $form->getRecord();

//en attendant de mettre en place le system de generation de la balise form
if ($form->isNew()) {
    $action = url('pEcran/creerEcran');
} else {
    $action = url('pEcran/majEcran');
}
$form->setAction($action);
?>
<h1>Edition de l'écran</h1>
<?php echo $form->start() ?>
<fieldset>
    <?php echo $form->renderButtons() ?>
    <table style="width: auto;">
        <tr>
            <th style="width: 20ex;">Nom de l'ecran :</th><td><?php echo $form['nom_ecran'] . $form['nom_ecran']->renderErrors() ?></td>
            <th style="width: 20ex;">| Table associées :</th><td><?php echo $form['table_liee'] . $form['table_liee']->renderErrors(); ?></td>
            <th style="width: 20ex;">| Mode de rendu :</th><td><?php echo $form['mode_rendu'] ?></td>
            <th style="width: 20ex;">| Destination :</th><td><?php echo $form['destination'] ?></td>
        </tr>
    </table>
    <?php echo $form['template'] ?>
</fieldset>
<?php echo $form->stop() ?>
<?php echo $form->renderJavascript() ?>

<script type="text/javascript">
    var carCurseur = '';
    var cPosition = 0;
    var saisieModifie = false;

    function editAttribut()
    {
        champSelectionne = $('#liste_champ_id').val();
        mmAjaxHtmlDialog('<?php echo url('pEcranPopup/editer?c=') ?>' + champSelectionne);
    }

    function assignVar()
    {
        contenu = $('#<?php echo $form['template']->getId() ?>').val();
        nbrVar = contenu.split(/\$/g).length - 1;
        mmAjaxHtmlDialog('<?php echo url('pEcranService/editer') ?>');
    }

    function reinitSaisie()
    {
        $("#__mdDialog").jqmHide();
        cleanTextArea();
    }

    function cleanTextArea()
    {
        txt = $('#<?php echo $form['template']->getId() ?>').val();
        partie1 = txt.substr(0, cPosition - 1);
        partie2 = txt.substr(cPosition);
        $('#<?php echo $form['template']->getId() ?>').val(partie1 + partie2);
    }

    function nettoieChaineVariable(position)
    {
        txt = $('#<?php echo $form['template']->getId() ?>').val();
        partie1 = txt.substr(0, cPosition);
        partie2 = txt.substr(cPosition);
        posDebut = partie1.lastIndexOf('$');
        posFin = partie2.indexOf(' ') + position;
        nvText = txt.substr(0, posDebut) + txt.substr(posFin);
        $('#<?php echo $form['template']->getId() ?>').val(nvText);

    }

    function insertChaine(chaine)
    {
        txt = $('#<?php echo $form['template']->getId() ?>').val();
        partie1 = txt.substr(0, cPosition);
        partie2 = txt.substr(cPosition);
        $('#<?php echo $form['template']->getId() ?>').val(partie1 + chaine + partie2);
    }

    function InsEditVar(champText, positionCurseur)
    {
        var carCurseur = champText.val().substr(positionCurseur - 1, 1);
        if (carCurseur == '$')
        {
            //on verifie si c'est un insert ou une edition
            var autreCar = champText.val().substr(positionCurseur, 1);
            if (autreCar == '$')
            {
                var finSaisie = champText.val().substr(positionCurseur + 1);
                var positionFinVar = finSaisie.indexOf(' ');
                var nomChamp = finSaisie.substr(0, positionFinVar);
                mmAjaxHtmlDialog('<?php echo url('pEcranAssign?o=e&champ=') ?>' + nomChamp, 'Modification de la variable ' + nomChamp);
                cleanTextArea();
            }
            else
            {
                mmAjaxHtmlDialog('<?php echo url('pEcranAssign?o=i') ?>', 'Insertion d\'une variable');
            }
        }
    }


    $(document).ready(function() {

//    $('#nom_ecran_id').change(function(){
//      goPage('?module=pEcran&action=editEcran&ecran='+$(this).val());
//    });

        $(window).bind('beforeunload', function() {
            if (saisieModifie)
            {
                //      return 'La saisie &agrave; &eacute;t&eacute; modifi&eacute;. Si vous quittez cette page toutes les information vont etre perdue.';
                //      $('#ecran_utilisateur_id').submit();
            }
        });

        $('#supprimer_id').click(function() {
            if (confirm('Souhaitez vous supprimer cet écran ? Cette opération est irréversible.')) {
                goPage('<?php echo url('pEcran/supprimer?ecran') ?>' + $('#nom_ecran_id').val());
            }
        })

//    $('#<?php echo $form['template']->getId() ?>').blur(function(){
//      //on recupere les infos e position lorsqu'on perd le focus
//      cPosition = $(this).getCursorPosition();
//      carCurseur = $(this).val().substr(cPosition, 1);
//    });

        $('#<?php echo $form['template']->getId() ?>').dblclick(function() {
            cPosition = $(this).getCursorPosition();
            insertChaine('$');
            cPosition--;
            InsEditVar($(this), cPosition);
        });

        $('#<?php echo $form['template']->getId() ?>').keyup(function(event) {
            //On verifie que tout roule
            touche = event.which;
            if (touche < 48 || (touche >= 91 && touche <= 93) || (touche >= 112 && touche <= 123) || touche == 221)
            {
                return false;
            }
            else
            {
                cPosition = $(this).getCursorPosition();
                carCurseur = $(this).val().substr(cPosition - 1, 1);
                var sousChaine = $(this).val().substr(0, cPosition);
                var recherche = sousChaine.match(/\$/g);
                if (recherche == null)
                {
                    var nbInsertion = 1;
                }
                else
                {
                    var nbInsertion = recherche.length;
                }

                if (carCurseur == '$')
                {
                    //on verifie si c'est un insert ou une edition
                    var autreCar = $(this).val().substr(cPosition, 1);
                    if (autreCar == '$')
                    {
                        var finSaisie = $(this).val().substr(cPosition + 1);
                        var positionFinVar = finSaisie.indexOf(' ');
                        var nomChamp = finSaisie.substr(0, positionFinVar);
                        mmAjaxHtmlDialog('<?php echo url('pEcranAssign?o=e&champ=') ?>' + nomChamp, 'Modification de la variable ' + nomChamp);
                        cleanTextArea();
                    }
                    else
                    {
                        mmAjaxHtmlDialog('<?php echo url('pEcranAssign?o=i') ?>', 'Insertion d\'une variable');
                    }
                }
            }
        })

        $('#<?php echo $form['template']->getId() ?>').keydown(function(event) {

            cPosition = $(this).getCursorPosition();
            carCurseur = $(this).val().substr(cPosition, 1);

            var sousChaine = $(this).val().substr(0, cPosition);
            var recherche = 0;

            //on considere que la page a été modifier des qu'une touche est pressé dans le blob
            saisieModifie = true;

            $('#debugZone').append('down');

            //on met a jour l'interface des qu'on tape un caractere
            $('#apercu_id').hide();

            if (event.which == 112)
            {
                mmAjaxHtmlDialog('aide/aideEcran.php');
//        openWindow('aide/aideEcran.php');
                event.stopPropagation();
                event.preventDefault();
            }
            if (false && ((event.which == 52 || event.which == 59) && !event.shiftKey && !event.ctrlKey && !event.altKey) || event.which == 112) {
                //On appel l'ecran d'assignation. Avant on regarde si on insert un '$'
                $('#retour_id').hide();
                if (carCurseur == '$')
                {
                    mmAjaxHtmlDialog('<?php echo url('pEcranAssign?o=e&n') ?>' + nbInsertion, 'Assignation de la variable N°' + nbInsertion);
                    event.preventDefault(); //on arrete le comportement par defaut. i.e. on interdit l'insertion du $'
                }
                else
                {
                    mmAjaxHtmlDialog('<?php echo url('pEcranAssign?o=i&n=') ?>' + nbInsertion, 'Assignation de la variable N°' + nbInsertion);
                }
                //on cache les truc pour fermer
//        $('.mdModalClose').hide();
            }
            if (false && (event.which == 8 || event.which == 46)) {
                sel = $(this).getSelectedText();
                if (true || sel.indexOf('$') == -1) {
                    if (event.which == 8)
                    {
                        if (nbInsertion <= 1)
                        {
                            nbInsertion = 1;
                        }
//            else
//            {
//              nbInsertion--;
//            }
                        carSupprime = $(this).val().substr(cPosition - 1, 1);
                        if (carSupprime == '$')
                        {
                            recherche = sousChaine.match(/\$/g);
                            if (recherche == null)
                            {
                                var nbInsertion = 1;
                            }
                            else
                            {
                                var nbInsertion = recherche.length;
                            }

                            mmAjaxHtmlDialog('<?php echo url('pEcranAssign/supprime?n=') ?>' + nbInsertion, 'Suppression de la variable N°' + nbInsertion);
                        }
                    }
                    if (event.which == 46)
                    {
                        carSupprime = $(this).val().substr(cPosition, 1);
                        if (carSupprime == '$')
                        {
                            recherche = sousChaine.match(/\$/g);
                            if (recherche == null)
                            {
                                var nbInsertion = 1;
                            }
                            else
                            {
                                var nbInsertion = recherche.length + 1;
                            }
                            mmAjaxHtmlDialog('<?php echo url('pEcranAssign/supprime?n=') ?>' + nbInsertion, 'Suppression de la variable N°' + nbInsertion);
                        }
                    }
                }
                else {
                    mdPopup('La selection comporte des $.Suppression annulée');
                    event.preventDefault();
                }
            }
        });

        /*
         * Traitements specifique a CKeditor
         */
<?php if ($ecran['mode_rendu'] == 'htm'): ?>
            CKEDITOR.instances.<?php echo $form['template']->getId() ?>.on('contentDom', function() {
                CKEDITOR.instances.<?php echo $form['template']->getId() ?>.document.on('keydown', function(event) {
                    if (event.data.$.which == 121)
                    {
                        event.data.$.preventDefault();
                        $('#apercu_id').hide();
                        selection = CKEDITOR.instances.<?php echo $form['template']->getId() ?>.getSelection().getSelectedText();
                        if (selection.trim() == '')
                        {
                            mmAjaxHtmlDialog('<?php echo url('pEcranAssign?o=i') ?>', 'Insertion de la variables');
                        }
                        else
                        {
                            mmAjaxHtmlDialog('<?php echo url('pEcranAssign?o=e&champ=') ?>' + selection, 'Modification de la variable ' + selection);
                        }
                    }
                });

                CKEDITOR.instances.<?php echo $form['template']->getId() ?>.document.on('dblclick', function(event) {
                    $('#apercu_id').hide();
                    selection = CKEDITOR.instances.<?php echo $form['template']->getId() ?>.getSelection().getSelectedText();
                    if (selection.trim() == '')
                    {
                        mmAjaxHtmlDialog('<?php echo url('pEcranAssign?o=i') ?>', 'Insertion de la variables');
                    }
                    {
                        mmAjaxHtmlDialog('<?php echo url('pEcranAssign?o=e&champ=') ?>' + selection, 'Modification de la variable ' + selection);
                    }
                });
            });
<?php endif; ?>
    });
</script>