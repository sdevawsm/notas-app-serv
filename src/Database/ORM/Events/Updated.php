<?php

namespace LadyPHP\Database\ORM\Events;

class Updated extends ModelEvent
{
    public function getName(): string
    {
        return ModelEvents::UPDATED;
    }
} 