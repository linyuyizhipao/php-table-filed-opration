<?php
/**
 * Created by PhpStorm.
 * User: hushuang
 * Date: 2019/3/4
 * Time: 下午4:38
 */

namespace App\Http\Controllers;


use Illuminate\Support\Facades\DB;

class TestController
{
    public function showDatabses()
    {
        $databaseList = DB::select("show databases");
    }
    public function showTables()
    {
        $databaseName = "";
        $databaseList = DB::cusConnection($databaseName)->select("show tables");

    }

    public function descFields()
    {
        $table = "";
        $databaseName = "";
        $fieldList = DB::cusConnection($databaseName)->select("desc {$table}");

    }

}