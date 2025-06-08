<?php

namespace App\Events;

class UserRegistered {
    public $user;
    public function __construct($user) {}
}
