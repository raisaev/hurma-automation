<?php

declare(strict_types=1);

namespace App\Hurma;

class SheetRecordFactory
{
    public function __construct(
        public readonly string $from,
        public readonly string $name,
        public readonly string $source,
        public readonly string $coins,
        public readonly string $comment,
        public readonly string $status,
    ) {
    }

    public function validate(array $data): void
    {
        foreach ([$this->from, $this->name, $this->source, $this->coins, $this->comment, $this->status] as $field) {
            if (!in_array($field, $data[0], true)) {
                throw new \InvalidArgumentException("Required header was not found: $field.");
            }
        }
    }

    public function create(array $data, int $index): SheetRecord
    {
        $row = $data[$index];
        if (count($data[0]) > count($row)) {
            $row = array_merge(
                $row,
                array_fill(count($row) + 1, (count($data[0]) - count($row)), '')
            );
        }
        $row = array_combine($data[0], $row);

        return new SheetRecord(
            index      : $index,
            from       : $row[$this->from] ?? null,
            name       : $row[$this->name] ?? null,
            source     : $row[$this->source] ?? null,
            coinCount  : (int)($row[$this->coins] ?? 0),
            coinComment: $row[$this->comment] ?? null,
            status     : $row[$this->status] ?? null,
        );
    }
}
