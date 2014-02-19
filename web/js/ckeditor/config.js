/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
        config.language = 'fr';
	config.uiColor = '#CCCCFF';
        config.protectedSource.push(/{@%[\s\S]*?%@}/g);
        config.protectedSource.push(/<\s*\/?page\s*>/g);
        config.height = '500px';
        config.enterMode = CKEDITOR.ENTER_BR;
        config.shiftEnterMode = CKEDITOR.ENTER_DIV;
        
        
};
