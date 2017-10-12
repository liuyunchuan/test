<?php
namespace app\index\controller;

class Menu extends Base
{
	/*自定义菜单管理*/
	public function menu()
	{
		return $this->fetch();		
	}

}
