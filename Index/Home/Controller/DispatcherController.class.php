<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 9/29/16 21:47
 */

namespace Home\Controller;


class DispatcherController extends TemplateController
{
    public function index() {
        if ($this->isTeacher()) {
            redirect(U('/Teacher'));
        } else {
            redirect(U('/Home'));
        }
    }
}