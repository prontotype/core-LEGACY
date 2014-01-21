<?php

namespace Prontotype\User;

Class User {

    protected $props = array();

    public function __construct($props)
    {
        $this->props = $props;
    }

    public function name()
    {
        return 'foo';
    }

    public function __get($key)
    {
        return isset($this->props[$key]) ? $this->props[$key] : null;
    }

    public function __isset($key)
    {
        return isset($this->props[$key]);
    }

}