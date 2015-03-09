<?php
/**
 * ConvertToYmlCommand.php
 *
 * @package JMS Command
 */
namespace JMS\Command\Json;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\DumpException;

/**
 * ConvertToYmlCommand Class
 *
 * @author Joe Sexton <joe@josephmsexton.com>
 * @package JMS Command
 * @version $Id$
 */
class ConvertToYmlCommand extends Command
{
    /**
     * configure
     */
    protected function configure()
    {
        $this->setName('json:to:yml')
            ->setDescription('Convert a JSON file to YAML')
            ->addArgument(
                'infile',
                InputArgument::REQUIRED,
                'JSON file to convert to YAML'
            )
            ->addOption(
                'outfile',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output file to save converted YAML'
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

        try {
            $dumper = new Dumper();
            $yaml   = $dumper->dump( $data, 100 );
        } catch (DumpException $e) {
            $output->writeln(sprintf('<error>Could not convert JSON content in %s to YAML</error>', $inFile));
            exit(1);
        }
        if (!$yaml) {
            $output->writeln(sprintf('<error>Could not convert JSON content in %s to YAML</error>', $inFile));
            exit(1);
        }

        if (false === file_put_contents($outFile, $yaml)) {
            $output->writeln(sprintf('<error>Could not write YAML to %s</error>', $outFile));
            exit(1);
        }

        $successMessage = sprintf('Converted JSON in %s to YAML', $inFile);
        if ($inFile !== $outFile) {
            $successMessage .= sprintf(' and saved to %s', $outFile);
        }

        $output->writeln(sprintf('<info>%s</info>', $successMessage));
    }
}