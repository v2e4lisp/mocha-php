<?php
namespace Preview\DSL\BDD;

use Preview\Preview;
use Preview\World;
use Preview\Configuration;

require_once __DIR__.'/../../helper.php';

describe("bdd[context]", function () {
    before_each(function () {
        $this->test_world = Preview::$world;
        $this->test_config = Preview::$config;
    });

    before_each(function () {
        $this->world = new World;
        $this->config = new Configuration;
        $this->config->reporter = new \Recorder;
    });

    context("test suite", function () {
        it("share the context with before/after hook", function () {
            // start new env
            Preview::$world = $this->world;
            Preview::$config = $this->config;
            $tmp = null;

            describe("sample suite", function () use (&$tmp) {
                before(function () use (&$tmp) {
                    // $this->user = "wenjun.yan";
                });

                it("context diff from suite context", function () use ($tmp) {
                    ok($this !== $tmp);
                });

                after(function () use ($tmp) {
                    // ok($this === $tmp);
                });

            });
            $results = Preview::$world->run();

            // end new env
            Preview::$world = $this->test_world;
            Preview::$config = $this->test_config;
            $result = $results[0];
            ok($result->passed());
        });

        it("extend the context of its parent test suite", function () {
            // start new env
            Preview::$world = $this->world;
            Preview::$config = $this->config;

            describe("parent suite", function () {
                before(function () {
                    $this->user = "wenjun.yan";
                });

                describe("sample suite", function () {
                    before(function () {
                        $this->shared = ($this->user == "wenjun.yan");
                    });

                    it("access suite context", function () {
                        ok($this->shared);
                    });
                });
            });
            $results = Preview::$world->run();

            // end new env
            Preview::$world = $this->test_world;
            Preview::$config = $this->test_config;
            $result = $results[0];
            ok($result->passed());
        });
    });

    context("test case", function (){
        it("share the context with before/after each hook", function () {
            // start new env
            Preview::$world = $this->world;
            Preview::$config = $this->config;
            $context_tmp = null;

            describe("context sample", function () use (&$context_tmp) {
                before_each(function () use (&$context_tmp) {
                    $this->user = "wenjun.yan";
                    $context_tmp = $this;
                });

                it("should have a user", function () use (&$context_tmp) {
                    ok($this === $context_tmp);
                });

                after_each(function () use (&$context_tmp) {
                    ok($this === $context_tmp);
                });
            });
            $results = Preview::$world->run();

            // end new env
            Preview::$world = $this->test_world;
            Preview::$config = $this->test_config;

            $suite_result = $results[0];
            $cases_result = $suite_result->cases();
            ok($suite_result->passed());
            ok($cases_result[0]->passed());
        });

        it("share the context with subject and let");

        it("should extend test suite context", function () {
            // start new env
            Preview::$world = $this->world;
            Preview::$config = $this->config;

            describe("context sample", function () {
                before(function () {
                    $this->user = "wenjun.yan";
                });

                before_each(function () {
                    ok($this->user == "wenjun.yan");
                });

                it("should have a user", function () {
                    ok($this->user == "wenjun.yan");
                });

                after_each(function () {
                    ok($this->user == "wenjun.yan");
                });
            });
            $results = Preview::$world->run();

            // end new env
            Preview::$world = $this->test_world;
            Preview::$config = $this->test_config;

            $result = $results[0];
            ok($result->passed());
        });
    });
});