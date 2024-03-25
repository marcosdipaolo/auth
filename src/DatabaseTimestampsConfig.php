<?php

namespace MDP\Auth;

class DatabaseTimestampsConfig
{
    public function __construct(
        public bool $enabled,
        public string $createdAtFieldName = 'created_at',
        public string $updatedAtFieldName = 'updated_at'
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
