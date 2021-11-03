<?php

declare(strict_types=1);

namespace App\Hurma;

use App\Google\Sheets;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;

class CoinProcessor
{
    public function __construct(
        private readonly Client $client,
        private readonly Sheets $sheets,
        private readonly SheetRecordFactory $recordFactory,
    ) {
    }

    // ----------------------------------------

    public function process(
        string $sheetId,
        ?string $sheetName,
        string $sheetRange,
        bool $dryRun = false
    ): array {
        $range = $this->sheets->getRange($sheetId, $sheetName, $sheetRange);
        if (count($range) <= 1) {
            return [];
        }

        $this->recordFactory->validate($range);

        $statusIndex = array_search($this->recordFactory->status, $range[0], true);
        $this->openManagementPage();

        //todo [r.isaev] move it from here
        $processed = [];
        $ignored = [
            '#m2echarity🙌',
            'на авто для ЗСУ🛻',
            'Product Team',
            'Sharks',
            'Space Whales',
            'Support Team',
            'M2E Pro Team',
            'HR Team',
            'Content Team',
        ];
        foreach (array_slice($range, 1) as $index => $row) {
            $record = $this->recordFactory->create($range, ++$index);

            if ($record->coinCount === 0 || $record->isStatusDone() || in_array($record->name, $ignored, true)) {
                $processed[$record->name] = $record::STATUS_SKIPPED;
                continue;
            }

            if (!$this->search($record)) {
                !$dryRun && $this->sheets->writeCell(
                    $sheetId,
                    $sheetName,
                    Sheets::columnCode($statusIndex) . Sheets::rowCode($record->index),
                    'not found'
                );

                $processed[$record->name] = $record::STATUS_NOT_FOUND;
                continue;
            }

            $processed[$record->name] = $record::STATUS_DONE;
            if ($dryRun) {
                continue;
            }

            try {
                $this->submitCoin($record);
                $message = $record::STATUS_DONE;
            } catch (\Throwable $e) {
                $message = "error: {$e->getMessage()}";
                $processed[$record->name] = $message;
            }

            $this->sheets->writeCell(
                $sheetId,
                $sheetName,
                Sheets::columnCode($statusIndex) . Sheets::rowCode($record->index),
                $message
            );
        }

        return $processed;
    }

    // ----------------------------------------

    private function openManagementPage(): void
    {
        $driver = $this->client->getDriver();

        $this->client->ensureLogin();
        $driver->get($this->client->url() . 'company-absence-request');

        usleep(1_000_000);

        $driver
            ->findElements(WebDriverBy::xpath('//button[@class="tab-item"]'))[1]
            ->click();

        usleep(1_000_000);

        $driver
            ->findElements(WebDriverBy::xpath('//div[@class="cus-select-avatar__header"]'))[0]
            ->click();

        usleep(1_000_000);

        $driver
            ->findElements(WebDriverBy::xpath('//div[@class="cus-select-avatar__body open"]//div[@class="cus-select-avatar__item"]'))[1]
            ->click();

        usleep(1_000_000);
    }

    private function search(SheetRecord $record): bool
    {
        $search = $this->client->getDriver()
            ->findElements(WebDriverBy::xpath('//div[@class="cus-select-avatar__search"]//input[@type="search"]'))[0];

        $search->sendKeys(WebDriverKeys::ESCAPE);
        usleep(1_000_000);

        $search->sendKeys($record->name);
        usleep(1_000_000);

        $elems = $this->client->getDriver()
            ->findElements(WebDriverBy::xpath('//div[@class="v-data-table__checkbox v-simple-checkbox"]'));

        if (count($elems) !== 1) {
            return false;
        }

        $elems[0]->click();

        return true;
    }

    private function submitCoin(SheetRecord $record): void
    {
        $this->client->getDriver()
             ->findElements(WebDriverBy::xpath('//button[@data-target="#editModalRequest"]'))[0]
             ->click();

        usleep(500_000);

        if ($record->coinCount < 0) {
            $this->client->getDriver()
                ->findElements(WebDriverBy::xpath('//div[@id="editModalRequest"]//input[@id="sub_days-action"]'))[0]
                ->click();
        }

        $this->client->getDriver()
            ->findElements(WebDriverBy::xpath('//div[@id="editModalRequest"]//input[@name="days"]'))[0]
            ->clear()
            ->sendKeys(abs($record->coinCount));

        $this->client->getDriver()
            ->findElements(WebDriverBy::xpath('//div[@id="editModalRequest"]//textarea[@name="comment"]'))[0]
            ->clear()
            ->sendKeys("$record->from: $record->coinComment");

        $this->client->getDriver()
            ->findElements(WebDriverBy::xpath('//div[@id="editModalRequest"]//button[contains(@class, "btn")]'))[0]
            ->click();
    }
}
