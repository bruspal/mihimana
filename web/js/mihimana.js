/*
 * Global variables
 */

/*
 * Fonctions génériques
 */
function openWindow(url, windowName, type) {
    if (windowName == undefined) {
        windowName = 'subWindow';
    }
    switch (type) {
        case 'large':
            wOption = "height=800, width=1000, top=0, left=0, toolbar=no, menubar=no, location=no, resizable=no, scrollbars=yes, status=no";
        default:
            wOption = "height=710, width=920, top=100, left=100, toolbar=no, menubar=no, location=no, resizable=no, scrollbars=yes, status=no";
            break;
    }
    window.open(url, windowName, wOption);
}

function goPage(url) {
    document.location.href = url;
}

function refreshParent() {
    window.parent.opener.location.reload();
}

function goPageParent(url)
{
    window.parent.opener.location.href = url;
}

/*
 * Manipulation des widgets
 */
function addToSelect(idSelect, dataObject) {
    $.each(dataObject, function(key, label) {
        $(idSelect).append(
                $('<option></option>').val(key).html(label)
                )
    });
}

/*
 * Fonction de gestion ajax
 */
$.ajaxSetup({
    type: "GET",
    statusCode: {
        404: function() {
            mmPopup('Page introuvable')
        },
        500: function() {
            mmPopup('Erreur interne')
        }
    },
    error: function(XHR, textError) {
        mmPopup("Erreur: " + textError + "\n" + XHR.responseText + "\nLa session a expire veuillez vous reconnecter");
    },
    dataType: "json"
});

function mmAjaxSubmit(formObject, resultContainer) {
    if (resultContainer == undefined)
    {
        resultContainer = mmGetModalContainer('__mmDialog');
    }
    $.ajax({
        url: formObject.attr('action'),
        data: formObject.serialize(),
        type: 'POST',
        dataType: 'html',
        success: function(data) {
            resultContainer.find('.mmModalContent').html(data);
        }
    });
}

function mmAjaxChangeAction(formObject, nvAction) {
    formObject.attr('action', nvAction);
}


//Gestion des popup
function ajxPopup(content, title)
{
    alert('desuet. Utiliser mmPopup');
    mmPopup(content, file);
}

function mmPopup(content, title) {
    if (content == undefined) {
        content = '';
    }
    if (title == undefined) {
        title = 'Informations';
    }
    mmGetModalContainer('__mmPopup');
    $('#__jqmPopup').jqm({
        modal: true
    });
    $('#__mmPopup .title').html(title);
    $('#__mmPopup .mmModalContent').html(content);
    $('#__mmPopup').jqmShow();
}

function mmAjaxHtmlDialog(remote, title) {
    if (title == undefined) {
        title = 'Fenetre';
    }
    if (remote.indexOf('?') == -1) { //on force la réponse sous forme http
        remote += '?_fhr_=1';
    } else {
        remote += '&_fhr_=1';
    }

    mmGetModalContainer('__mmDialog');
    $('#__mmDialog .title').text(title);

    $('#__mmDialog').jqm({
        ajax: remote,
        modal: true,
        target: $('#__mmDialog .mmModalContent')
    });
    $('#__mmDialog').jqmShow();
}

function mmGetModalContainer(containerName)
{
    container = $('#' + containerName);
    if (container.length == 0)
    {
        container = $('<div>').addClass('mmModalWindow').attr('id', containerName).hide();
        container.append($('<div>').addClass('windowBar').append($('<span>').addClass('title').addClass('mmModalClose')).append($('<span>').addClass('mmXClose').addClass('mmModalClose').text('[X]')));
        container.append($('<div>').addClass('mmModalContent'));
        //    container.append($('<div>').addClass('mmModalMenu').append($('<button>').addClass('mmButtonClose').addClass('mmModalClose').text('Fermer')));
        container.jqm({
            closeClass: 'mmModalClose',
            overlayClass: 'mmModalOverlay'
        });
        $(document.body).append(container);
    }

    return container;
}

/*
 * Gestion des widgets
 */
function mmAjaxSubWindow(idSubWindow, remote)
{
    $.ajax({
        url: remote,
        success: function(retour) {
            if (retour.success)
            {
                htmlRes = retour.data.replace('<_script', '<script').replace('<_/script', '</script');
                //        $('#'+idSubWindow+' .subContent').html(htmlRes);
                $('#' + idSubWindow + ' .subContent')[0].innerHTML = htmlRes;
                $('#' + idSubWindow).show();
            }
            else
            {
                mmPopup(retour.message, 'Erreur');
            }
        }
    });
}

function _pageRecordListe(nom, id, ordre)
{
    $.ajax({
        url: '?module=pWs&action=pg&o=' + ordre + '&l=' + nom,
        success: function(data) {
            if (data.success)
            {
                $('#' + id + '').html(data.html);
            }
            else
            {
                mmPopup('Impossible de recupérer la liste');
            }
        }
    });
}


/*
 * Ensemble des fonction specifique a Maides
 */

//Variable globale
var errObject = {};

function mmJsCheckLengthMax(jqObject, rule) {
//    $(jqObject).blur(function() {
        displayError(jqObject, jqObject.val().length > rule, 'Le champ ne doit pas exceder ' + rule + ' characteres', 'Erreur');
//    });
}

function mmJsCheckNotnull(jqObject) {
//    jqObject.blur(function() {
        displayError(jqObject, jqObject.val().length == 0, 'Le champs ne peux pas etre vide');
//    });
}

function mmJsCheckInteger(jqObject) {
//    jqObject.blur(function() {
        displayError(jqObject, ! /^\d*$/.test(jqObject.val()), 'Le champ doit contenir un entier');
//        displayError(jqObject, false, 'Le champ doit contenir un entier');
//    });
}

function mmJsCheckReal(jqObject) {
//    jqObject.blur(function() {
        displayError(jqObject, !/^\d*.?\d*$/.test(jqObject.val()), 'Le champ doit contenir un nombre');
//    });
}

function mmJsCheckTime(jqObject, withSec) {

    if (withSec == undefined) {
        withSec = false;
        //todo: implementer les seconde plus tard, deja present dans la version PHP
    }

//    jqObject.blur(function() {
        valeur = jqObject.val();
        if (valeur == '')
            return '';

        if (valeur.length == 4) {
            valeur = valeur.substring(0, 2) + ':' + valeur.substring(2, 4);
        }
        hour = parseInt(valeur.substring(0, 2));
        min = parseInt(valeur.substring(3, 5));
        error = false;
        if (displayError(jqObject, hour < 0 || hour > 24, 'L\'heure doit etre entre 00 et 24'))
            error = true;
        ;
        if (displayError(jqObject, min < 0 || min > 60, 'Les minutes doivent etre entre 00 et 60'))
            error = true;
        if (!error) {
            if (hour < 10)
                hour = '0' + hour;
            if (min < 10)
                min = '0' + min;
            jqObject.val(hour + ':' + min);
        }
//    });
}

function mmJsCheckDate(jqObject) {

    function dateValide(d) {
        var dateRegEx = /^((((0?[1-9]|[12]\d|3[01])[\.\-\/](0?[13578]|1[02])[\.\-\/]((1[6-9]|[2-9]\d)?\d{2}))|((0?[1-9]|[12]\d|30)[\.\-\/](0?[13456789]|1[012])[\.\-\/]((1[6-9]|[2-9]\d)?\d{2}))|((0?[1-9]|1\d|2[0-8])[\.\-\/]0?2[\.\-\/]((1[6-9]|[2-9]\d)?\d{2}))|(29[\.\-\/]0?2[\.\-\/]((1[6-9]|[2-9]\d)?(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)|00)))|(((0[1-9]|[12]\d|3[01])(0[13578]|1[02])((1[6-9]|[2-9]\d)?\d{2}))|((0[1-9]|[12]\d|30)(0[13456789]|1[012])((1[6-9]|[2-9]\d)?\d{2}))|((0[1-9]|1\d|2[0-8])02((1[6-9]|[2-9]\d)?\d{2}))|(2902((1[6-9]|[2-9]\d)?(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)|00))))$/;
        return d.match(dateRegEx);
    }

//    jqObject.blur(function() {
        valeur = jqObject.val();
        if (valeur == '')
            return '';

        if (valeur.length == 8 && valeur.indexOf('-') == -1 && valeur.indexOf('/') == -1) {
            valeur = valeur.substring(0, 2) + "-" + valeur.substring(2, 4) + "-" + valeur.substring(4);
        }

        if (valeur.length == 6) {
            annee = valeur.substring(4);
            if (parseInt(annee) < 30) {
                siecle = '20';
            }
            else {
                siecle = '19';
            }
            valeur = valeur.substring(0, 2) + "-" + valeur.substring(2, 4) + "-" + siecle + annee;
        }

        displayError(jqObject, !dateValide(valeur), 'La date saisie n\'est pas valide. le format est jjmmaa, jjmmaaaa, jj-mm-aa ou jj-mm-aaaa.\nLe mois entre 01 et 12.\nLe jour entre 01 et 31.', 'Erreur')
        if (dateValide(valeur)) {
            jqObject.val(valeur);
        }
//    });
}

function mmJsCheckEmail(jqObject) {
//    jqObject.blur(function(){
        var rexp = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
        displayError(jqObject, ! rexp.test(jqObject.val()), 'adresse email invalide');
//    });
}

//Gestion des erreurs
function displayError(jqObject, testError, message) {
    id = '#er_' + jqObject.attr('id');
    uid = stringUID(message);
    if (jqObject['__errArray__'] == undefined) {
        jqObject['__errArray__'] = {};
    }
    if (testError) {
//        eval('errObject.' + uid + '="' + message + '"');
        jqObject['__errArray__'][uid] = message;
        jqObject.addClass('mmError');
    }
    else {
//        delete errObject[uid];
        delete jqObject['__errArray__'][uid];
        jqObject.removeClass('mmError');
    }
    //on redessine la zone d'erreur'
    $(id).empty();
    for (mess in jqObject['__errArray__']) {
        $(id).append('<div>' + jqObject['__errArray__'][mess] + '</div>');
    }

    return testError;
}

//utilitaire
function stringUID(enter) {
    cleaned = enter.replace(/[ \-\+@,?\.\:!'"]*/gi, '').toUpperCase();
    result = cleaned.substr(Math.round(cleaned.length / 2) - 3, 6) + cleaned.length;
    return result;
}

function listToList(src, dest, ordered) {
    //  http://www.geekeries.com/2009/04/24/comment-bien-demarrer-avec-jquery/
    if (ordered == undefined) {
        ordered = true;
    }
    $('#' + src + ' option:selected').each(function() {
        $('#' + dest).append($(this));
    });
    if (ordered) {
        $('#' + dest + ' option').get().sort(function(a, b) {
            keyA = $(a).val().toUpperCase();
            keyB = $(b).val().toUpperCase();
            if (keyA < keyB)
                return -1;
            if (keyA > keyB)
                return 1;
            return 0;
        });
    }
}


/*
 * Ajout de fonction plugin jquery
 */
//Manipulation des textarea
(function($) {
    $.fn.getCursorPosition = function() {
        var input = this.get(0);
        if (!input)
            return; // No (input) element found
        if ('selectionStart' in input) {
            // Standard-compliant browsers
            return input.selectionStart;
        } else if (document.selection) {
            // IE
            input.focus();
            var sel = document.selection.createRange();
            var selLen = document.selection.createRange().text.length;
            sel.moveStart('character', -input.value.length);
            return sel.text.length - selLen;
        }
    }

    $.fn.getSelectedText = function() {
        var input = this.get(0);
        if (!input)
            return false; //pas d'element valide'
        if ('selectionStart' in input) {
            pStart = input.selectionStart;
            pStop = input.selectionEnd;
            iText = input.value;
            result = iText.substr(pStart, pStop - pStart);
        }
        else {
            alert("Fonction non supporté par le navigateur (IE ?). soit faut attendre qu'elle soit adapté. Soit faut utiliser un navigateur compatible");
            return input.val();
        }
        return result;
    }
})(jQuery);

/*
 * plugin ckEditor
 */

// Full sample plugin, which does not only register a dialog window but also adds an item to the context menu.
// To open the dialog window, choose "Open dialog" in the context menu.

CKEDITOR.plugins.add('myplugin',
        {
            init: function(editor)
            {
                editor.addCommand('mydialog', new CKEDITOR.dialogCommand('mydialog'));

                if (editor.contextMenu)
                {
                    editor.addMenuGroup('mygroup', 10);
                    editor.addMenuItem('My Dialog',
                            {
                                label: 'Open dialog',
                                command: 'mydialog',
                                group: 'mygroup'
                            });
                    editor.contextMenu.addListener(function(element)
                    {
                        return {
                            'My Dialog': CKEDITOR.TRISTATE_OFF
                        };
                    });
                }

                CKEDITOR.dialog.add('mydialog', function(api)
                {
                    // CKEDITOR.dialog.definition
                    var dialogDefinition =
                            {
                                title: 'Sample dialog',
                                minWidth: 390,
                                minHeight: 130,
                                contents: [
                                    {
                                        id: 'tab1',
                                        label: 'Label',
                                        title: 'Title',
                                        expand: true,
                                        padding: 0,
                                        elements:
                                                [
                                                    {
                                                        type: 'html',
                                                        html: '<p>This is some sample HTML content.</p>'
                                                    },
                                                    {
                                                        type: 'textarea',
                                                        id: 'textareaId',
                                                        rows: 4,
                                                        cols: 40
                                                    }
                                                ]
                                    }
                                ],
                                buttons: [CKEDITOR.dialog.okButton, CKEDITOR.dialog.cancelButton],
                                onOk: function() {
                                    // "this" is now a CKEDITOR.dialog object.
                                    // Accessing dialog elements:
                                    var textareaObj = this.getContentElement('tab1', 'textareaId');
                                    alert("You have entered: " + textareaObj.getValue());
                                }
                            };

                    return dialogDefinition;
                });
            }
        });

function showHelp(widget)
{
    $('#' + widget).mouseenter(function() {
        $('#a_' + widget).show();
    });
    $('#' + widget).mouseleave(function() {
        $('#a_' + widget).hide();
    });
}
