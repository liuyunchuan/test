<?php
namespace app\index\controller;

class Duties extends Base
{
	/*职务管理*/
	public function duties()
	{
		print_r( config( 'haha.muban') );
		echo "<br />";
		print_r( config( 'haha.muban', 'xiugailema') );
		print_r( config( 'haha.muban') );

		// return $this->fetch();		
	}

	/*部门管理*/
	public function department()
	{
		return $this->fetch();
	}

}
