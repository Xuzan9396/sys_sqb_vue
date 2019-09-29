<?php

namespace Sqb\Modules\Sys\Controllers;

use Common\Models\Member;

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $this->view->disable();
        echo '<pre>';
            var_dump('sys');
        echo '</pre>';
        exit;
        
        $res = Member::getInstance()->test();
        echo '<pre>';
            var_dump($res);
        echo '</pre>';
        exit;
    }

    public function index2Action()
    {
        $this->view->disable();
        echo '<pre>';
            var_dump('sys2');
        echo '</pre>';
        exit;

        $res = Member::getInstance()->test();
        echo '<pre>';
        var_dump($res);
        echo '</pre>';
        exit;
    }
}

