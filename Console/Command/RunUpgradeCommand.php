<?php

namespace Team23\SetupModule\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Team23\SetupModule\Setup\RunUpgrade;

/**
 * Class RunUpgradeCommand
 * @package Team23\SetupModule\Console\Command
 */
class RunUpgradeCommand extends Command
{
    /** @var RunUpgrade */
    protected $runUpgrade;

    /**
     * RunUpgradeCommand constructor.
     * @param RunUpgrade $runUpgrade
     */
    public function __construct(
        RunUpgrade $runUpgrade
    ) {
        $this->runUpgrade = $runUpgrade;
        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('setupmodule:runupgrade')
            ->setDescription('Run SetupModule upgrade')
            ->setDefinition([
                new InputOption(
                    'setup_version',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Please enter version (eg. --setup_version "1.0.4")'
                )
            ]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runUpgrade->run($input->getOption('setup_version'));
    }
}
