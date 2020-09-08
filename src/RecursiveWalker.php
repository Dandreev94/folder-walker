<?php

namespace Drom\TestTask;

use ErrorException;
use InvalidArgumentException;
use TypeError;

/**
 * Class RecursiveWalker
 * @package Drom\TestTask
 *
 * used PHP ver 7.3
 */
class RecursiveWalker
{
    const DIALOG_LIMIT = 20;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * the var which keep amount of count files
     *
     * @var int
     */
    private $counter = 0;

    /**
     * the var which keep common sum of numbers into count files
     *
     * @var int
     */
    private $sum = 0;

    /**
     * @var int
     */
    private $dialogCounter = 0;

    /**
     * The main method - entry point for app
     */
    public function walk()
    {
        $this->initDir();
        $this->displayMessage("Great! Let's find some counts!");

        $this->lookIntoFolder();
        $this->displayMessage("This folder contains {$this->counter} files.");
        $this->displayMessage("The sum of these files is {$this->sum}.");
    }

    protected function initDir()
    {
        $this->dialogCounter++;
        if ($this->dialogCounter > self::DIALOG_LIMIT) {
            exit("Stop abuse!\n");
        }

        try {
            $this->validateAndSetDir();
        } catch (InvalidArgumentException $e) {
            $this->displayMessage($e->getMessage());
            $this->initDir();
        } catch (ErrorException $e) {
            $this->displayMessage($e->getMessage());
            $this->initDir();
        }
    }

    /**
     * @throws ErrorException
     * @throws InvalidArgumentException
     */
    protected function validateAndSetDir()
    {
        $this->displayMessage("Hey, can you enter absolute path to your dir?");
        $pathToDir = (string) trim(fgets(STDIN));

        if (!$pathToDir) {
            throw new InvalidArgumentException('You forgot enter the path, please enter it!');
        }

        if (!file_exists($pathToDir)) {
            throw new ErrorException('Sorry, this dir does not exist, please enter new path to dir!');
        }

        $this->rootDir = $pathToDir;
    }

    protected function lookIntoFolder()
    {
        $folderItems = array_slice(scandir($this->rootDir), 2);
        foreach ($folderItems as $item) {
            $itemPath = "{$this->rootDir}/{$item}";
            if (is_dir($itemPath)) {
                $this->rootDir = $itemPath;
                $this->lookIntoFolder();
                $this->rootDir = str_replace("/{$item}", '', $this->rootDir);
            }

            if (
                (preg_match('/^count[.]/', $item) || preg_match('/^count$/', $item))
                && is_file($itemPath)
            ) {
                try{
                    $this->processFile($itemPath);
                } catch (TypeError $e) {
                    $this->displayMessage($e->getMessage());
                }

            }
        }
    }

    /**
     * @param string $pathToFile
     * @throws TypeError
     */
    protected function processFile(string $pathToFile)
    {
        $this->counter++;

        $content = trim(file_get_contents($pathToFile));
        if (!is_numeric($content)) {
            throw new TypeError("Content of {$pathToFile} is not number");
        }

        $this->sum += $content;
    }

    /**
     * @param string $msg
     */
    protected function displayMessage(string $msg)
    {
        fwrite(STDOUT, $msg . "\n");
    }
}