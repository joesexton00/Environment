<?php
/**
 * WgetParserCommand
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
 * WgetParserCommand Class
 *
 * @author Joe Sexton <joe.sexton@nerdery.com>
 * @package JMS Command
 * @version $Id$
 */
class WgetParserCommand extends Command
{
    const CACHE_DIR = '/../../../../cache';
    const CACHE_SUBDIR = '/crawler';
    const ARGUMENT_INFILE = 'in-file';
    const ARGUMENT_OUTFILE = 'out-file';

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('wget:parse')
            ->setDescription('Parse the ouput of the wget command')
            ->setHelp( <<<EOT
The <info>wget:parse</info> command parses the output from the wget command to provide more useful information

<info>php app/console wget:parse</info>
EOT
            )
            ->addArgument(
                self::ARGUMENT_INFILE,
                InputArgument::REQUIRED,
                'Specify the wget log file to use as input'
            )
            ->addArgument(
                self::ARGUMENT_OUTFILE,
                InputArgument::REQUIRED,
                'Specify where to save the results'
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
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating cache files');
        $this->warmCache();

        $urls = $this->parseUrls();
        $this->writeToFile($this->getOutFile(), implode("\n", $urls));

        $output->writeln('');
        $output->writeln(sprintf('<info>OK parsing completed successfully - results output file located at %s</info>', realpath($this->getOutFile())));
        $output->writeln('');
    }

    /**
     * Parse wget output for a list of URLs
     *
     * @return array
     */
    protected function parseUrls()
    {
        $urls = [];
        $logHandle = fopen($this->getInFile(), 'r');
        while ($line = fgets($logHandle)) {
            $result = preg_match("/\b(?:(?:https?|ftp):\/\/)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $line, $matches);

            if (!empty($matches)) {
                $urls = array_merge($urls, $matches);
            }
        }
        fclose($logHandle);

        $urls = array_unique($urls);
        asort($urls);
        $urls = array_values($urls);

        return $urls;
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

        $this->warmCacheFile($this->getOutFile());
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
     * Get in file
     *
     * @return string
     */
    public function getInFile()
    {
        return $this->getInput()->getArgument(self::ARGUMENT_INFILE);

    }

    /**
     * Get out file
     *
     * @return string
     */
    public function getOutFile()
    {
        return $this->getInput()->getArgument(self::ARGUMENT_OUTFILE);

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
}