<?php

namespace FireflyIII\Generator\Chart\Account;

use Carbon\Carbon;
use FireflyIII\Models\Account;
use Illuminate\Support\Collection;
use Steam;

/**
 * Class ChartJsAccountChartGenerator
 *
 * @package FireflyIII\Generator\Chart\Account
 */
class ChartJsAccountChartGenerator implements AccountChartGenerator
{

    /**
     * @param Collection $accounts
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return array
     */
    public function expenseAccounts(Collection $accounts, Carbon $start, Carbon $end)
    {
        $data = [
            'count'  => 1,
            'labels' => [], 'datasets' => [[
                                               'label' => trans('firefly.spent'),
                                               'data'  => []]]];

        bcscale(2);
        $start->subDay();
        $ids           = $this->getIdsFromCollection($accounts);
        $startBalances = Steam::balancesById($ids, $start);
        $endBalances   = Steam::balancesById($ids, $end);

        $accounts->each(
            function (Account $account) use ($startBalances, $endBalances) {
                $id                  = $account->id;
                $startBalance        = $startBalances[$id] ?? '0';
                $endBalance          = $endBalances[$id] ?? '0';
                $diff                = bcsub($endBalance, $startBalance);
                $account->difference = round($diff, 2);
            }
        );

        $accounts = $accounts->sortByDesc(
            function (Account $account) {
                return $account->difference;
            }
        );

        foreach ($accounts as $account) {
            if ($account->difference > 0) {
                $data['labels'][]              = $account->name;
                $data['datasets'][0]['data'][] = $account->difference;
            }
        }

        return $data;
    }

    /**
     * @param Collection $accounts
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return array
     */
    public function frontpage(Collection $accounts, Carbon $start, Carbon $end)
    {
        // language:
        $format  = trans('config.month_and_day');
        $data    = [
            'count'    => 0,
            'labels'   => [],
            'datasets' => [],
        ];
        $current = clone $start;
        while ($current <= $end) {
            $data['labels'][] = $current->formatLocalized($format);
            $current->addDay();
        }


        foreach ($accounts as $account) {
            $set      = [
                'label'                => $account->name,
                'fillColor'            => 'rgba(220,220,220,0.2)',
                'strokeColor'          => 'rgba(220,220,220,1)',
                'pointColor'           => 'rgba(220,220,220,1)',
                'pointStrokeColor'     => '#fff',
                'pointHighlightFill'   => '#fff',
                'pointHighlightStroke' => 'rgba(220,220,220,1)',
                'data'                 => [],
            ];
            $current  = clone $start;
            $range    = Steam::balanceInRange($account, $start, clone $end);
            $previous = array_values($range)[0];
            while ($current <= $end) {
                $format  = $current->format('Y-m-d');
                $balance = $range[$format] ?? $previous;

                $set['data'][] = round($balance, 2);
                $previous      = round($balance, 2);
                $current->addDay();
            }
            $data['datasets'][] = $set;
        }
        $data['count'] = count($data['datasets']);

        return $data;
    }

    /**
     * @param Account $account
     * @param Carbon  $start
     * @param Carbon  $end
     *
     * @return array
     */
    public function single(Account $account, Carbon $start, Carbon $end)
    {
        // language:
        $format = trans('config.month_and_day');

        $data     = [
            'count'    => 1,
            'labels'   => [],
            'datasets' => [
                [
                    'label' => $account->name,
                    'data'  => []
                ]
            ],
        ];
        $range    = Steam::balanceInRange($account, $start, $end);
        $current  = clone $start;
        $previous = array_values($range)[0];

        while ($end >= $current) {
            $theDate = $current->format('Y-m-d');
            $balance = $range[$theDate] ??  $previous;

            $data['labels'][]              = $current->formatLocalized($format);
            $data['datasets'][0]['data'][] = round($balance,2);
            $previous                      = round($balance,2);
            $current->addDay();
        }

        return $data;
    }

    /**
     * @param Collection $collection
     *
     * @return array
     */
    protected function getIdsFromCollection(Collection $collection)
    {
        $ids = [];
        foreach ($collection as $entry) {
            $ids[] = $entry->id;
        }

        return array_unique($ids);

    }
}
