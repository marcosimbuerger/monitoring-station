<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WebsiteDataFetcher.
 *
 * @package App\Command
 */
class WebsiteDataFetcher extends Command {

  /**
   * The command name.
   *
   * @var string
   */
  protected static $defaultName = 'app:fetch-website-data';

  /**
   * Command configuration.
   */
  protected function configure(): void {
    $this->setDescription('Fetches the website data.')
      ->setHelp('Fetches the website data for the websites, which are configured in the websites config file.');
  }

  /**
   * Execute.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return int
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {

    // TODO: Call fetch service.

    return Command::SUCCESS;
  }

}
