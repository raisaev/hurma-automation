<?php

declare(strict_types=1);

namespace App\Hurma;

class SheetRecord
{
    public const SOURCE_OWN        = 'own';
    public const SOURCE_ADDITIONAL = 'add';

    public const STATUS_DONE      = 'done';
    public const STATUS_SKIPPED   = 'skipped';
    public const STATUS_NOT_FOUND = 'not found';

    /**
     * @param self::SOURCE_* $source
     * @param self::STATUS_* $status
     */
    public function __construct(
        public int $index,
        public string $from,
        public string $name,
        public string $source,
        public int $coinCount,
        public string $coinComment,
        public string $status,
    ) {
    }

    public function isStatusDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }
}
