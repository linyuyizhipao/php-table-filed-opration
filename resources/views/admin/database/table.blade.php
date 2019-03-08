<div class="bs-example" data-example-id="condensed-table">
    <table class="table table-condensed">
        <thead>
        <tr>
            <th>数据库名称:{{$fmtDatabaseName}}</th>
            <th>点击进入该数据库</th>
        </tr>
        </thead>
        <tbody>

        @forelse ($tablesList as $info)

            <tr>
                <th scope="row">{{$info->$fmtDatabaseName}}</th>
                <td><a href="{{$url}}&tableName={{$info->$fmtDatabaseName}}">进入</a></td>
            </tr>

        @empty
            <p>没有表</p>
        @endforelse

        </tbody>
    </table>
</div>