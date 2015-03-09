<?php
/**
 * LintCommand.php
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
        $this->setName('yaml:lint')
            ->setDescription('Lint a YAML file')
            ->addArgument(
                'infile',
                InputArgument::REQUIRED,
                'YAML file to lint'
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

        $yaml = file_get_contents($inFile, true);
        if (false === $yaml) {
            $output->writeln(sprintf('<error>Could not get the contents of %s</error>', $inFile));
            exit(1);
        }

        try {
            $parser = new Parser();
            $parser->parse($yaml);
        } catch (ParseException $e) {
            $output->writeln('<error>YAML is not valid</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            exit(1);
        }

        $output->writeln(sprintf('<info>YAML in %s is valid</info>', $inFile));
    }
}