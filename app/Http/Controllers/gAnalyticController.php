<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Analytics\AnalyticsFacade as Analytics;
use Spatie\Analytics\Period;

class gAnalyticController extends Controller
{
    public function index()
    {
        # docs : https://github.com/spatie/laravel-analytics


        // last week
        $lastWeek = Period::create(Carbon::now(), Carbon::now()->addWeek()->subDay());

        // Previous week
        $previousWeek = Period::create(Carbon::now()->subWeek(), Carbon::now()->subDay());


        // 5 minutes ago
        $period5MinutesAgo = Period::create(Carbon::now()->subMinutes(5), Carbon::now());

        // last month
        // $period = Period::months(1);

        // $period = Period::days(7);

        // $startDate = Carbon::now()->subYear();
        // $endDate = Carbon::now();
        // Period::create($startDate, $endDate);


        // $topBrowsers = $this->fetchTopBrowsers($period);
        // dd($topBrowsers);


        // $userTypes = $this->fetchUserTypes($period);
        // dd($userTypes);

        // $topReferrers = $this->fetchTopReferrers($period);
        // dd($topReferrers);

        // $mostVisitedPages = $this->fetchMostVisitedPages($period);
        // dd($mostVisitedPages);

        $visitorsAndPageViews = $this->fetchVisitorsAndPageViews($period5MinutesAgo);
        $pageViews = collect($visitorsAndPageViews)->pluck('pageViews');
        $visitor = collect($visitorsAndPageViews)->pluck('visitors');
        $totalPageViews = $pageViews->sum();
        $totalVisitor = $visitor->sum();


        // dd($period5MinutesAgo, $totalVisitor);


        // dd($visitorsAndPageViews[0]['pageViews']);

        // $totalVisitorsAndPageViews = $this->fetchTotalVisitorsAndPageViews($lastWeek);
        // dd($totalVisitorsAndPageViews, $visitorsAndPageViews, $pageViews);

        // getSession
        // $sessionCount = $this->getSessionCount($period5MinutesAgo);
        // dd($sessionCount);

        $sessionCountRealTimeAndDevice = $this->fetchSessionCountRealTimeAndDevice();
        // dd($sessionCountRealTimeAndDevice);
        $sessionCountRealTimeAndDeviceList = [];
        $sessionCountRealTime = 0;
        foreach ($sessionCountRealTimeAndDevice as $key => $value) {
            $sessionCountRealTime += $value['sessions'];
            $sessionCountRealTimeAndDeviceList[] = [
                'deviceCategory' => $value['deviceCategory'],
                'sessions' => $value['sessions'],
            ];
        }

        // getUser
        $userCount = $this->getUserCount($lastWeek);
        $previousUserCount = $this->getUserCount($previousWeek);


        // userByTimeOfDay
        // $userByTimeOfDay = $this->fetchUserByTimeOfDay($period);
        // dd($userByTimeOfDay);

        $dateFormat = 'd/m/y';
        // $periodText = $period->startDate->format($dateFormat) . ' - ' . $period->endDate->format($dateFormat);
        $previousWeekText = $previousWeek->startDate->format($dateFormat) . ' - ' . $previousWeek->endDate->format($dateFormat);
        $lastWeekText = $lastWeek->startDate->format($dateFormat) . ' - ' . $lastWeek->endDate->format($dateFormat);
        return view('google-analytics', compact(
            'previousWeekText',
            'lastWeekText',

            // 'period',
            // 'periodText',
            // 'totalVisitorsAndPageViews',

            'userCount',
            'previousUserCount',

            'totalPageViews',
            'totalVisitor',

            'sessionCountRealTime',
            'sessionCountRealTimeAndDeviceList',
        ));
    }

    public function getUserCount(Period $period)
    {
        $userTypes = $this->fetchUserTypes($period);
        $userCount = 0;
        $newUserCount = 0;
        $returningUserCount = 0;

        foreach ($userTypes as $userType) {
            $userCount += $userType['sessions'];
            if ($userType['userType'] == 'New Visitor') {
                $newUserCount = $userType['sessions'];
            } else {
                $returningUserCount = $userType['sessions'];
            }
        }

        return [
            'count' => $userCount,
            'newUserCount' => $newUserCount,
            'returningUserCount' => $returningUserCount
        ];
    }

    public function fetchVisitorsAndPageViews(Period $period)
    {
        $analyticsData = Analytics::fetchVisitorsAndPageViews($period);
        return $analyticsData;
    }

    public function fetchTotalVisitorsAndPageViews(Period $period)
    {
        $response = $this->performQuery(
            $period,
            'ga:sessions,ga:pageviews',
            [
                'dimensions' => 'ga:date',
            ]
        );

        return collect($response['rows'] ?? [])->map(function (array $pageRow) {
            return [
                'date' => Carbon::createFromFormat('Ymd', $pageRow[0])->toDateString(),
                'visitors' => (int) $pageRow[1],
                'pageViews' => (int) $pageRow[2],
            ];
        });
    }

    public function fetchMostVisitedPages(Period $period, int $maxResults = 20)
    {
        $response = $this->performQuery(
            $period,
            'ga:pageviews',
            [
                'dimensions' => 'ga:pageTitle,ga:pagePath',
                'sort' => '-ga:pageviews',
                'max-results' => $maxResults,
            ]
        );

        return collect($response['rows'] ?? [])->map(function (array $pageRow) {
            return [
                'pageTitle' => $pageRow[0],
                'pagePath' => $pageRow[1],
                'pageViews' => (int) $pageRow[2],
            ];
        });
    }


    public function fetchTopReferrers(Period $period, int $maxResults = 20)
    {
        $response = $this->performQuery(
            $period,
            'ga:sessions',
            [
                'dimensions' => 'ga:fullReferrer',
                'sort' => '-ga:sessions',
                'max-results' => $maxResults,
            ]
        );

        return collect($response['rows'] ?? [])->map(function (array $pageRow) {
            return [
                'fullReferrer' => $pageRow[0],
                'sessions' => (int) $pageRow[1],
            ];
        });
    }

    public function fetchUserTypes(Period $period)
    {
        $response = $this->performQuery(
            $period,
            'ga:sessions',
            [
                'dimensions' => 'ga:userType',
            ]
        );

        return collect($response['rows'] ?? [])->map(function (array $userTypeRow) {
            return [
                'userType' => $userTypeRow[0],
                'sessions' => (int) $userTypeRow[1],
            ];
        });
    }

    public function fetchTopBrowsers(Period $period, int $maxResults = 10)
    {
        $response = $this->performQuery(
            $period,
            'ga:pageviews',
            [
                'dimensions' => 'ga:browser',
                'sort' => '-ga:pageviews',
                'max-results' => $maxResults,
            ]
        );

        return collect($response['rows'] ?? [])->map(function (array $pageRow) {
            return [
                'browser' => $pageRow[0],
                'pageViews' => (int) $pageRow[1],
            ];
        });
    }


    public function performQuery(Period $period, string $metrics, array $others = [])
    {
        $defaultParams = [
            'start-date' => $period->startDate->format('Y-m-d'),
            'end-date' => $period->endDate->format('Y-m-d'),
            'metrics' => $metrics,
        ];

        $params = array_merge($defaultParams, $others);

        return $this->getAnalyticsService()->data_ga->get(
            'ga:' . config('analytics.view_id'),
            $period->startDate->format('Y-m-d'),
            $period->endDate->format('Y-m-d'),
            $metrics,
            $others
        );
    }

    public function fetchUserByTimeOfDay(Period $period)
    {
        $response = $this->performQuery(
            $period,
            'ga:sessions',
            [
                'dimensions' => 'ga:hour',
            ]
        );

        return collect($response['rows'] ?? [])->map(function (array $userTypeRow) {
            return [
                'hour' => $userTypeRow[0],
                'sessions' => (int) $userTypeRow[1],
            ];
        });
    }

    public function getSessionCount(Period $period)
    {
        $response = $this->performQuery(
            $period,
            'ga:sessions',
            [
                'dimensions' => 'ga:date',
            ]
        );

        return collect($response['rows'] ?? [])->map(function (array $pageRow) {
            return [
                'date' => Carbon::createFromFormat('Ymd', $pageRow[0])->toDateString(),
                'sessions' => (int) $pageRow[1],
            ];
        });
    }

    public function fetchSessionCountRealTime()
    {
        $response = $this->getAnalyticsService()->data_realtime->get(
            'ga:' . config('analytics.view_id'),
            'rt:activeVisitors'
        );

        return $response->totalsForAllResults['rt:activeVisitors'];
    }
    public function fetchSessionCountRealTimeAndDevice()
    {
        $response = $this->getAnalyticsService()->data_realtime->get(
            'ga:' . config('analytics.view_id'),
            'rt:activeVisitors',
            [
                'dimensions' => 'rt:deviceCategory'
            ]
        );

        return collect($response['rows'] ?? [])->map(function (array $pageRow) {
            return [
                'deviceCategory' => $pageRow[0],
                'sessions' => (int) $pageRow[1],
            ];
        });
    }

    public function getAnalyticsService()
    {
        $analytics = Analytics::getAnalyticsService();
        return $analytics;
    }
}
