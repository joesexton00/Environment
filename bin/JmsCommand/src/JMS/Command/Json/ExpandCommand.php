<?php
/**
 * ExpandCommand.php
 *
 * @package JMS Command
 */
namespace JMS\Command\Json;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * JsonExpandCommand Class
 *
 * @author Joe Sexton <joe@josephmsexton.com>
 * @package JMS Command
 * @version $Id$
 */
class ExpandCommand extends Command
{
    /**
     * configure
     */
    protected function configure()
    {
        $this->setName('json:expand')
            ->setDescription('Expand a minified JSON file to a human readable form')
            ->addArgument(
                'infile',
                InputArgument::REQUIRED,
                'JSON file to expand'
            )
            ->addOption(
                'outfile',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output file to save expanded JSON'
            );
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inFile  = $input->getArgument('infile');
        $outFile = $input->getOption('outfile');
        if (!$outFile) {
            $outFile = $inFile;
        }

        $json = file_get_contents($inFile, true);
        if (false === $json) {
            $output->writeln(sprintf('<error>Could not get the contents of %s</error>', $inFile));
            exit(1);
        }

        $data = json_decode($json, true);
        if (is_null($data)) {
            $output->writeln(sprintf('<error>Could not parse contents of %s into JSON</error>', $inFile));
            exit(1);
        }

        $expandedJson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (false === $expandedJson) {
            $output->writeln(sprintf('<error>Could not expand JSON content in %s</error>', $inFile));
            exit(1);
        }

        if (false === file_put_contents($outFile, $expandedJson)) {
            $output->writeln(sprintf('<error>Could not write expanded JSON to %s</error>', $outFile));
            exit(1);
        }

        $successMessage = sprintf('Expanded JSON in %s', $inFile);
        if ($inFile !== $outFile) {
            $successMessage .= sprintf(' and saved to %s', $outFile);
        }

        $output->writeln(sprintf('<info>%s</info>', $successMessage));
    }
}