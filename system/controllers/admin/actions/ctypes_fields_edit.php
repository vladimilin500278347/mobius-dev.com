<?php

class actionAdminCtypesFieldsEdit extends cmsAction {

    public function run($ctype_id, $field_id) {

        if (!$ctype_id || !$field_id) {
            return cmsCore::error404();
        }

        $content_model = cmsCore::getModel('content');

        $ctype = $content_model->getContentType($ctype_id);
        if (!$ctype) {
            return cmsCore::error404();
        }

        $form = $this->getForm('ctypes_field', ['edit', $ctype['name']]);

        $form = cmsEventsManager::hook('ctype_field_form', $form);
        list($form, $ctype) = cmsEventsManager::hook($ctype['name'] . '_ctype_field_form', [$form, $ctype]);

        $field = $content_model->getContentField($ctype['name'], $field_id);
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
            $form->hideField('basic', 'hint');
            $form->hideField('visibility', 'options:relation_id');
            $form->setFieldProperty('visibility', 'options:is_in_item_pos', 'is_visible', false);
            $form->hideFieldset('group');
            $form->hideFieldset('format');
            $form->hideFieldset('values');
            $form->hideFieldset('labels');
            $form->hideFieldset('wrap');
            $form->hideFieldset('edit_access');
        }

        if ($this->request->has('submit')) {

            // добавляем поля настроек типа поля в общую форму
            // чтобы они были обработаны парсером и валидатором
            // вместе с остальными полями
            $field_type                 = $this->request->get('type', '');
            $field_class                = 'field' . string_to_camel('_', $field_type);
            $field_object               = new $field_class(null, null);
            $field_object->subject_name = $ctype['name'];
            $field_options              = $field_object->getOptions();
            $form->addFieldsetAfter('type', LANG_CP_FIELD_TYPE_OPTS, 'field_settings');
            foreach ($field_options as $option_field) {
                $option_field->setName("options:{$option_field->name}");
                $form->addField('field_settings', $option_field);
            }

            $defaults = $field['is_fixed_type'] ? ['type' => $field['type']] : [];

            $_field = array_merge($defaults, $form->parse($this->request, true));
            $errors = $form->validate($this, $_field);

            if (!$errors) {

                // если не выбрана группа, обнуляем поле группы
                if (!$_field['fieldset']) {
                    $_field['fieldset'] = null;
                }

                // если создается новая группа, то выбираем ее
                if ($_field['new_fieldset']) {
                    $_field['fieldset'] = $_field['new_fieldset'];
                }
                unset($_field['new_fieldset']);

                // сохраняем поле
                $content_model->updateContentField($ctype['name'], $field_id, $_field);

                cmsUser::addSessionMessage(LANG_SUCCESS_MSG, 'success');

                $this->redirectToAction('ctypes', ['fields', $ctype['id']]);
            }

            if ($errors) {

                cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');

                $field = array_merge($field, $_field);
            }
        }

        return $this->cms_template->render('ctypes_field', [
            'do'     => 'edit',
            'ctype'  => $ctype,
            'field'  => $field,
            'form'   => $form,
            'errors' => isset($errors) ? $errors : false
        ]);
    }

}
