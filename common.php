<?php

/**
 * Returns a random temporary file name.
 *
 * @return string
 */
function getTemporaryFileName()
{
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
}

/**
 * Runs a survey on a PHP project directory.
 */
class Surveyor
{
    /**
     * The patterns to look for.
     *
     * S : a string identifier, such as a function name or 'array', optionally surrounded by spaces or tabs.
     * n : a new line, immediatly followed by an indentation of one or more spaces or tabs.
     *
     * @var array
     */
    private $patterns = array(
        'S(S(n',
        'S(nS(n'
    );

    /**
     * The translations from patterns elements to regular expressions.
     * All patterns are case insensitive.
     *
     * @var array
     */
    private $regexPatternElements = array(
        'S' => '[ \t]*[a-z][a-z0-9_]+[ \t]*',
        'n' => '[\r\n][ \t]+'
    );

    /**
     * The regular expression patterns. Built at construction time.
     * Keys are the patterns, values are the regular expressions.
     *
     * @var array
     */
    private $regexPatterns = array();

    /**
     * The occurrence count for each pattern.
     * Keys are the patterns, values are the number of occurrences of the pattern.
     *
     * @var array
     */
    private $patternCounters = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->buildRegexPatterns();
    }

    /**
     * Analyzes each PHP file in the given directory, and returns the result.
     * Keys are the patterns, values are the number of occurrences of the pattern.
     *
     * @param string $dir
     * @return array
     */
    public function survey($dir)
    {
        $this->resetPatternCount();
        $this->browse($dir);

        return $this->patternCounters;
    }

    /**
     * Recursively browses the given directory to look for patterns in PHP files.
     *
     * @param string $dir The path of the directory to browse.
     * @return void
     */
    private function browse($dir)
    {
        $iterator = new \DirectoryIterator($dir);

        foreach ($iterator as $file) {
            /** @var \DirectoryIterator $file */
            if (! $file->isDot()) {
                $path = $dir . DIRECTORY_SEPARATOR . $file->getFilename();

                if ($file->isDir()) {
                    $this->browse($path);
                } elseif ($file->isFile()) {
                    if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) == 'php') {
                        $this->analyze($path);
                    }
                }
            }
        }
    }

    /**
     * Analyzes a file to look for patterns, and prints the result.
     *
     * @param string $file The path of the file to analyze.
     * @return void
     */
    private function analyze($file)
    {
        $data = file_get_contents($file);

        foreach ($this->regexPatterns as $pattern => $regexp) {
            preg_match_all($regexp, $data, $matches, PREG_SET_ORDER);
            $this->patternCounters[$pattern] += count($matches);
        }
    }

    /**
     * Resets the pattern counters. Must be called before iterating over a new directory.
     *
     * @return void
     */
    private function resetPatternCount()
    {
        foreach ($this->patterns as $pattern) {
            $this->patternCounters[$pattern] = 0;
        }
    }

    /**
     * Builds the regular expressions from the patterns.
     *
     * @return void
     */
    private function buildRegexPatterns()
    {
        foreach ($this->patterns as $pattern) {
            $regex = '/' . preg_quote($pattern) . '/i';

            foreach ($this->regexPatternElements as $element => $regexElement) {
                $regex = str_replace($element, $regexElement, $regex);
            }

            $this->regexPatterns[$pattern] = $regex;
        }
    }
}
