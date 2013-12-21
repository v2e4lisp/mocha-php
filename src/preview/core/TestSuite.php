<?php
/**
 * Test suite class
 * It contains children test cases and test suites.
 * Test suites can be nested.
 *
 * @package Preview
 * @author Wenjun Yan
 * @email mylastnameisyan@gmail.com
 */

namespace Preview\Core;

use Preview\Configuration;
use Preview\Result\TestSuite as TestSuiteResult;

class TestSuite extends TestBase {
    /**
     * before hooks for itself.
     *
     * @var array $before_hooks
     */
    protected $before_hooks = array();

    /**
     * before hooks for children test cases
     *
     * @var array $before_each_hooks
     */
    protected $before_each_hooks = array();

    /**
     * after hooks for itself
     *
     * @var array $after_hooks
     */
    protected $after_hooks = array();

    /**
     * after hooks for children test cases
     *
     * @var array $after_each_hooks
     */
    protected $after_each_hooks = array();

    /**
     * children test suite objects
     *
     * @var array $suites
     */
    public $suites = array();

    /**
     * children test case objects
     *
     * @var array $cases
     */
    public $cases = array();

    public function __construct($title, $fn) {
        parent::__construct($title, $fn);
        $this->result = new TestSuiteResult($this);
    }

    /**
     * Skip this test. recursively mark all its children to be skipped.
     *
     * @param null
     * @retrun null
     */
    public function skip() {
        parent::skip();
        foreach ($this->suites as $suite) {
            $suite->skip();
        }
        foreach ($this->cases as $case) {
            $case->skip();
        }
    }

    /**
     * set parent test suite.
     *
     * @param object|null $suite: parent test suite
     * @return null
     */
    public function set_parent($suite) {
        // if empty($suite), that's a start point of the TEST-WORLD.
        if (!empty($suite)) {
            $suite->suites[] = $this;
            $this->parent = $suite;
        }
    }

    /**
     * Add test child suite/case
     *
     * @param object $suite
     * @retrun object $this
     */
    public function add($suite_or_case) {
        $suite_or_case->set_parent($this);
        return $this;
    }

    /**
     * Invoke $fn and randomize children cases and suites.
     *
     * @param null
     * @return null
     */
    public function setup() {
        if ($this->pending) {
            return;
        }

        $this->fn->__invoke();
        shuffle($this->cases);
        shuffle($this->suites);
    }

    /**
     * Run this suites.
     * 1. Call Reporter's before_suite method.
     * 2. Run before hooks.
     * 3. Run children test cases and suites.
     * 4. Run after hooks.
     * 5. Call Reporters's after_suite method.
     *
     * @param null
     * @return null
     */
    public function run() {
        if (!$this->runnable()) {
            return;
        }

        if ($this->parent) {
            $this->context = (object) array_merge((array) $this->context,
                                                  (array) $this->parent->context);
        }

        $this->timer->start();
        Configuration::reporter()->before_suite($this->result);
        $this->run_before();
        foreach ($this->cases as $case) {
            $case->run();
        }
        foreach ($this->suites as $suite) {
            $suite->run();
        }
        $this->finish();
        $this->run_after();
        Configuration::reporter()->after_suite($this->result);
        $this->timer->stop();
    }

    /**
     * Run before hooks.
     *
     * @param null
     * @return null
     */
    public function run_before() {
        foreach ($this->before_hooks as $before) {
            $this->invoke_closure_with_context($before, $this->context);
        }
    }

    /**
     * Run children cases' before hooks
     * This method should be called only by its children test cases.
     *
     * @param null
     * @return null
     */
    public function run_before_each($context) {
        if ($this->parent) {
            $this->parent->run_before_each($context);
        }

        foreach ($this->before_each_hooks as $before) {
            $this->invoke_closure_with_context($before, $context);
        }
    }

    /**
     * run after hooks
     *
     * @param null
     * @return null
     */
    public function run_after() {
        foreach ($this->after_hooks as $after) {
            $this->invoke_closure_with_context($after, $this->context);
        }
    }

    /**
     * Run children test cases' after hooks
     * This method should be called only by its children test cases.
     *
     * @param null
     * @return null
     */
    public function run_after_each($context) {
        if ($this->parent) {
            $this->parent->run_after_each($context);
        }

        foreach ($this->after_each_hooks as $after) {
            $this->invoke_closure_with_context($after, $this->context);
        }
    }

    /**
     * add before hooks.
     *
     * @param function $fn
     * @retrun object $this
     */
    public function before($fn) {
        $this->before_hooks[] = $fn;
        return $this;
    }

    /**
     * add after hooks.
     *
     * @param function $fn
     * @retrun object $this
     */
    public function after($fn) {
        $this->after_hooks[] = $fn;
        return $this;
    }

    /**
     * add before_each hooks.
     *
     * @param function $fn
     * @retrun object $this
     */
    public function before_each($fn) {
        $this->before_each_hooks[] = $fn;
        return $this;
    }

    /**
     * add after_each hooks.
     *
     * @param function $fn
     * @retrun object $this
     */
    public function after_each($fn) {
        $this->after_each_hooks[] = $fn;
        return $this;
    }

    /**
     * check if this test suite is in test group
     *
     * @param null
     * @retrun bool
     */
    public function in_test_group() {
        if (!is_null($this->in_test_group)) {
            return $this->in_test_group;
        }

        $groups = Configuration::$test_groups;
        $this->in_test_group = false;

        foreach ($groups as $group) {
            if ($this->in_group($group)) {
                $this->in_test_group = true;
            }
        }
        foreach ($this->suites as $suite) {
            if ($suite->in_test_group()) {
                $this->in_test_group = true;
            }
        }
        foreach ($this->cases as $case) {
            if ($case->in_test_group()) {
                $this->in_test_group = true;
            }
        }

        return $this->in_test_group;
    }
}