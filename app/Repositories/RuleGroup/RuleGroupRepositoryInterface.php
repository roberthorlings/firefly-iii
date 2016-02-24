<?php
declare(strict_types = 1);

namespace FireflyIII\Repositories\RuleGroup;


use FireflyIII\Models\RuleGroup;
use FireflyIII\User;
use Illuminate\Support\Collection;

/**
 * Interface RuleGroupRepositoryInterface
 *
 * @package FireflyIII\Repositories\RuleGroup
 */
interface RuleGroupRepositoryInterface
{


    /**
     * @return int
     */
    public function count();

    /**
     * @param RuleGroup $ruleGroup
     * @param RuleGroup $moveTo
     *
     * @return bool
     */
    public function destroy(RuleGroup $ruleGroup, RuleGroup $moveTo = null);

    /**
     * @return Collection
     */
    public function get();

    /**
     * @return int
     */
    public function getHighestOrderRuleGroup();

    /**
     * @param User $user
     *
     * @return Collection
     */
    public function getRuleGroupsWithRules(User $user): Collection;

    /**
     * @param RuleGroup $ruleGroup
     *
     * @return bool
     */
    public function moveDown(RuleGroup $ruleGroup);

    /**
     * @param RuleGroup $ruleGroup
     *
     * @return bool
     */
    public function moveUp(RuleGroup $ruleGroup);

    /**
     * @return bool
     */
    public function resetRuleGroupOrder();

    /**
     * @param RuleGroup $ruleGroup
     *
     * @return bool
     */
    public function resetRulesInGroupOrder(RuleGroup $ruleGroup);

    /**
     * @param array $data
     *
     * @return RuleGroup
     */
    public function store(array $data);

    /**
     * @param RuleGroup $ruleGroup
     * @param array     $data
     *
     * @return RuleGroup
     */
    public function update(RuleGroup $ruleGroup, array $data);

    /**
     * Finds a rule group by title
     * @param unknown $title
     * @return RuleGroup
     */
    public function findByTitle(User $user, $title); 
    
}
