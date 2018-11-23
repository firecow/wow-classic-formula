<?
declare(strict_types=1);

namespace App;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

class Context
{

    private $twig;
    private $config;
    private $random;
    private $localizedTexts;
    private $passwordHasher;

    public function __construct()
    {
        $this->random = new Random();
        $this->config = new Config();
        $this->passwordHasher = new PasswordHasher();

        $localizedTexts = new LocalizedTexts();
        $this->localizedTexts = $localizedTexts;
        $loader = new FilesystemLoader("/");
        $this->twig = new Environment($loader);
        $this->twig->addFilter(new TwigFilter('localize', function (string $localizationKey, ...$args) use ($localizedTexts) {
            return $localizedTexts->getText($localizationKey, ...$args);
        }));
    }

    protected function getConfig(): Config
    {
        return $this->config;
    }

    public function getJWTSecret(): string
    {
        return $this->getConfig()->getJWTSecret();
    }

    public function getPasswordHasher(): PasswordHasher
    {
        return $this->passwordHasher;
    }

    public function render(string $name, array $ctx = []): string
    {
        return $this->twig->render($name, $ctx);
    }

    public function createLogger(string $facility): Logger
    {
        $handler = new StreamHandler('php://stdout');
        return new Logger($facility, [$handler]);
    }

    public function createSQL(): SQL
    {
        $dataSourceName = $this->getConfig()->getPDODataSourceName();
        $username = $this->getConfig()->getPDOUsername();
        $password = $this->getConfig()->getPDOPassword();
        return new SQL($dataSourceName, $username, $password);
    }

    public function getLocalizedTexts(): LocalizedTexts
    {
        return $this->localizedTexts;
    }

    public function getLanguageCode(): string
    {
        return "eng";
    }

    public function getRandom(): Random
    {
        return $this->random;
    }

}