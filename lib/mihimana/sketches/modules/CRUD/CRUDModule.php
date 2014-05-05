<?php


/**
 * Description of CRUDModule
 *
 * @author bruno
 */
class CRUDModule extends mmProg {
    public function executeIndex(mmRequest $request) {
        
    }

    public function executeNew(mmRequest $request) {
        if ($tagParent = $request->get('key', false)) {
            $this->initNew($tagParent);
            $this->initForm();
            $this->form->setAction(url('@module/create/'.$tagParent));
        } else {
            mmUser::flashError('Aucune tag recu');
            $this->redirect('@module');
        }
    }

    public function executeCreate(mmRequest $request) {
        $data = $request->get('habitat_surface', false);
        if ($data) {
            $this->executeNew($request);
            if ($this->form->setValues($data)) {
                $this->form['tag'] = \linke\genererTag($data);
                $this->form->save();
                $this->redirect(url('domicile/show/'.$this->form->getValue('id_surface_parent')));
            } else {
                mmUser::flashError('Il y\'a des erreur de saisie');
                $this->setTemplate('#new');
            }
        }
    }

    public function executeEdit (mmRequest $request) {
        $this->setTemplate('#new');
        if ($tag = $request->get('key', false)) {
            $this->surface = Doctrine_Core::getTable('HabitatSurface')->findOneByTag($tag);
            $this->initForm();
            $this->form->setAction(url('@module/update/'.$tag));
        } else {
            mmUser::flashError('Tag manquant');
            $this->redirect(url('@home'));
        }
    }

    public function executeUpdate(mmRequest $request) {
        $this->setTemplate('#new');
        $this->executeEdit($request);
        if ($data = $request->get('habitat_surface', false)) {
            if ($this->form->setValues($data)) {
                $this->form->save();
                mmUser::flashSuccess('Enregistrement effectué');
            } else {
                mmUser::flashError('Erreur de saisie');
            }
        } else {
            mmUser::flashError('Aucunes données recues');
            $this->redirect(url('@home'));
        }
    }

    public function executeDelete(mmRequest $request) {
        $tag = $request['key'];
        $surface = Doctrine_Core::getTable('HabitatSurface')->findOneByTag($tag);
        $surface->delete();
        mmUser::flashSuccess('Parois supprimées');
    }
    private function initNew($tagParent) {
        $surfaceParent = mmSQL::queryOne("SELECT * FROM habitat_surface WHERE tag = '$tagParent'");
        $surface = new HabitatSurface();
        $surface['id_habitat_piece'] = $surfaceParent['id_habitat_piece'];
        $surface['id_surface_parent'] = $surfaceParent['id'];

        $this->surface = $surface;
    }

    private function initForm() {
        $form = new mmForm($this->surface);
        //widget de validation
        $form->addWidget(new mmWidgetButtonSubmit());
        //parametrage du formulaire
        $form->addWidget(new mmWidgetSelectTable($form['type_surface'], 'HABITAT_SURFACE_TYPE_SURFACE'));
        $form->addWidget(new mmWidgetSelectTable($form['horizontale'], 'SURFACE_ORIENTATION'));

        $this->form = $form;
    }

}
