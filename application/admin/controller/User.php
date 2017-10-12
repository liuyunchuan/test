<?php
namespace app\index\controller;

class User extends Base
{
    public function index()
    {
        return $this->fetch();
    }

    /*访客管理·显示所有访客信息*/
    public function visitor()
    {
        // 获取数据
        // 排除员工信息，type值为0时，所选择的数据类型为访客
        $map['type']   = 0;
        // status值为-1时，表示该条数据为已经删除，现在已经没有了这个状态，因为删除的时候是直接删除了，没有回收站的概念了
        $map['status'] = ['>' , -1];
        // session('id')为当前管理员的id，如果为0代表是超级管理员
        if( session('id') != 0 )
        {
            // 如果不为0，代表是普通管理员，需要查询该管理员下的访客信息
            $map['token'] = session('token');
        }
        // 根据限定条件查询出对应的访客信息，并且分页显示
        $visitorres = db( 'user' )->where( $map)->order( 'token asc, id asc' )->paginate( 12 );
        // 把数据传递给模板文件
        $this->assign( 'visitorres', $visitorres );
        // 渲染模板文件
    	return $this->fetch();
    }

    /*员工管理*/
    public function employee()
    {
    	
        // 获取数据
        // 排除访客信息，type值为1时，所选择的数据类型为员工
        $map['type'] = 1;
        // status值为-1时，表示该条数据为已经删除，现在已经没有了这个状态，因为删除的时候是直接删除了，没有回收站的概念了
        $map['status'] = ['>' , -1];
        // session('id')为当前管理员的id，如果为0代表是超级管理员
        if( session('id') != 0 )
        {
            // 如果不为0，代表是普通管理员，需要查询该管理员下的员工信息
            $map['token'] = session( 'token' );
        }
        // 根据限定条件查询出对应的员工信息，并且分页显示
        $employeeres = db('user')->where($map)->order('id asc')->paginate( 12 );
        // 把数据传递给模板文件
        $this->assign('employeeres',$employeeres);
        // 渲染模板文件
        return $this->fetch();
    }

    /*编辑访客信息*/
    public function visitorDetail()
    {
        header("Content-type:text/html;charset=utf-8");
        if(Request()->isPost())
        {
            $data = [
                'id'     => input('id'),
                'name'   => input('name'),
                'phone'  => input('phone'),
                'openid' => input('openid'),
                'idcard' => input('idcard'),
                'status' => input('status'),
                'type'   => input('ischange'),
            ];
            // 判断是否设定为员工
            $data['type'] = $data['type'] == 'on' ? 1 : 0;
            // 验证数据的合法性
            $validate = \think\Loader::validate('User');
            if($validate->check($data))
            {
                // 数据合法，进行数据更新操作
                $db = db('User')->update($data);
                // 判断数据信息是否成功
                if($db) {
                    // 数据修改成功
                    return $this->success('修改访客信息成功！','visitor');
                }else if($db!==false) {
                    // 数据没有修改，原因是所提交的数据与原来的数据一样
                    return $this->success('信息无修改！','visitor');
                } else {
                    // 数据修改失败
                    return $this->error('修改访客信息失败，请稍后再试！');
                }
            } else {
                // 数据验证失败
                return $this->error($validate->getError());
            }
            return;
        } else {
            // 这里缺少对ID的判断  
            // if( $id == 0){return ;die('非法参数');}    
            // 或许需要修改信息的id
            $id = input('id'); 
            // 根据id查找对应的访客信息
            $visitors = db('user')->where('id',input('id'))->find();
            $this->assign('visitors',$visitors);
            return $this->fetch();  
        }
        
    }

    /*删除访客，包含批量删除和单独删除的方法*/
    public function visitorDel()
    {
        // 这里缺少对传递的数据进行验证
        $id[] = input('post.id/a');
        if( !empty( $id[0] ) )
        {
            // 批量删除
            for($i=0 ; $i<count($id[0]) ; $i++)
            {
                $data   = ["id" => $id[0][$i]];
                $dels[] = db('user')->delete($data);
            }
            print_r($dels);
        } else {
            // 单独删除
            $data   = [ 'id'=>input('id')];
            $dels[] = db('user')->delete($data);
            if( $dels )
            {
                $this->success('删除访客信息成功','visitor');
            } else {
                $this->error( '删除访客信息失败,请稍后再试');
            }
        }
    }

    // 清空所有访客信息
    public function visitorDeleteAll()
    {
        $map['type']  = 0;
        if( session('id') != 0 )
        {
            $map['token'] = session('token');
        }
        $delid = db('user')->where($map)->column('id');
        $dels  = db('user')->delete($delid);
        print_r($dels);
    }

    // 清空所有员工信息
    public function employeeDeleteAll()
    {
        $map['type']  = 1;
        if( session('id') != 0 )
        {
            $map['token'] = session('token');
        }
        $delid = db('user')->where($map)->column('id');
        $dels  = db('user')->delete($delid);
        print_r($dels);
    }

    /*增加访客*/
    public function visitorAdd()
    {
        header("Content-type:text/html;charset=UTF-8");
        if(Request()->isPost())
        {
            $data = [
                'name'   => input('name'),
                'phone'  => input('phone'),
                'openid' => input('openid'),
                'idcard' => input('idcard'),
                'status' => input('status'),
                'token'  => session('token'),
            ];

            $validate = \think\Loader::validate('User');
            if($validate->check($data))
            {
                $db = db('User')->insert($data);
                if($db) {
                    return $this->success('添加访客信息成功！','visitor');
                } else {
                    return $this->error('添加访客信息失败，请稍后再试！');
                }
            } else {
                return $this->error($validate->getError());
            }
            return;
        }
        return $this->fetch();
    }

    /*导出所有访客信息*/
    public function visitorExportAll()
    {
        // 引入库文件
        \think\Loader::import('Excel.PHPExcel');
        \think\Loader::import('Excel.PHPExcel.IOFactory');
        
        // header("Content-Type:text/html;charset=UTF-8");
        // 拼接文件名
        $fileName = "visitorAll".date("Ymd-His",time()).".xls";

        // 查找数据
        // $epl = db('user')->select();
        // 排除员工信息
        $map['type'] = 0;
        // 排除删除状态的数据
        $map['status'] = ['>' , -1];
        $epl = db('user')->where($map)->order('id asc')->select();

        $objExcel = new \PHPExcel();  // 创建一个处理对象实例
        // 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);   // 用于其他版本格式

        //*************************************
        //设置文档基本属性
        $objProps = $objExcel->getProperties();
        $objProps->setCreator("Zeal Li");
        $objProps->setLastModifiedBy("Zeal Li");
        $objProps->setTitle("Office XLS Test Document");
        $objProps->setSubject("Office XLS Test Document, Demo");
        $objProps->setDescription("Test document, generated by PHPExcel.");
        $objProps->setKeywords("office excel PHPExcel");
        $objProps->setCategory("Test");

        //*************************************
        //设置当前的sheet索引，用于后续的内容操作。
        //一般只有在使用多个sheet的时候才需要显示调用。
        //缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle("访客信息");

        //设置单元格首格内容
        $objActSheet->setCellValue('A1','访客姓名');
        $objActSheet->setCellValue('B1','联系方式');
        $objActSheet->setCellValue('C1','身份证号(选填)');

        //  遍历访客信息数组，将数据存入对应的excel文件的每个单元格
        for($i = 0; $i < count($epl); $i++)
        {
            $objExcel->getActiveSheet()->setCellValueExplicit('A'.($i+2),$epl[$i]['name']);
            $objExcel->getActiveSheet()->setCellValueExplicit('B'.($i+2),$epl[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValueExplicit('C'.($i+2),$epl[$i]['idcard']);
        }

        //显式指定内容类型
        /*$objActSheet->setCellValueExplicit('A5', '847475847857487584',
            PHPExcel_Cell_DataType::TYPE_STRING);*/

        //合并单元格
        /*$objActSheet->mergeCells('B1:C22');

        //分离单元格
        $objActSheet->unmergeCells('B1:C22');*/

        //*************************************
        //设置单元格样式
        //

        //设置宽度
        $objActSheet->getColumnDimension('A')->setWidth(14.5);
        $objActSheet->getColumnDimension('B')->setWidth(14.5);
        $objActSheet->getColumnDimension('C')->setWidth(18.5);

        //设置单元格内容的数字格式。
        //
        //如果使用了 PHPExcel_Writer_Excel5 来生成内容的话，
        //这里需要注意，在 PHPExcel_Style_NumberFormat 类的 const 变量定义的
        //各种自定义格式化方式中，其它类型都可以正常使用，但当setFormatCode
        //为 FORMAT_NUMBER 的时候，实际出来的效果被没有把格式设置为"0"。需要
        //修改 PHPExcel_Writer_Excel5_Format 类源代码中的 getXf($style) 方法，
        //在 if ($this->_BIFF_version == 0x0500) { （第363行附近）前面增加一
        //行代码:
        //if($ifmt === '0') $ifmt = 1;
        //
        //设置格式为PHPExcel_Style_NumberFormat::FORMAT_NUMBER，避免某些大数字
        //被使用科学记数方式显示，配合下面的 setAutoSize 方法可以让每一行的内容
        //都按原始内容全部显示出来。
                /*$objStyleA5
                    ->getNumberFormat()
                    ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);*/

        //设置字体
        $objStyleA = $objActSheet->getStyle('A');
        $objStyleA = $objStyleA->getFont();
        $objStyleA->setName('宋体');
        $objStyleA->setSize(10);
        $objStyleA->setBold(true);
        $objStyleA->getColor()->setARGB('FF999999');

        //设置对齐方式
        /*$objAlignA5 = $objStyleA5->getAlignment();
        $objAlignA5->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objAlignA5->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置边框
        $objBorderA5 = $objStyleA5->getBorders();
        $objBorderA5->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objBorderA5->getTop()->getColor()->setARGB('FFFF0000'); // color
        $objBorderA5->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objBorderA5->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objBorderA5->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        //设置填充颜色
        $objFillA5 = $objStyleA5->getFill();
        $objFillA5->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objFillA5->getStartColor()->setARGB('FFEEEEEE');

        //从指定的单元格复制样式信息.
        $objActSheet->duplicateStyle($objStyleA5, 'B1:C22');*/


        //*************************************
        //添加图片
        /*$objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('ZealImg');
        $objDrawing->setDescription('Image inserted by Zeal');
        $objDrawing->setPath('./zeali.net.logo.gif');
        $objDrawing->setHeight(36);
        $objDrawing->setCoordinates('C23');
        $objDrawing->setOffsetX(10);
        $objDrawing->setRotation(15);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(36);
        $objDrawing->setWorksheet($objActSheet);*/


        //添加一个新的worksheet
        /*$objExcel->createSheet();
        $objExcel->getSheet(1)->setTitle('测试2');

        //保护单元格
        $objExcel->getSheet(1)->getProtection()->setSheet(true);
        $objExcel->getSheet(1)->protectCells('A1:C22', 'PHPExcel');*/


        //*************************************
        //输出内容
        //

        $outputFileName = $fileName;
        // $time = date("Ymd H-i-s",time());
        // $outputFileName = $time.".xls";
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:attachment;filename="' . $outputFileName . '"');//兼容其他浏览器到文件
        header('Content-Disposition:filename="' . $outputFileName . '"');  //兼容IE浏览器到文件
        header('Content-Disposition:inline;filename="'.$outputFileName.'"');  //到浏览器
        header("Content-Transfer-Encoding: binary");
        header("Expires: 0");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('./excel/'.$outputFileName);
        echo $outputFileName;
    }

    /*导出选中访客信息*/
    public function visitorExport()
    {
        // 引入库文件
        \think\Loader::import('Excel.PHPExcel');
        \think\Loader::import('Excel.PHPExcel.IOFactory');
        
        $id[] = input('post.id/a');
        // return print_r($id[0][0]);
        for($i=0 ; $i<count($id[0]) ; $i++)
        {
            $epl[] = db('user')->where('id',$id[0][$i])->find();
        }

        // 拼接文件名
        $fileName = "visitor".date("Ymd-His",time()).".xls";


        $objExcel = new \PHPExcel();  // 创建一个处理对象实例
        // 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);   // 用于其他版本格式

        //*************************************
        //设置文档基本属性
        $objProps = $objExcel->getProperties();
        $objProps->setCreator("Zeal Li");
        $objProps->setLastModifiedBy("Zeal Li");
        $objProps->setTitle("Office XLS Test Document");
        $objProps->setSubject("Office XLS Test Document, Demo");
        $objProps->setDescription("Test document, generated by PHPExcel.");
        $objProps->setKeywords("office excel PHPExcel");
        $objProps->setCategory("Test");

        //*************************************
        //设置当前的sheet索引，用于后续的内容操作。
        //一般只有在使用多个sheet的时候才需要显示调用。
        //缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle("访客信息");

        //设置单元格首格内容
        $objActSheet->setCellValue('A1','访客姓名');
        $objActSheet->setCellValue('B1','联系方式');
        $objActSheet->setCellValue('C1','身份证号(选填)');

        //  遍历访客信息数组，将数据存入对应的excel文件的每个单元格
        for($i = 0; $i < count($epl); $i++)
        {
            $objExcel->getActiveSheet()->setCellValueExplicit('A'.($i+2),$epl[$i]['name']);
            $objExcel->getActiveSheet()->setCellValueExplicit('B'.($i+2),$epl[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValueExplicit('C'.($i+2),$epl[$i]['idcard']);
        }

        //显式指定内容类型
        /*$objActSheet->setCellValueExplicit('A5', '847475847857487584',
            PHPExcel_Cell_DataType::TYPE_STRING);*/

        //合并单元格
        /*$objActSheet->mergeCells('B1:C22');

        //分离单元格
        $objActSheet->unmergeCells('B1:C22');*/

        //*************************************
        //设置单元格样式
        //

        //设置宽度
        $objActSheet->getColumnDimension('A')->setWidth(14.5);
        $objActSheet->getColumnDimension('B')->setWidth(14.5);
        $objActSheet->getColumnDimension('C')->setWidth(18.5);

        //设置单元格内容的数字格式。
        //
        //如果使用了 PHPExcel_Writer_Excel5 来生成内容的话，
        //这里需要注意，在 PHPExcel_Style_NumberFormat 类的 const 变量定义的
        //各种自定义格式化方式中，其它类型都可以正常使用，但当setFormatCode
        //为 FORMAT_NUMBER 的时候，实际出来的效果被没有把格式设置为"0"。需要
        //修改 PHPExcel_Writer_Excel5_Format 类源代码中的 getXf($style) 方法，
        //在 if ($this->_BIFF_version == 0x0500) { （第363行附近）前面增加一
        //行代码:
        //if($ifmt === '0') $ifmt = 1;
        //
        //设置格式为PHPExcel_Style_NumberFormat::FORMAT_NUMBER，避免某些大数字
        //被使用科学记数方式显示，配合下面的 setAutoSize 方法可以让每一行的内容
        //都按原始内容全部显示出来。
                /*$objStyleA5
                    ->getNumberFormat()
                    ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);*/

        //设置字体
        $objStyleA = $objActSheet->getStyle('A');
        $objStyleA = $objStyleA->getFont();
        $objStyleA->setName('宋体');
        $objStyleA->setSize(10);
        $objStyleA->setBold(true);
        $objStyleA->getColor()->setARGB('FF999999');

        //设置对齐方式
        /*$objAlignA5 = $objStyleA5->getAlignment();
        $objAlignA5->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objAlignA5->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置边框
        $objBorderA5 = $objStyleA5->getBorders();
        $objBorderA5->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objBorderA5->getTop()->getColor()->setARGB('FFFF0000'); // color
        $objBorderA5->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objBorderA5->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objBorderA5->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        //设置填充颜色
        $objFillA5 = $objStyleA5->getFill();
        $objFillA5->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objFillA5->getStartColor()->setARGB('FFEEEEEE');

        //从指定的单元格复制样式信息.
        $objActSheet->duplicateStyle($objStyleA5, 'B1:C22');*/


        //*************************************
        //添加图片
        /*$objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('ZealImg');
        $objDrawing->setDescription('Image inserted by Zeal');
        $objDrawing->setPath('./zeali.net.logo.gif');
        $objDrawing->setHeight(36);
        $objDrawing->setCoordinates('C23');
        $objDrawing->setOffsetX(10);
        $objDrawing->setRotation(15);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(36);
        $objDrawing->setWorksheet($objActSheet);*/


        //添加一个新的worksheet
        /*$objExcel->createSheet();
        $objExcel->getSheet(1)->setTitle('测试2');

        //保护单元格
        $objExcel->getSheet(1)->getProtection()->setSheet(true);
        $objExcel->getSheet(1)->protectCells('A1:C22', 'PHPExcel');*/


        //*************************************
        //输出内容
        //

        $outputFileName = $fileName;
        // $time = date("Ymd H-i-s",time());
        // $outputFileName = $time.".xls";
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:attachment;filename="' . $outputFileName . '"');//兼容其他浏览器到文件
        header('Content-Disposition:filename="' . $outputFileName . '"');  //兼容IE浏览器到文件
        header('Content-Disposition:inline;filename="'.$outputFileName.'"');  //到浏览器
        header("Content-Transfer-Encoding: binary");
        header("Expires: 0");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('./excel/'.$outputFileName);
        echo $outputFileName;
    }

    /*接收批量录入访客信息数据*/
    public function visitorImport()
    {
        header("Content-Type:text/html;charset=utf-8");
        // 获取表单上传文件
        $file = request()->file('excelData');

        // 移动到框架应用目录/public/uploads/目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
        
        // 数据为空返回错误
        if(empty($info)) {
            $this->error('导入数据失败，请稍后再试！');
        }
        // 获取文件名
        $fileName = $info->getSaveName();
        // 获取带参数文件名
        $excelPath = ROOT_PATH . 'public' . DS . 'upload' . DS .$fileName; 
        $extension = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));

        if($info) {
            // 信息上传成功，导入访客信息
            $this->visitorImports($excelPath, $extension);
        }
    }

    // 批量录入访客信息
    public function visitorImports($fileName ,$exts = 'xls')
    {
        // 导入PHPExcel
        \think\Loader::import('Excel.PHPExcel');

        $objExcel = new \PHPExcel();  // 创建一个处理对象实例
        // 根据后缀名导入对应的类库文件
        if($exts == 'xls'){
            \think\Loader::import('Excel.PHPExcel.Reader.Excel5');
            $PHPReader = new \PHPExcel_Reader_Excel5();
        } else if($exts == 'xlsx') {
            \think\Loader::import('Excel.PHPExcel.Reader.Excel2007');
            $PHPReader = new \PHPExcel_Reader_Excel2007();
        }
        // 载入文件
        $PHPExcel = $PHPReader ->load($fileName);
        // print_r($PHPExcel);
        // 获取表中的第一个工作表
        $currentSheet = $PHPExcel->getSheet(0);
        // 获取总列数
        $allColum = $currentSheet->getHighestColumn();
        // 获取总行数
        $allRow = $currentSheet->getHighestRow();
        // print_r($allColum);
        // echo "<br>";
        // print_r($allRow);
        // 循获取表中的数据，$currentRow表示当前行， 从哪行开始读取数据 ，索引值从0开始
        for($currentRow=2;$currentRow<=$allRow;$currentRow++)
        {
            // 判断是否到了数据最后一行，以行首元素是否为空为标准，如果为空，则跳出循环
            if((trim($currentSheet->getCell('A'.$currentRow)->getValue())) == '') break;
            // 从那列开始，A表示第一列
            for($currentColumn='A';$currentColumn<=$allColum;$currentColumn++)
            {
                // 数据坐标
                $address = $currentColumn.$currentRow;
                // 读取到的数据，保存到数组$data中
                $data[$currentRow][$currentColumn] = trim($currentSheet->getCell($address)->getValue());
            }
                // // 获得部门数组
                // $departmentArr[$currentRow] = trim($currentSheet->getCell('E'.$currentRow)->getValue());
                // // 获得职务数值
                // $dutiesArr[$currentRow] = trim($currentSheet->getCell('F'.$currentRow)->getValue());
        }
                // print_r($data);

        // 数组去重
        // $departmentArr = array_flip(array_flip($departmentArr));
        // $dutiesArr = array_flip(array_flip($dutiesArr));
        // 删除缓存文件
        // unlink($fileName);
        $departmentArr = array();
        $dutiesArr = array();
        $this->save_import($data,$departmentArr,$dutiesArr);
        // print_r($data);
    }

    /*保存访客导入数据*/
    public function save_import($data, $departmentArr=array(), $dutiesArr=array())
    {
        // 获取当前公司id
        $token = 'gh_6a18db1fd0a9';
        $address = '广州市天河区软件路15号2楼';
        $create_time = time();
        $status = 1;
        $company = '广州德生';
        $type = 0;

        /*处理没有部门的记录*/
            // $departmentData = array();
            // // 获得数据库当前部门数据集合
            // $hasDepartment = db('department')->where('uid',$uid)->column('id','title');
            // // 获得当前公司没有的部门数据集合
            // $departmentAdd = array_diff($departmentArr, $hasDepartment);
            // // 如果差集不为空，将差集首先插入部门表
            // if(!empty($departmentAdd)){
            //    foreach ($departmentAdd as $k => $v) {
            //         $departmentAddArr[] = array('uid'=>$uid,'name'=>$v,'title'=>$v,'pid'=>0,'create_time'=>NOW_TIME);
            //     }
            //     // 批量插入部门数据
            //     db('department')->insertAdd($departmentAddArr);
            // }
            // // 将部门名称和部门id键互换
            // $hasDepartmentId = db('department')->where('uid',$uid)->column('title','id');
        /*/处理没有部门的记录*/

        /*处理没有职位的记录*/
            // $dutiesData = array();
            // // 获得数据库当前职位数据集合
            // $hasDuties = db('dutites')->where('uid',$uid)->column('id','title');
            // // 获得当前公司没有的职位数据集合
            // $dutiesAdd = array_diff($dutiesArr, $hasDuties);
            // // 如果差集不为空，将差集首先插入职位表
            // if(!empty($dutiesAdd)) {
            //     foreach ($dutiesAdd as $k => $v) {
            //         $dutiesAddArr[] = array('uid'=>$uid,'name'=>$v,'title'=>$v,'pid'=>0,'create_time'=>NOW_TIME)
            //     }
            //     // 批量插入职位数据
            //     db('duties')->insertAdd($dutiesAddArr);
            // }
            // // 将职位名称和职位id键互换
            //  $hasDutiesId = db('duties')->where('uid',$uid)->column('title','id');
        /*/处理没有职位的记录*/

        /*将$data里面的部门和职位替换为对应的id*/
            $addData = array();
            $error = '';
            foreach ($data as $k => $v) {
                // 这里缺少对数据的验证
                // 
                $addData[] = array(
                    'name'   => $data[$k]['A'],
                    'phone'  => $data[$k]['B'],
                    'idcard' => $data[$k]['C'],
                    'token'  => $token,
                    'type'   => $type,
                    'address'=> $address,
                    'company'=> $company,
                    'create_time' => $create_time,
                    );
            }
        /*/将$data里面的部门和职位替换为对应的id*/
        
        // $arr = db('user')->where('token', $token)->column('idcard','phone');

        // 这里缺少对重复数据的验证操作
        // 插入数据
        // 
            
            $res = array_values($addData);
            // print_r($res);
            // print_r($addData);

            $result = db('user')->insertAll($res);
            if($result){
                $this->success('访客信息导入成功');
            } else {
                $this->error('员工信息格式不对，导入失败');
            }
    }

    /*增加员工*/
    public function employeeAdd()
    {
        header("Content-type:text/html;charset=UTF-8");
        if(Request()->isPost())
        {
            $token = 'gh_6a18db1fd0a9';
            $address = '广州市天河区软件路15号2楼';
            $create_time = time();
            $status = 2;
            $company = '广州德生';
            $type = 1;
            $data = [
                'name'   => input('name'),
                'phone'  => input('phone'),
                'idcard' => input('idcard'),
                'token'  => $token,
                'type'   => $type,
                'status' => $status,
                'address'  => $address,
                'company'  => $company,
                'token'     => session('token'),
                'duties_id' => input('duties'),
                'create_time'   => $create_time,
                'department_id' => input('department'),
            ];

            $validate = \think\Loader::validate('User');
            if($validate->check($data))
            {
                $db = db('User')->insert($data);
                if($db) {
                    return $this->success('添加员工信息成功！','employee');
                } else {
                    return $this->error('添加员工信息失败，请稍后再试！');
                }
            } else {
                return $this->error($validate->getError());
            }
            return;

        }
        return $this->fetch();
    }

    /*编辑员工信息*/
    public function employeeDetail()
    {
        header("Content-type:text/html;charset=utf-8");
        if(Request()->isPost())
        {
            $data = [
                'id'     => input('id'),
                'name'   => input('name'),
                'phone'  => input('phone'),
                'openid' => input('openid'),
                'idcard' => input('idcard'),
                'address'=> input('address'),
                'status' => input('status'),
                'type'   => input('ischange'),
            ];
            $data['type'] = $data['type'] == 'on' ? 0 : 1;

            $validate = \think\Loader::validate('User');
            if($validate->check($data))
            {
                $db = db('User')->update($data);
                if($db) {
                    return $this->success('修改员工信息成功！','employee');
                }else if($db!==false) {
                    return $this->success('信息无修改！','employee');
                } else {
                    return $this->error('修改员工信息失败，请稍后再试！');
                }
            } else {
                return $this->error($validate->getError());
            }
            return;
        }
        // 这里缺少对ID的判断  
        // if( $id == 0){return ;die('非法参数');}    
        $id = input('id'); 
        $employee = db('user')->where('id',input('id'))->find();
        $this->assign('employee',$employee);
        return $this->fetch();
    }

    /*删除员工*/
    public function employeeDel()
    {
        // 这里缺少对传递的数据进行验证
        $id[] = input('post.id/a');
        if(!empty($id[0])){
            for($i=0 ; $i<count($id[0]) ; $i++)
            {
                $data   = ["id" => $id[0][$i]];
                $dels[] = db('user')->delete($data);
            }
            print_r($dels);
        } else {
            $data   = [ 'id'=>input('id')];
            $dels[] = db('user')->delete($data);
            if( $dels )
            {
                $this->success('删除员工信息成功','employee');
            } else {
                $this->error( '删除员工信息失败,请稍后再试');
            }
        }
    }

    /*导出所有员工信息*/
    public function employeeExportAll()
    {
        // 引入库文件
        \think\Loader::import('Excel.PHPExcel');
        \think\Loader::import('Excel.PHPExcel.IOFactory');
        
        // 拼接文件名
        $fileName = "employeeAll".date("Ymd-His",time()).".xls";

        // 排除访客信息
        $map['type'] = 1;
        // 排除删除状态的数据
        $map['status'] = ['>' , -1];
        $epl = db('user')->where($map)->order('id asc')->select();

        $objExcel = new \PHPExcel();  // 创建一个处理对象实例
        // 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);   // 用于其他版本格式

        //*************************************
        //设置文档基本属性
        $objProps = $objExcel->getProperties();
        $objProps->setCreator("Zeal Li");
        $objProps->setLastModifiedBy("Zeal Li");
        $objProps->setTitle("Office XLS Test Document");
        $objProps->setSubject("Office XLS Test Document, Demo");
        $objProps->setDescription("Test document, generated by PHPExcel.");
        $objProps->setKeywords("office excel PHPExcel");
        $objProps->setCategory("Test");

        //*************************************
        //设置当前的sheet索引，用于后续的内容操作。
        //一般只有在使用多个sheet的时候才需要显示调用。
        //缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle("员工信息");

        //设置单元格首格内容
        $objActSheet->setCellValue('A1','员工姓名');
        $objActSheet->setCellValue('B1','联系方式');
        $objActSheet->setCellValue('C1','员工地址');
        $objActSheet->setCellValue('D1','部门(选填)');
        $objActSheet->setCellValue('E1','职务(选填)');
        $objActSheet->setCellValue('F1','身份证号(选填)');

        //  遍历访客信息数组，将数据存入对应的excel文件的每个单元格
        for($i = 0; $i < count($epl); $i++)
        {
            $objExcel->getActiveSheet()->setCellValueExplicit('A'.($i+2),$epl[$i]['name']);
            $objExcel->getActiveSheet()->setCellValueExplicit('B'.($i+2),$epl[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValueExplicit('C'.($i+2),$epl[$i]['address']);
            $objExcel->getActiveSheet()->setCellValueExplicit('D'.($i+2),$epl[$i]['department_id']);
            $objExcel->getActiveSheet()->setCellValueExplicit('E'.($i+2),$epl[$i]['duties_id']);
            $objExcel->getActiveSheet()->setCellValueExplicit('F'.($i+2),$epl[$i]['idcard']);
        }

        //设置单元格样式

        //设置宽度
        $objActSheet->getColumnDimension('A')->setWidth(14.5);
        $objActSheet->getColumnDimension('B')->setWidth(14.5);
        $objActSheet->getColumnDimension('C')->setWidth(38.5);
        $objActSheet->getColumnDimension('D')->setWidth(14.5);
        $objActSheet->getColumnDimension('E')->setWidth(14.5);
        $objActSheet->getColumnDimension('F')->setWidth(18.5);

        //设置字体
        $objStyleA = $objActSheet->getStyle('A');
        $objStyleA = $objStyleA->getFont();
        $objStyleA->setName('宋体');
        $objStyleA->setSize(10);
        $objStyleA->setBold(true);
        $objStyleA->getColor()->setARGB('FF999999');

        //输出内容

        $outputFileName = $fileName;
        // $time = date("Ymd H-i-s",time());
        // $outputFileName = $time.".xls";
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:attachment;filename="' . $outputFileName . '"');//兼容其他浏览器到文件
        header('Content-Disposition:filename="' . $outputFileName . '"');  //兼容IE浏览器到文件
        header('Content-Disposition:inline;filename="'.$outputFileName.'"');  //到浏览器
        header("Content-Transfer-Encoding: binary");
        header("Expires: 0");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('./excel/'.$outputFileName);
        echo $outputFileName;
    }

    /*导出选中信息*/
    public function employeeExport()
    {
        // 引入库文件
        \think\Loader::import('Excel.PHPExcel');
        \think\Loader::import('Excel.PHPExcel.IOFactory');
        
        // 拼接文件名
        $fileName = "employeeAll".date("Ymd-His",time()).".xls";

        // 获取要导出的数据
        $id[] = input('post.id/a');
        for($i=0 ; $i<count($id[0]) ; $i++)
        {
            $epl[] = db('user')->where('id',$id[0][$i])->find();
        }

        $objExcel = new \PHPExcel();  // 创建一个处理对象实例
        // 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);   // 用于其他版本格式

        //*************************************
        //设置文档基本属性
        $objProps = $objExcel->getProperties();
        $objProps->setCreator("Zeal Li");
        $objProps->setLastModifiedBy("Zeal Li");
        $objProps->setTitle("Office XLS Test Document");
        $objProps->setSubject("Office XLS Test Document, Demo");
        $objProps->setDescription("Test document, generated by PHPExcel.");
        $objProps->setKeywords("office excel PHPExcel");
        $objProps->setCategory("Test");

        //*************************************
        //设置当前的sheet索引，用于后续的内容操作。
        //一般只有在使用多个sheet的时候才需要显示调用。
        //缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle("员工信息");

        //设置单元格首格内容
        $objActSheet->setCellValue('A1','员工姓名');
        $objActSheet->setCellValue('B1','联系方式');
        $objActSheet->setCellValue('D1','员工地址');
        $objActSheet->setCellValue('D1','部门(选填)');
        $objActSheet->setCellValue('E1','职务(选填)');
        $objActSheet->setCellValue('F1','身份证号(选填)');

        //  遍历访客信息数组，将数据存入对应的excel文件的每个单元格
        for($i = 0; $i < count($epl); $i++)
        {
            $objExcel->getActiveSheet()->setCellValueExplicit('A'.($i+2),$epl[$i]['name']);
            $objExcel->getActiveSheet()->setCellValueExplicit('B'.($i+2),$epl[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValueExplicit('C'.($i+2),$epl[$i]['address']);
            $objExcel->getActiveSheet()->setCellValueExplicit('D'.($i+2),$epl[$i]['department_id']);
            $objExcel->getActiveSheet()->setCellValueExplicit('E'.($i+2),$epl[$i]['duties_id']);
            $objExcel->getActiveSheet()->setCellValueExplicit('F'.($i+2),$epl[$i]['idcard']);
        }

        //设置单元格样式

        //设置宽度
        $objActSheet->getColumnDimension('A')->setWidth(14.5);
        $objActSheet->getColumnDimension('B')->setWidth(14.5);
        $objActSheet->getColumnDimension('C')->setWidth(38.5);
        $objActSheet->getColumnDimension('D')->setWidth(14.5);
        $objActSheet->getColumnDimension('E')->setWidth(14.5);
        $objActSheet->getColumnDimension('F')->setWidth(18.5);

        //设置字体
        $objStyleA = $objActSheet->getStyle('A');
        $objStyleA = $objStyleA->getFont();
        $objStyleA->setName('宋体');
        $objStyleA->setSize(10);
        $objStyleA->setBold(true);
        $objStyleA->getColor()->setARGB('FF999999');

        //输出内容

        $outputFileName = $fileName;
        // $time = date("Ymd H-i-s",time());
        // $outputFileName = $time.".xls";
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:attachment;filename="' . $outputFileName . '"');//兼容其他浏览器到文件
        header('Content-Disposition:filename="' . $outputFileName . '"');  //兼容IE浏览器到文件
        header('Content-Disposition:inline;filename="'.$outputFileName.'"');  //到浏览器
        header("Content-Transfer-Encoding: binary");
        header("Expires: 0");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('./excel/'.$outputFileName);
        echo $outputFileName;
    }

    /*接收批量录入员工信息数据*/
    public function employeeImport()
    {
        header("Content-Type:text/html;charset=utf-8");
        // 获取表单上传文件
        $file = request()->file('excelData');

        // 移动到框架应用目录/public/uploads/目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
        
        // 数据为空返回错误
        if(empty($info)) {
            $this->error('导入数据失败，请稍后再试！');
        }
        // 获取文件名
        $fileName = $info->getSaveName();
        // 获取带参数文件名
        $excelPath = ROOT_PATH . 'public' . DS . 'upload' . DS .$fileName; 
        $extension = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));

        if($info) {
            // 信息上传成功，导入访客信息
            $this->employeeImports($excelPath, $extension);
        }
    }

    // 批量录入员工信息
    public function employeeImports($fileName ,$exts = 'xls')
    {
        // 导入PHPExcel
        \think\Loader::import('Excel.PHPExcel');

        $objExcel = new \PHPExcel();  // 创建一个处理对象实例
        // 根据后缀名导入对应的类库文件
        if($exts == 'xls'){
            \think\Loader::import('Excel.PHPExcel.Reader.Excel5');
            $PHPReader = new \PHPExcel_Reader_Excel5();
        } else if($exts == 'xlsx') {
            \think\Loader::import('Excel.PHPExcel.Reader.Excel2007');
            $PHPReader = new \PHPExcel_Reader_Excel2007();
        }
        // 载入文件
        $PHPExcel = $PHPReader ->load($fileName);
        // print_r($PHPExcel);
        // 获取表中的第一个工作表
        $currentSheet = $PHPExcel->getSheet(0);
        // 获取总列数
        $allColum = $currentSheet->getHighestColumn();
        // 获取总行数
        $allRow = $currentSheet->getHighestRow();
        // print_r($allColum);
        // echo "<br>";
        // print_r($allRow);
        // 循获取表中的数据，$currentRow表示当前行， 从哪行开始读取数据 ，索引值从0开始
        for($currentRow=2;$currentRow<=$allRow;$currentRow++)
        {
            // 判断是否到了数据最后一行，以行首元素是否为空为标准，如果为空，则跳出循环
            if((trim($currentSheet->getCell('A'.$currentRow)->getValue())) == '') break;
            // 从那列开始，A表示第一列
            for($currentColumn='A';$currentColumn<=$allColum;$currentColumn++)
            {
                // 数据坐标
                $address = $currentColumn.$currentRow;
                // 读取到的数据，保存到数组$data中
                $data[$currentRow][$currentColumn] = trim($currentSheet->getCell($address)->getValue());
            }
                // // 获得部门数组
                // $departmentArr[$currentRow] = trim($currentSheet->getCell('E'.$currentRow)->getValue());
                // // 获得职务数值
                // $dutiesArr[$currentRow] = trim($currentSheet->getCell('F'.$currentRow)->getValue());
        }
                // print_r($data);

        // 数组去重
        // $departmentArr = array_flip(array_flip($departmentArr));
        // $dutiesArr = array_flip(array_flip($dutiesArr));
        // 删除缓存文件
        // unlink($fileName);
        $departmentArr = array();
        $dutiesArr = array();
        $this->save_import_employee($data,$departmentArr,$dutiesArr);
        // print_r($data);
    }

    /*保存员工导入数据*/
    public function save_import_employee($data, $departmentArr=array(), $dutiesArr=array())
    {
        // 获取当前公司id
        $token = 'gh_6a18db1fd0a9';
        $address = '广州市天河区软件路15号2楼';
        $create_time = time();
        $status = 1;
        $company = '广州德生';
        $type = 1;

        /*处理没有部门的记录*/
            // $departmentData = array();
            // // 获得数据库当前部门数据集合
            // $hasDepartment = db('department')->where('uid',$uid)->column('id','title');
            // // 获得当前公司没有的部门数据集合
            // $departmentAdd = array_diff($departmentArr, $hasDepartment);
            // // 如果差集不为空，将差集首先插入部门表
            // if(!empty($departmentAdd)){
            //    foreach ($departmentAdd as $k => $v) {
            //         $departmentAddArr[] = array('uid'=>$uid,'name'=>$v,'title'=>$v,'pid'=>0,'create_time'=>NOW_TIME);
            //     }
            //     // 批量插入部门数据
            //     db('department')->insertAdd($departmentAddArr);
            // }
            // // 将部门名称和部门id键互换
            // $hasDepartmentId = db('department')->where('uid',$uid)->column('title','id');
        /*/处理没有部门的记录*/

        /*处理没有职位的记录*/
            // $dutiesData = array();
            // // 获得数据库当前职位数据集合
            // $hasDuties = db('dutites')->where('uid',$uid)->column('id','title');
            // // 获得当前公司没有的职位数据集合
            // $dutiesAdd = array_diff($dutiesArr, $hasDuties);
            // // 如果差集不为空，将差集首先插入职位表
            // if(!empty($dutiesAdd)) {
            //     foreach ($dutiesAdd as $k => $v) {
            //         $dutiesAddArr[] = array('uid'=>$uid,'name'=>$v,'title'=>$v,'pid'=>0,'create_time'=>NOW_TIME)
            //     }
            //     // 批量插入职位数据
            //     db('duties')->insertAdd($dutiesAddArr);
            // }
            // // 将职位名称和职位id键互换
            //  $hasDutiesId = db('duties')->where('uid',$uid)->column('title','id');
        /*/处理没有职位的记录*/

        /*将$data里面的部门和职位替换为对应的id*/
            $addData = array();
            $error = '';
            foreach ($data as $k => $v) {
                // 这里缺少对数据的验证
                // 
                $addData[] = array(
                    'name'   => $data[$k]['A'],
                    'phone'  => $data[$k]['B'],
                    'address' => $data[$k]['C'],
                    'department_id' => $data[$k]['D'],
                    'duties_id' => $data[$k]['E'],
                    'idcard' => $data[$k]['F'],
                    'token'  => $token,
                    'type'   => $type,
                    'address'=> $address,
                    'company'=> $company,
                    'create_time' => $create_time,
                    );
            }
        /*/将$data里面的部门和职位替换为对应的id*/
        
        // $arr = db('user')->where('token', $token)->column('idcard','phone');

        // 这里缺少对重复数据的验证操作
        // 插入数据
        // 
            
            $res = array_values($addData);
            // print_r($res);
            // print_r($addData);

            $result = db('user')->insertAll($res);
            if($result){
                $this->success('访客信息导入成功');
            } else {
                $this->error('员工信息格式不对，导入失败');
            }
    }
}