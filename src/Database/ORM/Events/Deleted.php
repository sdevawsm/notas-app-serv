<?php

namespace LadyPHP\Database\ORM\Events;

class Deleted extends ModelEvent
{
    public function getName(): string
    {
        return ModelEvents::DELETED;
    }
} 