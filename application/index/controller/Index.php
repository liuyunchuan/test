<?php
namespace app\index\controller;

class Index extends Base
{
	// public function _initialize()
	// {
	// }
    public function index()
    {
        return $this->fetch();
    }
}
