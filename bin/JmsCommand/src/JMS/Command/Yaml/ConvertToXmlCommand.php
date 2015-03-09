<?php
/**
 * ConvertToXmlCommand.php
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
 * ConvertToXmlCommand Class
 *
 * @author Joe Sexton <joe@josephmsexton.com>
 * @package JMS Command
 * @version $Id$
 */
class ConvertToXmlCommand extends Command
{
    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    /**
     * configure
     */
    protected function configure()
    {
        $this->setName('yaml:to:xml')
            ->setDescription('Convert a YAML file to XML')
            ->addArgument(
                'infile',
                InputArgument::REQUIRED,
                'YAML file to convert to XML'
            )
            ->addOption(
                'outfile',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output file to save converted XML'
            )
            ->addOption(
                'root-node',
                null,
                InputOption::VALUE_OPTIONAL,
                'Name of the root node element',
                'root'
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

        $rootNode = $input->getOption('root-node');

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

        $this->xml = new \SimpleXMLElement(sprintf("<?xml version=\"1.0\"?><%s></%s>", $rootNode, $rootNode));
        $this->arrayToXml($data);
        $xml = $this->xml->asXML();
        if (!$xml) {
            $output->writeln(sprintf('<error>Could not convert YAML content in %s to XML</error>', $inFile));
            exit(1);
        }

        if (false === file_put_contents($outFile, $xml)) {
            $output->writeln(sprintf('<error>Could not write XML to %s</error>', $outFile));
            exit(1);
        }

        $successMessage = sprintf('Converted YAML in %s to XML', $inFile);
        if ($inFile !== $outFile) {
            $successMessage .= sprintf(' and saved to %s', $outFile);
        }

        $output->writeln(sprintf('<info>%s</info>', $successMessage));
    }

    protected function arrayToXml($data) {
        foreach($data as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $this->xml->addChild("$key");
                    $this->arrayToXml($value, $subnode);
                }
                else{
                    $subnode = $this->xml->addChild("item$key");
                    $this->arrayToXml($value, $subnode);
                }
            }
            else {
                $this->xml->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }
}