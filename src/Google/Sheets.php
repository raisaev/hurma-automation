<?php

declare(strict_types=1);

namespace App\Google;

use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Google\Service\Sheets\UpdateValuesResponse;
use Google\Service\Sheets\ValueRange;

class Sheets
{
    private GoogleSheets $sheets;

    public function __construct(
        GoogleClient $client = new GoogleClient(),
    ) {
        $client->setScopes([GoogleSheets::SPREADSHEETS]);
        $client->useApplicationDefaultCredentials();

        $this->sheets = new GoogleSheets($client);
    }

    /**
     * @return list<mixed>
     */
    public function getRange(string $sheetId, ?string $sheetName, string $range): array
    {
        return $this->sheets->spreadsheets_values
            ->get($sheetId, $sheetName !== null ? "$sheetName!$range" : $range)
            ->getValues();
    }

    public function writeCell(string $sheetId, ?string $sheetName, string $cell, string $value): UpdateValuesResponse
    {
        return $this->writeRange(
            sheetId  : $sheetId,
            sheetName: $sheetName,
            range    : "$cell:$cell",
            values   : [[$value]]
        );
    }

    public function writeRange(string $sheetId, ?string $sheetName, string $range, array $values): UpdateValuesResponse
    {
        return $this->sheets->spreadsheets_values
            ->update(
                $sheetId,
                $sheetName !== null ? "$sheetName!$range" : $range,
                new ValueRange(['values' => $values]),
                ['valueInputOption' => 'USER_ENTERED']
            );
    }

    public static function columnCode(int $index): string
    {
        $map = [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'AA',
            'AB',
            'AC',
            'AD',
            'AE',
            'AF',
            'AG',
            'AH',
            'AI',
            'AJ',
            'AK',
            'AL',
            'AM',
            'AN',
            'AO',
            'AP',
            'AQ',
            'AR',
            'AS',
            'AT',
            'AU',
            'AV',
            'AW',
            'AX',
            'AY',
            'AZ',
        ];

        return $map[$index] ?? throw new \RuntimeException(sprintf('Can column code by index: %d', $index));
    }

    public static function rowCode(int $index): int
    {
        return $index + 1;
    }
}
