# Laravel-admin  开源项目
* 这个项目 基于laravel   mvc的后台现存，包括权限分配那一套

http://laravel-admin.org/  文档拓展

```php
C 与 V 配合  分析下
app/Admin/Controllers/DatabaseController.php  => index 方法里面   参看其中规律  便知c与v
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
```