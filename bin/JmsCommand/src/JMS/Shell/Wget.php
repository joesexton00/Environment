<?php
/**
 * Wget.php;
 *
 * @package Environment
 */
namespace JMS\Shell;

/**
 * Wget Class
 *
 * @author Joe Sexton <joe.sexton@nerdery.com>
 * @package Environment
 * @version $Id$
 */
class Wget extends Command
{
    /** @see http://www.gnu.org/software/wget/manual/wget.html */
    const DOMAINS = '--domains';
    const EXECUTE = '-e';
    const INPUT_FILE = '--input-file';
    const LEVEL = '--level';
    const LOAD_COOKIES = '--load-cookies';
    const OUTPUT_FILE = '--output-file';
    const PAGE_REQUISITES = '--page-requisites';
    const POST_DATA = '--post-data';
    const RECURSIVE = '--recursive';
    const SAVE_COOKIES = '--save-cookies';
    const SPAN_HOSTS = '--span-hosts';
    const SPIDER = '--spider';

    protected $bin = 'wget';

    /**
     * {@inheritdoc}
     */
    public function getCommand()
    {
        if (!filter_var($this->getUrl(), FILTER_VALIDATE_URL) && false === $this->getOption(self::INPUT_FILE)) {
            throw new \LogicException('A URL or input file must be provided.');
        }

        return parent::getCommand();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return array_pop($this->getArguments());
    }

    /**
     * @param string $url
     * @return Wget
     */
    public function setUrl($url)
    {
        $this->setArguments([$url]);

        return $this;
    }
}