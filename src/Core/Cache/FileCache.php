<?php

namespace Core\Cache;

class FileCache {
    protected $path;
    public function __construct($path) {}
    public function get($key) {}
    public function set($key, $value, $ttl = 3600) {}
    public function delete($key) {}
}
