<?php

namespace Tests\Amtgard\IAM\Fixtures;

use Amtgard\IAM\Requirement\Requirement;

class CustomProvisoRequirement extends Requirement
{
    public function ornSegmentSchema(): array
    {
        return ['tenant-id', 'org unit'];
    }

    protected function getResourceMap(?string $resource = null): array
    {
        $map = [
            'Widget' => ['Read'],
        ];

        return $resource ? $map[$resource] : $map;
    }
}
