<div class="bs-example" data-example-id="condensed-table">
    <table class="table table-condensed">
        <thead>
        <tr>
            <th>数据库名称</th>
            <th>点击进入该数据库</th>
        </tr>
        </thead>
        <tbody>

        @forelse ($databaseList as $info)

            <tr>
                <th scope="row">{{$info->Database}}</th>
                <td><a href="{{$url}}?databaseName={{$info->Database}}">进入</a></td>
            </tr>

        @empty
            <p>没有用户</p>
        @endforelse

        </tbody>
    </table>
</div>