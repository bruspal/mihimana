/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	config.language = 'fr';
//        config.uiColor = '#AADC6E';
        config.protectedSource.push(/{@%[\s\S]*?%@}/g);
        config.protectedSource.push(/<\s*\/?page\s*>/g);
//        config.extraPlugins = 'tableresize,mdPdfPageBreak,mdFieldSet,stylesheetparser,devtools'; //pour dev des plugins
        config.extraPlugins = 'tableresize,mdPdfPageBreak,mdFieldSet,stylesheetparser';
//        config.extraPlugins = 'tableresize,stylesheetparser';
        config.contensCss = ['/css/main.css', '/css/maides.css'];
        config.height = '500px';

        config.toolbar = 
        [
            { name: 'document',    items : [ 'Source','-','Save','NewPage','DocProps','Preview','Print' ] },
            { name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
            { name: 'editing',     items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
            { name: 'PDF',         items : [ 'mdPdfPageBreak', 'mdFieldSet'] },
            '/',
            { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
            { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
            { name: 'links',       items : [ 'Link','Unlink','Anchor' ] },
            { name: 'insert',      items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak' ] },
            '/',
            { name: 'styles',      items : [ 'Styles','Format','Font','FontSize' ] },
            { name: 'colors',      items : [ 'TextColor','BGColor' ] },
            { name: 'tools',       items : [ 'Maximize', 'ShowBlocks','-','About','devtools' ] }
        ];

};
