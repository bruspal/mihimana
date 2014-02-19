CKEDITOR.plugins.add('mdFieldSet',
  {
    init: function( editor )
    {
    //Le code commence ici
    editor.addCommand('mdInsertFieldSet',
      {
        exec: function(editor)
        {
          editor.insertHtml("<fieldset><legend>TITRE</legend>TEXT</fieldset>");
        }
      }
    );
    editor.ui.addButton('mdPdfPageBreak',
      {
        label: 'Inserer une frameset',
        command: 'mdInsertFieldSet',
        icon: this.path + 'images/frameset.jpg'
      }
    );
    }
  }
);  