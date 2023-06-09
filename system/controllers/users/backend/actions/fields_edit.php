<?php

class actionUsersFieldsEdit extends cmsAction {

    public function run($field_id) {

        if (!$field_id) {
            return cmsCore::error404();
        }

        $content_model = cmsCore::getModel('content');

        $content_model->setTablePrefix('');

        $form = $this->getForm('field', ['edit']);
        $form = cmsEventsManager::hook('user_field_form', $form);

        $field = $content_model->getContentField('{users}', $field_id);
        if (!$field) {
            return cmsCore::error404();
        }

        // скроем поле "Системное имя" для фиксированных полей
        if ($field['is_fixed']) {
            $form->hideField('basic', 'name');
        }

        // Скроем для системных и фиксированных полей тип поля
        if ($field['is_system'] || $field['is_fixed_type']) {
            // Для валидации списка меняем на все доступные поля
            $form->setFieldProperty('type', 'type', 'generator', function () {
                return cmsForm::getAvailableFormFields(false, 'content');
            });
            $form->hideField('type', 'type');
        }

        // скроем лишние опции для системных полей
        if ($field['is_system']) {
            $form->removeFieldset('labels');
            $form->removeFieldset('values');
        }

        if ($this->request->has('submit')) {

            // добавляем поля настроек типа поля в общую форму
            // чтобы они были обработаны парсером и валидатором
            // вместе с остальными полями
            $field_type                 = $this->request->get('type', '');
            $field_class                = 'field' . string_to_camel('_', $field_type);
            $field_object               = new $field_class(null, null);
            $field_object->subject_name = '{users}';
            $field_options              = $field_object->getOptions();
            $form->addFieldsetAfter('type', LANG_CP_FIELD_TYPE_OPTS, 'field_settings');
            foreach ($field_options as $option_field) {
                $option_field->setName("options:{$option_field->name}");
                $form->addField('field_settings', $option_field);
            }

            $defaults = $field['is_fixed_type'] ? ['type' => $field['type']] : [];

            $field  = array_merge($defaults, $form->parse($this->request, true));
            $errors = $form->validate($this, $field);

            if (!$errors) {

                // если не выбрана группа, обнуляем поле группы
                if (!$field['fieldset']) {
                    $field['fieldset'] = null;
                }

                // если создается новая группа, то выбираем ее
                if ($field['new_fieldset']) {
                    $field['fieldset'] = $field['new_fieldset'];
                }
                unset($field['new_fieldset']);

                // сохраняем поле
                $content_model->updateContentField('{users}', $field_id, $field);

                cmsUser::addSessionMessage(LANG_CP_SAVE_SUCCESS, 'success');

                $this->redirectToAction('fields');
            }

            if ($errors) {
                cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
            }
        }

        return $this->cms_template->render('backend/field', [
            'do'     => 'edit',
            'field'  => $field,
            'form'   => $form,
            'errors' => isset($errors) ? $errors : false
        ]);
    }

}
