<?php

namespace DkanTools\Commands;

use Symfony\Component\Console\Input\InputOption;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class TestCommands extends \Robo\Tasks
{
    /**
     * Initialize test folders and install dependencies for running tests.
     *
     * Initialize test folders and install dependencies for running tests. This
     * command will run composer install, and create an "assets" folder under
     * "dkan/test" for output. Usually this command does not need to be run on
     * its own as all other test commands run it first.
     */
    function testInit()
    {
        if (!file_exists('docroot/profiles/dkan/test/vendor')) {
            $this->io()->section('Installing test dependencies.');
            $this->taskExec('composer install --prefer-source --no-interaction')
                ->dir('docroot/profiles/dkan/test')
                ->run();
        }
        if (!file_exists('docroot/profiles/dkan/test/assets')) {
            $this->io()->section('Creating test subdirectories.');
            $this->_mkdir('docroot/profiles/dkan/test/assets/junit');
        }
    }

    /**
     * Runs DKAN core Behat tests.
     *
     * Runs DKAN core Behat tests. Pass any additional behat options as
     * arguments. For example:
     *
     * dktl test:behat --name="Datastore API"
     *
     * or
     *
     * dktl test:behat features/workflow.feature
     *
     * @param array $args  Array of arguments to create a full Drush command.
     */
    function testBehat(array $args)
    {
        $this->testInit();
        $behatExec = $this->taskExec('bin/behat')
            ->dir('docroot/profiles/dkan/test')
            ->arg('--colors')
            ->arg('--suite=dkan')
            ->arg('--format=pretty')
            ->arg('--out=std')
            ->arg('--format=junit')
            ->arg('--out=assets/junit')
            ->arg('--config=behat.docker.yml');

        foreach ($args as $arg) {
            $behatExec->arg($arg);
        }
        return $behatExec->run();
    }

    function testPhpunit(array $args)
    {
        $this->testInit();
        $phpunitExec = $this->taskExec('bin/phpunit')
            ->dir('docroot/profiles/dkan/test')
            ->arg('--configuration=phpunit');

        foreach ($args as $arg) {
            $phpunitExec->arg($arg);
        }
        return $phpunitExec->run();
    }
}
