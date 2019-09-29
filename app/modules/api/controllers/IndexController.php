<?php

namespace Sqb\Modules\Api\Controllers;

use Common\Models\Member;

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $this->view->disable();
        
        $res = Member::getInstance()->test();
        echo '<pre>';
            var_dump($res);
        echo '</pre>';
        exit;
    }

    public function index2Action()
    {
        $this->view->disable();

        $res = Member::getInstance()->test();
        echo '<pre>';
        var_dump($res);
        echo '</pre>';
        exit;
    }
}

