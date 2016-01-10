<?php

namespace FireflyIII\Repositories\PiggyBank;

use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;
use FireflyIII\Models\{
    PiggyBank, PiggyBankEvent
};

/**
 * Class PiggyBankRepository
 *
 * @package FireflyIII\Repositories\PiggyBank
 */
class PiggyBankRepository implements PiggyBankRepositoryInterface
{

    /**
     * @param PiggyBank $piggyBank
     * @param           $amount
     *
     * @return bool
     */
    public function createEvent(PiggyBank $piggyBank, $amount)
    {
        PiggyBankEvent::create(['date' => Carbon::now(), 'amount' => $amount, 'piggy_bank_id' => $piggyBank->id]);

        return true;
    }

    /**
     * @param PiggyBank $piggyBank
     *
     * @return boolean|null
     */
    public function destroy(PiggyBank $piggyBank)
    {
        return $piggyBank->delete();
    }

    /**
     * @param PiggyBank $piggyBank
     *
     * @return Collection
     */
    public function getEventSummarySet(PiggyBank $piggyBank)
    {
        return DB::table('piggy_bank_events')->where('piggy_bank_id', $piggyBank->id)->groupBy('date')->get(['date', DB::Raw('SUM(`amount`) AS `sum`')]);
    }

    /**
     * @param PiggyBank $piggyBank
     *
     * @return Collection
     */
    public function getEvents(PiggyBank $piggyBank)
    {
        return $piggyBank->piggyBankEvents()->orderBy('date', 'DESC')->orderBy('id', 'DESC')->get();
    }

    /**
     * @return Collection
     */
    public function getPiggyBanks()
    {
        /** @var Collection $set */
        $set = Auth::user()->piggyBanks()->orderBy('order', 'ASC')->get();

        return $set;
    }

    /**
     * Set all piggy banks to order 0.
     *
     * @return boolean
     */
    public function reset()
    {
        // split query to make it work in sqlite:
        $set = PiggyBank::
        leftJoin('accounts', 'accounts.id', '=', 'piggy_banks.id')
                        ->where('accounts.user_id', Auth::user()->id)->get(['piggy_banks.*']);
        foreach ($set as $e) {
            $e->order = 0;
            $e->save();
        }

        return true;
    }

    /**
     *
     * set id of piggy bank.
     *
     * @param int $piggyBankId
     * @param int $order
     *
     * @return void
     */
    public function setOrder(int $piggyBankId, int $order)
    {
        $piggyBank = PiggyBank::leftJoin('accounts', 'accounts.id', '=', 'piggy_banks.account_id')->where('accounts.user_id', Auth::user()->id)
                              ->where('piggy_banks.id', $piggyBankId)->first(['piggy_banks.*']);
        if ($piggyBank) {
            $piggyBank->order = $order;
            $piggyBank->save();
        }
    }

    /**
     * @param array $data
     *
     * @return PiggyBank
     */
    public function store(array $data)
    {
        $data['remind_me']     = false;
        $data['reminder_skip'] = 0;

        $piggyBank = PiggyBank::create($data);

        return $piggyBank;
    }

    /**
     * @param PiggyBank $piggyBank
     * @param array     $data
     *
     * @return PiggyBank
     */
    public function update(PiggyBank $piggyBank, array $data)
    {

        $piggyBank->name         = $data['name'];
        $piggyBank->account_id   = intval($data['account_id']);
        $piggyBank->targetamount = floatval($data['targetamount']);
        $piggyBank->targetdate   = $data['targetdate'];
        $piggyBank->startdate    = $data['startdate'];

        $piggyBank->save();

        return $piggyBank;
    }
}
