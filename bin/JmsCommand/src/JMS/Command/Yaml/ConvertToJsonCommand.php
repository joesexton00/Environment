<?php
/**
 * ConvertToJsonCommand.php
 *
 * @package JMS Command
 */
namespace JMS\Command\Yaml;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * ConvertToJsonCommand Class
 *
 * @author Joe Sexton <joe@josephmsexton.com>
 * @package JMS Command
 * @version $Id$
 */
class ConvertToJsonCommand extends Command
{
    /**
     * configure
     */
    protected function configure()
    {
        $this->setName('yaml:to:json')
            ->setDescription('Convert a YAML file to JSON')
            ->addArgument(
                'infile',
                InputArgument::REQUIRED,
                'YAML file to convert to JSON'
            )
            ->addOption(
                'outfile',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output file to save converted JSON'
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

        $yaml = file_get_contents($inFile, true);
        if (false === $yaml) {
            $output->writeln(sprintf('<error>Could not get the contents of %s</error>', $inFile));
            exit(1);
        }

        try {
            $parser = new Parser();
            $data   = $parser->parse($yaml);
        } catch (ParseException $e) {
            $output->writeln(sprintf('<error>Could not parse contents of %s into YAML</error>', $inFile));
            exit(1);
        }

        $json = json_encode($data);
        if (!$json) {
            $output->writeln(sprintf('<error>Could not convert YAML content in %s to JSON</error>', $inFile));
            exit(1);
        }

        if (false === file_put_contents($outFile, $json)) {
            $output->writeln(sprintf('<error>Could not write JSON to %s</error>', $outFile));
            exit(1);
        }

        $successMessage = sprintf('Converted YAML in %s to JSON', $inFile);
        if ($inFile !== $outFile) {
            $successMessage .= sprintf(' and saved to %s', $outFile);
        }

        $output->writeln(sprintf('<info>%s</info>', $successMessage));
    }
}