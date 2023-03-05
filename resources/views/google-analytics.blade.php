<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Analytic</title>
</head>

<body>

    
    <div class="card">
        <div class="card-header">
            <h1>จำนวนผู้เข้าชมเว็บไซต์</h1>
            <div>{{ $previousWeekText }}</div>
            <div>{{ $lastWeekText }}</div>
        </div>

        <div class="card-body">
            <div>{{ $totalVisitor }}</div>
            <div>{{ $totalPageViews }}</div>
        </div>
    </div> 
    {{-- <div class="card">
        <div class="card-header">
            <h1>User</h1>
            <div>{{ $previousWeekText }}</div>
            <div>{{ $lastWeekText }}</div>
        </div>

        <div class="card-body">
            <div>{{ $userCount['count'] }}</div>
            <div>{{ $previousUserCount['count'] }}</div>
        </div>
    </div> --}}

    @isset($totalVisitorsAndPageViews)
    <div class="card">
        <div class="card-header">
            <h1>สถิติการเข้าชมเว็บไซต์</h1>
            <div>{{ $lastWeekText }}</div>
        </div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>ผู้เข้าชม/ครั้ง</th>
                        <th>จำนวนการเข้าชม</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($totalVisitorsAndPageViews as $item)
                        <tr>
                            <td>{{ $item['date'] }}</td>
                            <td>{{ $item['visitors'] }}</td>
                            <td>{{ $item['pageViews'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endisset
</body>

</html>
