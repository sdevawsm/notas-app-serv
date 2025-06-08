<?php

namespace LadyPHP\Database\ORM\Events;

use LadyPHP\Database\ORM\Model;

class ModelEvents
{
    // Eventos de Criação
    public const CREATING = 'creating';
    public const CREATED = 'created';

    // Eventos de Atualização
    public const UPDATING = 'updating';
    public const UPDATED = 'updated';

    // Eventos de Exclusão
    public const DELETING = 'deleting';
    public const DELETED = 'deleted';

    // Eventos de Restauração
    public const RESTORING = 'restoring';
    public const RESTORED = 'restored';

    // Eventos de Salvamento
    public const SAVING = 'saving';
    public const SAVED = 'saved';

    // Eventos de Carregamento
    public const RETRIEVING = 'retrieving';
    public const RETRIEVED = 'retrieved';

    // Eventos de Relacionamento
    public const RELATION_ATTACHING = 'relation.attaching';
    public const RELATION_ATTACHED = 'relation.attached';
    public const RELATION_DETACHING = 'relation.detaching';
    public const RELATION_DETACHED = 'relation.detached';
    public const RELATION_SYNCING = 'relation.syncing';
    public const RELATION_SYNCED = 'relation.synced';
} 