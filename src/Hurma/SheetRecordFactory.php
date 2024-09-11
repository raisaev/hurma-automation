<?php

declare(strict_types=1);

namespace App\Hurma;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class SheetRecordFactory
{
    public function __construct(
        #[Autowire(env: 'FIELD_FROM')] public string $from,
        #[Autowire(env: 'FIELD_NAME')] public string $name,
        #[Autowire(env: 'FIELD_SOURCE')] public string $source,
        #[Autowire(env: 'FIELD_COINS')] public string $coins,
        #[Autowire(env: 'FIELD_COMMENT')] public string $comment,
        #[Autowire(env: 'FIELD_STATUS')] public string $status,
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
            coinCount  : (int) ($row[$this->coins] ?? 0),
            coinComment: $row[$this->comment] ?? null,
            status     : $row[$this->status] ?? null,
        );
    }
}
