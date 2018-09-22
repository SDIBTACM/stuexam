<?php

namespace Teacher\Controller;

/**
 * Class IndexController
 *
 * @package \Teacher\Controller
 */
class IndexController extends TemplateController {

    public function index() {
        $this->redirect("/Teacher/Quiz/showList");
    }
}
