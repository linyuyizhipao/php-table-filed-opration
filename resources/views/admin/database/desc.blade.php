<div class="bs-example" data-example-id="condensed-table">



    <form class="form-horizontal" method="post" action="{{$url}}">


        <div class="form-group">

            <div class="col-lg-6">
                <div class="input-group">
                    <input id="search-table-name" type="text" class="form-control" placeholder="Search for...">
                    <span class="input-group-btn">
                         <button id="search" class="btn btn-default" type="button">搜索表</button>
                    </span>
                </div><!-- /input-group -->
            </div><!-- /.col-lg-6 -->

            <div class="col-sm-offset-2 col-sm-10">
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <th>表字段:</th>
                        <th>修改该字段的翻译类型</th>
                        <th>企业主键id(默认company_id)</th>
                        <th>where_condition</th>
                    </tr>
                    </thead>
                    <tbody>

                    @forelse ($descList as $info)
                        <tr>
                            <th scope="row">{{$info['Field']}}</th>
                            <td>
                                <select name="fileds[{{$info['Field']}}]" class="form-control">
                                    @foreach ($info['engineList'] as $k=>$engine)
                                        <option {{$engine[1]}} value="{{$engine[0]}}">{{$engine[2]}}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input class="form-control" name="id_name[{{$info['Field']}}]" type="text" value="{{$info['id_name'] ?? 'company_id'}}">
                            </td>

                            <td>
                                <input class="form-control" name="where_condition[{{$info['Field']}}]" type="text" value="{{$info['where_condition']??''}}">
                            </td>
                        </tr>

                    @empty
                        <p>没有字段</p>
                    @endforelse


                    </tbody>
                </table>
            </div>
        </div>



        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="databaseName" value="{{ $databaseName }}">
        <input type="hidden" name="tableName" value="{{ $tableName }}">
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-warning">提交</button>
            </div>
        </div>
    </form>

</div>
<script>
    $("#search").click(function(){
        val = $("#search-table-name").val();
        location.href = '/admin/database/searchTable?tableName='+val;
    })
</script>