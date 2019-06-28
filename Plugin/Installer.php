<?php

namespace Team23\SetupModule\Plugin;

class Installer
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    public function __construct(
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $this->output = $output;
    }

    public function beforeInstallSchema(array $request)
    {
        $this->output->writeln('demo');
        var_dump('demovardump');

        return $request;
    }
}