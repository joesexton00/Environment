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
     */
    public function run()
    {
        return shell_exec($this->getCommand());
    }

    /**
     * Get a formatted command using the options and arguments set
     *
     * @return string
     */
    public function getCommand()
    {
        $command = 'wget';
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
}