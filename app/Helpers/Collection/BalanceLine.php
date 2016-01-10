<?php

namespace FireflyIII\Helpers\Collection;

use FireflyIII\Models\Budget as BudgetModel;
use Illuminate\Support\Collection;

/**
 * @codeCoverageIgnore
 *
 * Class BalanceLine
 *
 * @package FireflyIII\Helpers\Collection
 */
class BalanceLine
{

    const ROLE_DEFAULTROLE = 1;
    const ROLE_TAGROLE     = 2;
    const ROLE_DIFFROLE    = 3;

    /** @var  Collection */
    protected $balanceEntries;

    /** @var BudgetModel */
    protected $budget;

    protected $role = self::ROLE_DEFAULTROLE;

    /**
     *
     */
    public function __construct()
    {
        $this->balanceEntries = new Collection;
    }

    /**
     * @param BalanceEntry $balanceEntry
     */
    public function addBalanceEntry(BalanceEntry $balanceEntry)
    {
        $this->balanceEntries->push($balanceEntry);
    }

    /**
     * @return Collection
     */
    public function getBalanceEntries()
    {
        return $this->balanceEntries;
    }

    /**
     * @param Collection $balanceEntries
     */
    public function setBalanceEntries($balanceEntries)
    {
        $this->balanceEntries = $balanceEntries;
    }

    /**
     * @return BudgetModel
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * @param BudgetModel $budget
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;
    }

    /**
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param int $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if ($this->getBudget() instanceof BudgetModel) {
            return $this->getBudget()->name;
        }
        if ($this->getRole() == self::ROLE_DEFAULTROLE) {
            return trans('firefly.noBudget');
        }
        if ($this->getRole() == self::ROLE_TAGROLE) {
            return trans('firefly.coveredWithTags');
        }
        if ($this->getRole() == self::ROLE_DIFFROLE) {
            return trans('firefly.leftUnbalanced');
        }

        return '';
    }

    /**
     * If a BalanceLine has a budget/repetition, each BalanceEntry in this BalanceLine
     * should have a "spent" value, which is the amount of money that has been spent
     * on the given budget/repetition. If you subtract all those amounts from the budget/repetition's
     * total amount, this is returned:
     *
     * @return float
     */
    public function leftOfRepetition()
    {
        $start = $this->budget->amount ?? 0;
        /** @var BalanceEntry $balanceEntry */
        foreach ($this->getBalanceEntries() as $balanceEntry) {
            $start += $balanceEntry->getSpent();
        }

        return $start;
    }
}
