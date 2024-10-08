<?php

declare(strict_types=1);

namespace App\Hurma;

use App\Kernel;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverPlatform;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class Client
{
    private ?RemoteWebDriver $driver;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Kernel $kernel,
        #[Autowire(env: 'CHROME_WEB_DRIVER_URL')] private readonly string $webDriverUrl,
        #[Autowire(env: 'HURMA_URL')] public readonly string $url,
        #[Autowire(env: 'HURMA_LOGIN')] private readonly string $login,
        #[Autowire(env: 'HURMA_PASSWORD')] private readonly string $password,
    ) {
    }

    public function __destruct()
    {
        if (isset($this->driver)) {
            $this->driver->quit();
        }
    }

    public function getDriver(): RemoteWebDriver
    {
        if (!isset($this->driver)) {
            $caps = DesiredCapabilities::chrome();
            $caps->setPlatform(WebDriverPlatform::LINUX);

            $this->driver = RemoteWebDriver::create($this->webDriverUrl, $caps);
        }

        return $this->driver;
    }

    public function ensureLogin(): void
    {
        $this->getDriver()->get($this->url);

        $cookiesFile = $this->kernel->getTmpPath() . 'cookies.json';
        if (file_exists($cookiesFile)) {
            $cookies = json_decode(file_get_contents($cookiesFile), true, 512, JSON_THROW_ON_ERROR);
            foreach ($cookies as $cookie) {
                $this->getDriver()->manage()->addCookie($cookie);
            }
        }

        $this->getDriver()->get($this->url . 'about-me');
        if (!str_ends_with($this->getDriver()->getCurrentURL(), 'login')) {
            $this->logger->debug("auth is not required'");
            return;
        }

        $this->getDriver()->findElement(WebDriverBy::name('email'))
            ->clear()
            ->sendKeys($this->login);

        $this->getDriver()->findElement(WebDriverBy::name('password'))
            ->clear()
            ->sendKeys($this->password)
            ->submit();

        $this->getDriver()->wait()->until(WebDriverExpectedCondition::urlContains('about-me'));

        $cookies = [];
        foreach ($this->getDriver()->manage()->getCookies() as $cookie) {
            $cookies[] = $cookie->toArray();
        }
        file_put_contents($cookiesFile, json_encode($cookies, JSON_THROW_ON_ERROR));

        $this->logger->debug("auth was processed'");
    }
}
