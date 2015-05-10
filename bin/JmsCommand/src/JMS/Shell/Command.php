<?php
/**
 * Command.php;
 *
 * @package Environment
 */
namespace JMS\Shell;

/**
 * Command Class
 *
 * @author Joe Sexton <joe.sexton@nerdery.com>
 * @package Environment
 * @version $Id$
 */
class Command
{
    /**
     * @var string
     */
    protected $bin;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * Run command
     *
     * @return string
     * @throws \Exception if command not found
     */
    public function run()
    {
        if (!$this->binExists()) {
            throw new \Exception(sprintf('%s command not found', $this->getCommand()));
        }

        return shell_exec($this->getCommand());
    }

    /**
     * Bin exists on server
     *
     * @return bool
     * @throws \Exception if bin not set
     */
    public function binExists()
    {
        $command = $this->getBin();
        if (!$command) {
            throw new \Exception('Binary not configured, no command to run');
        }

        $stdout = shell_exec("which $command");

        return (empty($stdout) ? false : true);
    }

    /**
     * Get a formatted command using the options and arguments set
     *
     * @return string
     * @throws \Exception if bin not set
     */
    public function getCommand()
    {
        $command = $this->getBin();
        if (!$command) {
            throw new \Exception('Binary not configured, no command to run');
        }

        foreach ($this->getOptions() as $option => $value) {

            $command .= ' '.$option;

            if (null === $value || "" === $value) {
                continue;
            }

            if ('--' === substr($option, 0, 2)) {
                $command .= '="'.$value.'"';
            } else {
                $command .= ' '.$value;
            }
        }

        foreach ($this->getArguments() as $argument) {
            $command .= ' '.$argument;
        }

        return $command;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return Command
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set a wget option
     * Option must be a valid wget option, the class constants provided may be used.
     * Value is an optional argument if the wget option requires a value
     *
     * @param string $option
     * @param string $value
     * @return Command
     */
    public function setOption($option, $value = null)
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * Get option
     * Returns false if the option is not set.  Returns null if the option is set
     * but does not require a value.
     *
     * @param string $option
     * @return string|null|false
     */
    public function getOption($option)
    {
        return array_key_exists($option, $this->options) ? $this->options[$option] : false;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     * @return Command
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Add argument
     *
     * @param string $argument
     */
    public function addArgument($argument)
    {
        $this->arguments[] = $argument;
    }

    /**
     * Get bin
     *
     * @return string
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * Set bin
     *
     * @param string $bin
     * @return Command
     */
    public function setBin($bin)
    {
        $this->bin = $bin;

        return $this;
    }
}