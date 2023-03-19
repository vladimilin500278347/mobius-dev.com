<?php

class actionWysiwygsPresetsDelete extends cmsAction {

    public function run($id){

        if (!cmsForm::validateCSRFToken($this->request->get('csrf_token', ''))){
            cmsCore::error404();
        }

        $preset = $this->model->getPreset($id);
        if (!$preset) { cmsCore::error404(); }

        $this->model->deletePreset($preset['id']);

        cmsUser::addSessionMessage(LANG_DELETE_SUCCESS, 'success');

        $this->redirectToAction('presets');

    }

}
