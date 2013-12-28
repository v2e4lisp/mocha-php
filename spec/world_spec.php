<?php
namespace Preview\DSL\BDD;

use Preview\Preview;
use Preview\World;
use Preview\Configuration;
use Preview\Reporter\Base as BaseReporter;

require_once 'helper.php';

describe("World", function () {
    before_each(function () {
        $this->world = new World;
    });

    describe("#current", function () {
        context("when nothing pushed in", function () {
            it ("should return null", function () {
                ok(is_null($this->world->current()));
            });
        });
    });

    describe("#push", function () {
        it ("should push item into the stack", function () {
            $this->world->push(1);
            ok($this->world->current() == 1);
        });
    });

    describe("#pop", function () {
        context("when nothing pushed in", function () {
            it ("should return null", function () {
                ok(is_null($this->world->pop()));
            });
        });

        context("when some items pushed in", function () {
            before_each(function () {
                $this->world->push(1);
                $this->world->push(2);
            });

            it ("should return the last item", function () {
                ok($this->world->pop() == 2);
            });

            it ("should remove the last item", function () {
                $this->world->pop();
                ok($this->world->current() == 1);
            });
        });
    });

    describe("#groups", function () {
        context("when no test groups", function () {
            it ("should return an empty array", function () {
                $groups = $this->world->groups();
                ok(is_array($groups) and empty($groups));
            });
        });
    });

    describe("#add_test_to_group", function () {
        it("should add test to group", function () {
            $this->world->add_test_to_group("a-test", "a-group");
            ok($this->world->groups() == array("a-group" => array("a-test")));
        });
    });

    describe("#run", function () {
        before_each(function () {
            $this->test_config = Preview::$config;
        });

        before_each(function () {
            $this->config = new Configuration;
            $this->config->reporter = new BaseReporter;
        });

        before_each(function () {
            $this->test1 = new \FakeTest("test-1");
            $this->test2 = new \FakeTest("test-2");
        });

        context("when no test groups specified", function () {
            it ("should run all the tests", function () {
                Preview::$config = $this->config;

                $this->world->push($this->test1);
                $this->world->pop();
                $this->world->push($this->test2);
                $results = $this->world->run();

                Preview::$config = $this->test_config;

                ok(count($results) == 2);
            });
        });

        context("when test groups specified", function () {
            before_each(function () {
                $this->config->test_groups = array("group-1");
                $this->world->add_test_to_group($this->test1, "group-1");
            });

            it("should only run tests in the test groups", function () {
                Preview::$config = $this->config;

                $this->world->push($this->test1);
                $this->world->pop();
                $this->world->push($this->test2);
                $results = $this->world->run();

                Preview::$config = $this->test_config;

                ok(count($results) == 1);
                ok($results[0]->title == "test-1");
            });
        });
    });
});