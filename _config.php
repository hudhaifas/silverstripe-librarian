<?php

/**
 * Fetches the name of the current module folder name.
 *
 * @return string
 */
if (!defined('LIBRARIAN_DIR')) {
    define('LIBRARIAN_DIR', ltrim(Director::makeRelative(realpath(__DIR__)), DIRECTORY_SEPARATOR));
}