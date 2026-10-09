<?php

namespace App\Services;

use App\Models\User;

final class ProjectOwner
{
    public function __construct(
        public readonly string $label,
        public readonly ?User $user = null,
        public readonly bool $isDepartmentWide = false,
        public readonly bool $isTerminal = false,
    ) {
    }
}
