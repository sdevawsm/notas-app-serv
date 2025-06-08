<?php

namespace LadyPHP\Database\ORM\Events;

class Updating extends ModelEvent
{
    public function getName(): string
    {
        return ModelEvents::UPDATING;
    }
} 