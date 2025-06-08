<?php

namespace Core;

class EventDispatcher {
    protected $listeners = [];
    public function listen($event, $listener) {}
    public function dispatch($event, $payload = null) {}
}
