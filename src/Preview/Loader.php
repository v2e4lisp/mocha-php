<?php

namespace Preview;

/**
 * Loader class to load test files
 *
 * @package Preview
 * @author Wenjun Yan
 * @email mylastnameisyan@gmail.com
 */
class Loader {
    /**
     * Load test file(s) or dir(s) by path
     *
     * @param string $path
     * @return null
     */
    public function load($path) {
        $path = realpath($path);
        if (!(file_exists($path))) {
            throw new \Exception("No such file or dir found : {$path}");
        }

        if (is_dir($path)) {
            $this->load_dir($path);
        } else {
            $this->load_file($path);
        }
    }

    /**
     * Load test a file by file path
     *
     * @param string $path
     * @return null
     */
    private function load_file($path) {
        if (!$this->is_spec_file($path)) {
            return false;
        }

        require_once $path;
    }

    /**
     * Recursively load all test files in a dir.
     * shared dir will be first loaded if there is one in current dir.
     *
     * @param string $path
     * @return null
     */
    private function load_dir($path) {
        $shared = Preview::$config->shared_dir_name;

        // load shared dir first
        $dirs = scandir($path);
        if(($key = array_search($shared, $dirs)) !== false) {
            $this->load("{$path}/{$shared}");
            unset($dirs[$key]);
        }

        foreach ($dirs as $p) {
            if ($p[0] != ".") {
                $this->load("{$path}/{$p}");
            }
        }
    }

    /**
     * Check if it's a test file by its filename
     *
     * @param string $param
     * @return bool
     */
    private function is_spec_file($file) {
        return preg_match(Preview::$config->spec_file_regexp, $file);
    }
}
