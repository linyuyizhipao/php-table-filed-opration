<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseController extends Controller
{
    const DATABASE_NAME = 'multilingual_english';
    const TABLE_NAME = 'english_translate_relavance';
    const INVLID_FIELDA_STATUS= 0; //不参与翻译的字段状态
    const TABLE_NAME_STATUS = false;//true 覆盖，false  不覆盖。如果多语库中存在 english_translate_relavance 中出现过的表，那么干掉它后  程序再维护一张我们需要的同名表

    public function index(Content $content,Request $request)
    {
        $databaseName =  $request->get('databaseName',-1);


        switch ($databaseName){

            case -1:
                //展示库列表
                $url = url("/admin/database/index");
                $databaseList = DB::select("show databases");
                return $content
                    ->header('表列表')
                    ->description('数据库里面的表展示,已经进入到具体库了')
                    ->row(function (Row $row) USE($databaseList,$url){

                        $row->column(4, function (Column $column) USE($databaseList,$url) {
                            $column->append(view('admin.database.index',['databaseList'=>$databaseList,"url"=>$url]));
                        });

                    });
                break;
            default :
                $tableName =  $request->get('tableName',-1);

                if($tableName == -1){
                    //库里面的表列表
                    $url = url("/admin/database/index?databaseName={$databaseName}");
                    $tablesList = DB::cusConnection($databaseName)->select("show tables");
                    return $content
                        ->header('表字段展示')
                        ->description('展示某库里面的具体某个表的字段列表展示')
                        ->row(function (Row $row) USE($tablesList,$databaseName,$url){

                            $row->column(4, function (Column $column) USE($tablesList,$databaseName,$url) {
                                $fmtDatabaseName = 'Tables_in_'.$databaseName;
                                $column->append(view('admin.database.table',['tablesList'=>$tablesList,'fmtDatabaseName'=>$fmtDatabaseName,"url"=>$url]));
                            });

                        });

                }else{
                    //字段列表
                    $url = url("/admin/database/editFieldEngineType");
                    $engineList = [[0,'selected = "selected"','不参与',],[1,'selected = "selected"','机翻翻译'],[2,'selected = "selected"','人工翻译'],[3,'selected = "selected"','拼音翻译']];

                    $descList = DB::cusConnection($databaseName)->select("desc {$tableName}");
                    $arr = [];

                    foreach ($descList as $k=>$v){
                        $arr[] = (array)$v;
                    }
                    $descList = $arr;

                    foreach ($descList as $k=>$v){
                        $res = DB::cusConnection(self::DATABASE_NAME)->table(self::TABLE_NAME)->where(['db_name'=>$databaseName,'table_name'=>$tableName,'field_name'=>$v['Field']])->get()->toArray();

                        $list = $engineList;
                        if(!empty($res)){
                            $res =(array)$res[0];

                            $descList[$k]['id_name'] = $res['id_name'];
                            $descList[$k]['where_condition'] = $res['where_condition'];

                            foreach ($list as $kk=>$vv){
                                if($res['engine_type'] != $vv[0]){
                                    $list[$kk][1] = '';
                                }
                            }

                        }else{
                            foreach ($list as $kk=>$vv){
                                $list[$kk][1] = '';
                            }
                        }


                        $descList[$k]['engineList'] = $list;

                    }

                    return $content
                        ->header('数据库列表')
                        ->description('数据库的列表展示，用于切入到库中展示库里面的表')
                        ->row(function (Row $row) USE($descList,$databaseName,$tableName,$url){

                            $row->column(10, function (Column $column) USE($descList,$databaseName,$tableName,$url) {
                                $column->append(view('admin.database.desc',[
                                    'descList'=>$descList,
                                    "url"=>$url,
                                    'databaseName'=>$databaseName,
                                    'tableName'=>$tableName,
                                ]));
                            });

                        });
                }

        }
    }

    //修改字段翻译类型
    public function editFieldEngineType(Request $request)
    {
        $databaseName = self::DATABASE_NAME;
        $tableName = self::TABLE_NAME;
        $fileds = $request->post('fileds');
        $idNames = $request->post('id_name', 'company_id');
        $whereConditions = $request->post('where_condition', '0');
        $postDatabaseName = $request->post('databaseName');
        $postTableName = $request->post('tableName');

        if (!empty($fileds) && is_array($fileds)) {
            if (count($fileds) > 100) {
                //遍历操作数据库最大安全值
                Log::error("遍历操作数据库超过最大安全值");
                return false;
            }

            foreach ($fileds as $fieldName => $val) {
                if($val == self::INVLID_FIELDA_STATUS){
                    continue;
                }

                $res = DB::cusConnection($databaseName)->table($tableName)->where(['db_name' => $postDatabaseName, 'table_name' => $postTableName, 'field_name' => $fieldName])->get()->toArray();

                $idName = $idNames[$fieldName] ?? '0';
                $whereCondition = $whereConditions[$fieldName] ?? '0';

                if (!empty($res)) {
                    //存在就修改
                    DB::cusConnection($databaseName)
                        ->update("update {$tableName} set engine_type = ?,id_name=? , where_condition=? where db_name=? && table_name=? && field_name=?",
                            [$val, $idName, $whereCondition, $postDatabaseName, $postTableName, $fieldName]);

                } else {
                    $task_screen_time = 0;
                    //不存在就能新增
                    DB::cusConnection($databaseName)
                        ->insert("insert into {$tableName} (db_name,table_name,field_name,engine_type,id_name,where_condition,task_screen_time) values(?,?,?,?,?,?,?)",
                            [$postDatabaseName, $postTableName, $fieldName, $val, $idName, $whereCondition, $task_screen_time]);

                }

            }
        }
        return redirect(url()->previous());

    }
    //通过表名去各数据库找出对应的表
   public function searchTable(Request $request)
   {
        $tablename = $request->get("tableName");
        $databaseList = DB::select("show databases");
        foreach ($databaseList as $k=>$v){
            $databaseName = $v->Database;
            if(strstr($databaseName,"_ods_") !== false){
                continue;
            }

            if($databaseName == 'xhxy'){
                continue;
            }
            try{
                $res = DB::cusConnection($databaseName)->table($tablename)->first();
                $url = url('/admin/database/index?databaseName='.$databaseName.'&tableName='.$tablename);
                break;
            }catch (\Exception $e){
                $url= null;
            }
        }
        if(isset($url)){
            return redirect($url);
        }else{
            return redirect(url()->previous());
        }
   }

    //更新数据库表主键名称
    //java场景中，需要根据 english_translate_relavance 表找到对应的需要翻译的库中的表中的字段，以为者程序知道了
    //哪个库中的哪个表的哪个字段需要进行机翻，还是人翻译，但是程序（java的定时任务）任然需要那个表的主键id的名称去调取api
    //这个脚本就是将 english_translate_relavance 中记录的记录中加一个 主键id名称信息同步的动作
    public function updatePrimaryName()
    {
        $info = DB::cusConnection(self::DATABASE_NAME)->table(self::TABLE_NAME)->get()->toArray();
        if(!empty($info) && is_array($info)){
            foreach ($info as $k=>$v){
                $databaseName = $v->db_name;
                $tableName = $v->table_name;
                $sql = "SELECT column_name FROM INFORMATION_SCHEMA.`KEY_COLUMN_USAGE` WHERE constraint_schema ='{$databaseName}' and table_name='{$tableName}' AND constraint_name='PRIMARY'";
                $primaryNameObj = DB::cusConnection($databaseName)->select($sql);
                if(isset($primaryNameObj[0]->column_name)){
                    $primaryName = $primaryNameObj[0]->column_name;
                    $updateSql = "update ". self::TABLE_NAME." set primary_name = ? where id=?";

                    DB::cusConnection(self::DATABASE_NAME)->update($updateSql,[$primaryName,$v->id]);
                }

            }
        }
    }

    //根据 english_translate_relavance 中记录的记录，去把对应的库对应的表的的结构在多语库中创建一个，并且新增的表只要待翻译的字段
    public function updateTableStruct()
    {



        $tableInfo = DB::cusConnection(self::DATABASE_NAME)->select("show tables");
        $tablesNameArr=[];
        foreach ($tableInfo as $k=>$v){
            $tablesNameArr[] = $v->Tables_in_multilingual_english;
        }


        $dataArrStruct = [];//库=》表=》字段结构
        $info = DB::cusConnection(self::DATABASE_NAME)->table(self::TABLE_NAME)->get()->toArray();
        if(!empty($info) && is_array($info)){
            foreach ($info as $k=>$v){
                $databaseName = $v->db_name;
                $tableName = $v->table_name;
                $fieldName = $v->field_name;
                $dataArrStruct[$databaseName][$tableName][] = $fieldName;
            }
        }

        if(self::TABLE_NAME_STATUS){
            foreach ($dataArrStruct as $dbName=>$tables){
                if(!is_array($tables))
                    continue;

                foreach ($tables as $tableName =>$fields){
                    if (empty($fields) || !is_array($fields))
                        continue;

                    if(in_array($tableName,$tablesNameArr)){

                        DB::cusConnection(self::DATABASE_NAME)->statement("drop table {$tableName}");

                    }


                }
            }
        }




        $tableInfo = DB::cusConnection(self::DATABASE_NAME)->select("show tables");
        $tablesNameArr=[];
        foreach ($tableInfo as $k=>$v){
            $tablesNameArr[] = $v->Tables_in_multilingual_english;
        }
        foreach ($dataArrStruct as $dbName=>$tables){
            if(!is_array($tables))
                continue;

            foreach ($tables as $tableName =>$fields){
                if (empty($fields) || !is_array($fields))
                    continue;

                if(!in_array($tableName,$tablesNameArr)){

                    $showCreateSql = "SHOW CREATE TABLE {$tableName}";
                    $res = DB::cusConnection($dbName)->select($showCreateSql);

                    $createSql = $res[0]->{'Create Table'};

                    DB::cusConnection(self::DATABASE_NAME)->statement($createSql);
                    $tablesNameArr[] = $tableName;
                }

                $descFields = DB::cusConnection(self::DATABASE_NAME)->select("desc {$tableName}");

                foreach ($descFields as $fk=>$fv){
                    if(isset($fv->Key) && $fv->Key == 'PRI'){
                        continue;
                    }
                    if(isset($fv->Field) && in_array($fv->Field,$fields)){
                        continue;
                    }

                    if(isset($fv->Field)){
                        $dropFieldSql = "ALTER TABLE {$tableName} DROP COLUMN {$fv->Field} ";

                        DB::cusConnection(self::DATABASE_NAME)->statement($dropFieldSql);
                    }

                }

            }
        }


    }



    public function searchTable2(Request $request)
    {
        $tt = ["不存在_updated_date_的表名"=>[],"存在_updated_date_的表名"=>[]];
        $databaseList = DB::select("show databases");
        foreach ($databaseList as $k=>$v){
            $databaseName = $v->Database;

            if(strstr($databaseName,"_yw_") === false){
                continue;
            }
            $tablesList = DB::cusConnection($databaseName)->select("show tables");

            foreach ($tablesList as $kk=>$vv){
                $h = "Tables_in_".$databaseName;
                $tabName = $vv->$h;
                $fieldList = DB::cusConnection($databaseName)->select("desc {$tabName}");
                $fgfg = true;
                foreach ($fieldList as $kkk=>$vvv){
                    if($vvv->Field == "updated_date" ){
                    }else{
                        $fgfg = false;
                    }
                }
                if($fgfg === false){
                    $tt["不存在_updated_date_的表名"][] = $databaseName."=>".$tabName."=>";
                }else{
                    $tt["存在_updated_date_的表名"][] = $databaseName."=>".$tabName."=>updated_date";
                }


            }
        }
        var_dump($tt);
    }
}
