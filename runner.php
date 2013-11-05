<?php

class Runner {
    protected $start_points = array();

    public function __construct($start_points) {
        $this->start_points = $start_points;
    }

    public function run() {
        $results = array();
        foreach($this->start_points as $point) {
            $results[] = $point->result;
        }

        Configuration::$reporter->before_all($results);
        foreach($this->start_points as $point) {
            $point->run();
        }
        Configuration::$reporter->after_all($results);

        return $results;
    }
}