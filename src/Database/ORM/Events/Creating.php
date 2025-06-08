<?php

namespace LadyPHP\Database\ORM\Events;

class Creating extends ModelEvent
{
    public function getName(): string
    {
        return ModelEvents::CREATING;
    }
} 