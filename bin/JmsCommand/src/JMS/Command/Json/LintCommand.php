<?php
/**
 * LintCommand.php
 *
 * @package JMS Command
 */
namespace JMS\Command\Json;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

/**
 * LintCommand Class
 *
 * @author Joe Sexton <joe@josephmsexton.com>
 * @package JMS Command
 * @version $Id$
 */
class LintCommand extends Command
{
    /**
     * configure
     */
    protected function configure()
    {
        $this->setName('json:lint')
            ->setDescription('Lint a JSON file')
            ->addArgument(
                'infile',
                InputArgument::REQUIRED,
                'JSON file to lint'
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

        $json = file_get_contents($inFile, true);
        if (false === $json) {
            $output->writeln(sprintf('<error>Could not get the contents of %s</error>', $inFile));
            exit(1);
        }

        $parser = new JsonParser();
        $e = $parser->lint($json);
        if ($e) {
            $output->writeln('<error>JSON is not valid</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            exit(1);
        }

        $output->writeln(sprintf('<info>JSON in %s is valid</info>', $inFile));
    }
}