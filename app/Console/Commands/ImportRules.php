<?php

namespace FireflyIII\Console\Commands;

use FireflyIII\User;
use Illuminate\Console\Command;

class ImportRules extends Command
{
    const HEADER_RULEGROUP = "rule group";
    const HEADER_RULENAME = "name";
    const HEADER_TRIGGERTYPE= "trigger";
    const HEADER_TRIGGERVALUE = "trigger value";
    const HEADER_BUDGET = "budget";
    const HEADER_CATEGORY = "category";
    const HEADER_TAG = "tag";
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rules:import {user_id} {filename} {delimiter=,}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports rules and rule groups from CSV file';

    /**
     * 
     * @var User
     */
    protected $user;
    
    /**
     * Cache to speed up rulegroup lookup
     * @var array
     */
    protected $ruleGroupCache = [];
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @TODO Improve robustness and error handling
     * @return mixed
     */
    public function handle()
    {
        $this->user = $this->loadUser();

        if(!$this->user) {
            $this->error('User not found');
            return;
        }
        
        $filename = $this->argument('filename');
        $delimiter = $this->argument('delimiter');
        
        if(!file_exists($filename) || !is_readable($filename)) {
            $this->error('Given file doesn\'t exist or is not readable.');
            return;
        }
        
        // Load data from the given file
        $fh = fopen($filename, "r");
        
        // Read the first line and store as header
        $headers = $this->parseHeader(fgetcsv($fh, 0, $delimiter));
        
        if(!$this->headersValid($headers)) {
            $this->error('No valid set of headers specified. ' .
                'Required headers are ' . implode([self::HEADER_RULENAME, self::HEADER_TRIGGERTYPE, self::HEADER_TRIGGERVALUE], ', ') .
                'At least one of the following columns must be specified: ' . implode([self::HEADER_BUDGET, self::HEADER_CATEGORY, self::HEADER_TAG], ', '));
            return;
        }
        
        // Loop through remaining lines and store the data
        while(($line = fgetcsv($fh, 0, $delimiter)) !== false) {
            $this->importRule($line, $headers);
        }
        
        fclose($fh);
        $this->info('Finished importing rules');
    }
    
    /**
     * Loads the current user
     */
    protected function loadUser() {
        $userId = $this->argument('user_id');
        return User::find($userId);
    }
    
    /**
     * Converts a single header array into a hashmap to speed up lookups
     * 
     * Supported headers are:
     *      Rule group, Name, Trigger, Trigger value, Budget, Category, Tag
     */
    protected function parseHeader($headers) {
        // Create map with default values
        $map = [
            self::HEADER_RULEGROUP => null,
            self::HEADER_RULENAME => null,
            self::HEADER_TRIGGERTYPE => null,
            self::HEADER_TRIGGERVALUE => null,
            self::HEADER_BUDGET => null,
            self::HEADER_CATEGORY => null,
            self::HEADER_TAG => null,            
        ];
        
        // Now overwrite the values with the column indices from the file
        foreach($headers as $idx => $header) {
            $map[strtolower($header)] = $idx;
        }
        
        return $map;
    }
    
    /**
     * Checks whether the given map of headers is valid
     * @param unknown $headers
     * @return bool
     */
    protected function headersValid($headers): bool {
        // Name and trigger are required
        if( $headers[self::HEADER_RULENAME] === null || $headers[self::HEADER_TRIGGERTYPE] === null || $headers[self::HEADER_TRIGGERVALUE] === null) {
            return false;
        }
        
        // At least a single action is required
        if( $headers[self::HEADER_BUDGET] === null && $headers[self::HEADER_CATEGORY] === null && $headers[self::HEADER_TAG] === null) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Imports a single line from the file 
     * @param array $line
     * @param array $header
     */
    protected function importRule($line, $headers) {
        // First we need a rulegroup to store the rule in
        $ruleGroup = $this->findOrCreateRuleGroup($line, $headers[self::HEADER_RULEGROUP]);
    
        /** @var RuleRepositoryInterface $repository */
        $repository = app('FireflyIII\Repositories\Rule\RuleRepositoryInterface');
        
        $ruleData = $this->getRuleData($line, $headers, $ruleGroup);
        if($ruleData) {
            $this->line("Storing rule " . $ruleData[ "title" ] . " within rule group " . $ruleGroup->title);
            return $repository->store($ruleData);
        } else {
            return null;
        }
    }
    
    /**
     * Finds or creates a rule group, based on the given line
     * @param unknown $line
     * @param unknown $columnIndex
     */
    protected function findOrCreateRuleGroup($line, $columnIndex) {
        // If no rulegroup is specified, use the default one
        if($columnIndex === null || !$line[$columnIndex] ) {
            $ruleGroupName = trans('firefly.default_rule_group_name');
        } else {
            $ruleGroupName = $line[$columnIndex];
        }
        
        // Lookup rulegroup in cache
        if(array_key_exists($ruleGroupName, $this->ruleGroupCache)) {
            return $this->ruleGroupCache[$ruleGroupName];
        }
        
        // Retrieve the repository using DI
        /** @var RuleGroupRepositoryInterface $repository */
        $repository = app('FireflyIII\Repositories\RuleGroup\RuleGroupRepositoryInterface');
        
        // If a rulegroup already exists, reuse it
        $existing = $repository->findByTitle($this->user, $ruleGroupName);
        if($existing) { 
            $ruleGroup = $existing;
        } else {
            // If it doesn't exist, create a new one
            $data = [
                'user_id'     => $this->user->id,
                'title'       => $ruleGroupName,
                'description' => ''
            ];
        
            $ruleGroup = $repository->store($data, $this->user);
        }
        
        // Store rulegroup in cache
        $this->ruleGroupCache[$ruleGroupName] = $ruleGroup;
        return $ruleGroup;        
    }
    
    /**
     * Creates a map with data to store for the rule
     */
    protected function getRuleData($line, $headers, $ruleGroup) {
        // If no name is specified for this rule, skip the line
        $ruleName = $line[$headers[self::HEADER_RULENAME]];
        
        if(!$ruleName) {
            return null;
        }
        
        // Create a map with basic rule data
        $data = [
            'rule_group_id'       => $ruleGroup->id,
            'title'               => $ruleName,
            'description'         => '',
            'user_id'             => $this->user->id,
            'trigger'             => 'store-journal',
            'stop_processing'     => 0,
        ];
        
        // Add information about triggers. There is only a single
        // trigger, so we create an array ourselves
        $data['rule-triggers']          = [$line[$headers[self::HEADER_TRIGGERTYPE]]];
        $data['rule-trigger-values']    = [$line[$headers[self::HEADER_TRIGGERVALUE]]];
        $data['rule-trigger-stop']      = [];
        
        // Add information about actions
        $actions = $this->getActions($line, $headers);
        
        // If no actions are specified, don't store the rule
        if(count($actions) == 0)
            return null;
        
        
        $data['rule-actions']        = array_map(function($action) { return $action['type']; }, $actions);
        $data['rule-action-values']  = array_map(function($action) { return $action['value']; }, $actions);
        $data['rule-action-stop']    = [];
        
        return $data;
    }
    
    /**
     * Create a list of actions that should be executed for the given line
     */
    protected function getActions($line, $headers) {
        $actions = [];
        
        if($headers[self::HEADER_BUDGET] !== null && $line[$headers[self::HEADER_BUDGET]]) {
            $actions[] = [
                'type' => 'set_budget',
                'value' => $line[$headers[self::HEADER_BUDGET]],
            ];
        }
        
        if($headers[self::HEADER_CATEGORY] !== null && $line[$headers[self::HEADER_CATEGORY]]) {
            $actions[] = [
                'type' => 'set_category',
                'value' => $line[$headers[self::HEADER_CATEGORY]],
            ];
        }
        
        if($headers[self::HEADER_TAG] !== null && $line[$headers[self::HEADER_TAG]]) {
            $actions[] = [
                'type' => 'add_tag',
                'value' => $line[$headers[self::HEADER_TAG]],
            ];
        }
        
        return $actions;
    }
}
