<?php
/**
 * CrawlCommand
 *
 * @package JMS Command
 */
namespace JMS\Command\Crawl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use JMS\Shell\Wget;

/**
 * CrawlCommand Class
 *
 * @author Joe Sexton <joe.sexton@nerdery.com>
 * @package JMS Command
 * @version $Id$
 */
class CrawlCommand extends Command
{
    const CACHE_DIR = '/../../../../cache';
    const CACHE_SUBDIR = '/crawler';
    const IN_FILE = 'wget.in';
    const LOG_FILE = 'wget.out';
    const COOKIE_FILE = 'cookie.txt';
    const ARGUMENT_URL = 'URL';
    const OPTION_REPORT_FILE = 'report-file';
    const OPTION_AUTH_URL = 'auth-url';
    const OPTION_AUTH_POST_DATA = 'auth-post-data';

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Wget
     */
    protected $wgetCommand;

    /**
     * @var array
     */
    protected $seedUrls = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('crawl')
            ->setDescription('Crawl a site')
            ->setHelp( <<<EOT
The <info>crawl</info> command crawls a site and can gather a list of pages, broken links, and outbound links

<info>php app/console crawl</info>
EOT
            )
            ->addArgument(
                self::ARGUMENT_URL,
                InputArgument::REQUIRED,
                'Set seed URL to crawl, specify multiple URLs using a comma-separated list'
            )
            ->addOption(
                self::OPTION_REPORT_FILE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify the report file location, default is crawl_report.txt'
            )
            ->addOption(
                self::OPTION_AUTH_URL,
                null,
                InputOption::VALUE_OPTIONAL,
                sprintf('Specify URL to authenticate on if authentication is required"',
                        self::OPTION_AUTH_URL)
            )
            ->addOption(
                self::OPTION_AUTH_POST_DATA,
                null,
                InputOption::VALUE_OPTIONAL,
                sprintf('Specify POST data to use to authenticate with if authentication is required. ex --%s="user=foo&password=bar" If this option is specified, the --%s option must also be used.',
                        self::OPTION_AUTH_POST_DATA, self::OPTION_AUTH_URL)
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->setInput($input);
        $this->setOutput($output);
        $this->setWgetCommand(new Wget());
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating cache and log files');
        $this->warmCache();
        $this->createSeedFile();

        // authenticate
        $this->authenticate();

        // crawl using wget and save to cache file
        $output->writeln(sprintf('Crawling %s', implode(', ', $this->seedUrls)));

        $this->getWgetCommand()
            ->setOption(Wget::INPUT_FILE, $this->getInFile())
            ->setOption(Wget::OUTPUT_FILE, $this->getLogFile())
            ->setOption(Wget::RECURSIVE)
            ->setOption(Wget::LEVEL, 'inf')
            ->setOption(Wget::SPIDER)
            // span hosts
            // domains
            ->run();
            ;
/*
 wget
--mirror
--convert-links
--span-hosts
--adjust-extension
--page-requisites
--execute robots=off
--restrict-file-names=windows
--output-file=wget.log
--input-file=wget.in
--content-disposition
--domains=responsibility-project.libertymutual.com,projetoresponsabilidade.com.br,proyectoresponsabilidad.com.ve,proyectoresponsabilidad.com.ve,proyectoresponsabilidad.co,proyectoresponsabilidad.cl,proresponsabilidad.com.ar,cdn-liberty.yottaa.net
 */

        // parse wget logs to find links, 404s
        // crawl each page to find outbound links
        // create report

        $this->cleanUp();
    }

    /**
     * Add seed URLs to the in file
     *
     * @throws \LogicException
     */
    public function createSeedFile()
    {
        $urls = explode(',', $this->getInput()->getArgument(self::ARGUMENT_URL));

        $urlString = "";

        foreach ($urls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new \LogicException('Invalid URL passed, URL argument should be a valid URL or comma-separated list of URLs.');
            }

            $this->seedUrls[] = $url;
            $urlString .= $url.PHP_EOL;
        }

        $this->writeToFile($this->getInFile(), $urlString);
    }

    /**
     * Post crawling clean up
     */
    public function cleanUp()
    {
//        $this->clearCacheFile($this->getInFile());
//        $this->clearCacheFile($this->getLogFile());
        $this->clearCacheFile($this->getCookieFile());
    }

    /**
     * Warm cache
     */
    protected function warmCache()
    {
        if (!is_dir(__DIR__.self::CACHE_DIR)) {
            mkdir(__DIR__.self::CACHE_DIR);
        }

        if (!is_dir(__DIR__.self::CACHE_DIR.self::CACHE_SUBDIR)) {
            mkdir(__DIR__.self::CACHE_DIR.self::CACHE_SUBDIR);
        }

        $this->warmCacheFile($this->getInFile());
        $this->warmCacheFile($this->getLogFile());
        $this->warmCacheFile($this->getCookieFile());
    }

    /**
     * Warm cache file
     *
     * @param string $file
     */
    protected function warmCacheFile($file)
    {
        $this->clearCacheFile($file);
        touch($file);
    }

    /**
     * Clear cache file
     *
     * @param string $file
     */
    protected function clearCacheFile($file)
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Write data to file
     *
     * @param string $file
     * @param string $data
     * @throws \Exception
     */
    protected function writeToFile($file, $data)
    {
        $handle = fopen($file, 'a');
        if (false === $handle) {
            throw new \Exception(sprintf('Error opening file %s', $file));
        }

        fwrite($handle, $data);

        $closed = fclose($handle);
        if (false === $closed) {
            throw new \Exception(sprintf('Error closing file %s', $file));
        }
    }

    /**
     * Authenticate with the auth URL passed as an option
     */
    protected function authenticate()
    {
        if (!$this->getAuthUrl()) {
            return;
        }

        $this->getOutput()->writeln(sprintf('Authenticating at %s', $this->getAuthUrl()));

        $command = new Wget();
        $command->setUrl($this->getAuthUrl());
        $command->setOption(Wget::SAVE_COOKIES, $this->getCookieFile());
        $command->setOption(Wget::OUTPUT_FILE, $this->getLogFile());

        if ($this->getAuthPostData()) {
            $command->setOption(Wget::POST_DATA, $this->getAuthPostData());
        }

        $command->run();

        // Use cookie file for subsequent requests
        $this->getWgetCommand()->setOption(Wget::LOAD_COOKIES, $this->getCookieFile());

        $this->getOutput()->writeln('Authentication complete, cookie saved');
    }

    /**
     * Get auth URL
     *
     * @return null|string
     */
    public function getAuthUrl()
    {
        return $this->getInput()->getOption(self::OPTION_AUTH_URL);
    }

    /**
     * Get auth post data
     *
     * @return null|string
     */
    public function getAuthPostData()
    {
        return $this->getInput()->getOption(self::OPTION_AUTH_POST_DATA);
    }

    /**
     * Get cookie file
     *
     * @return string
     */
    public function getCookieFile()
    {
        return __DIR__.self::CACHE_DIR.self::CACHE_SUBDIR.'/'.self::COOKIE_FILE;
    }

    /**
     * Get in file
     *
     * @return string
     */
    public function getInFile()
    {
        return __DIR__.self::CACHE_DIR.self::CACHE_SUBDIR.'/'.self::IN_FILE;
    }

    /**
     * Get log file
     *
     * @return string
     */
    public function getLogFile()
    {
        return __DIR__.self::CACHE_DIR.self::CACHE_SUBDIR.'/'.self::LOG_FILE;
    }

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param InputInterface $input
     * @return CrawlCommand
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     * @return CrawlCommand
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return Wget
     */
    public function getWgetCommand()
    {
        return $this->wgetCommand;
    }

    /**
     * @param Wget $wgetCommand
     * @return CrawlCommand
     */
    public function setWgetCommand($wgetCommand)
    {
        $this->wgetCommand = $wgetCommand;

        return $this;
    }

}