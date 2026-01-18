<?php

/**
 * Smarty compiler exception class
 *
 * @package Smarty
 */
class SmartyCompilerException extends SmartyException
{
    /**
     * @return string
     */
    public function __toString()
    {
        return ' --> Smarty Compiler: ' . $this->message . ' <-- ';
    }

    // Note: $line is inherited from Exception class (PHP 8.x compatibility)
    // The line number of the template error is stored in the inherited $line property

    /**
     * Set the template line number (PHP 8.x compatibility)
     * @param int $line
     */
    public function setTemplateLine($line)
    {
        $this->line = (int)$line;
    }

    /**
     * The template source snippet relating to the error
     *
     * @type string|null
     */
    public $source = null;

    /**
     * The raw text of the error message
     *
     * @type string|null
     */
    public $desc = null;

    /**
     * The resource identifier or template name
     *
     * @type string|null
     */
    public $template = null;
}
