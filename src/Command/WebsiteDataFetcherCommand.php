<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\WebsiteDataFetcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WebsiteDataFetcherCommand.
 *
 * @package App\Command
 */
class WebsiteDataFetcherCommand extends Command {

  /**
   * The command name.
   *
   * @var string
   */
  protected static $defaultName = 'website-data:fetch';

  /**
   * The website data fetcher.
   *
   * @var \App\Service\WebsiteDataFetcher
   */
  protected WebsiteDataFetcher $websiteDataFetcher;

  /**
   * WebsiteDataFetcherCommand constructor.
   *
   * @param \App\Services\WebsiteDataFetcher $websiteDataFetcher
   *   The website data fetcher.
   * @param string|null $name
   *   The name of the command. Passing NULL means it must be set in configure().
   */
  public function __construct(WebsiteDataFetcher $websiteDataFetcher, string $name = NULL) {
    parent::__construct($name);
    $this->websiteDataFetcher = $websiteDataFetcher;
  }

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
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output.
   *
   * @return int
   *   The exit status code.
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->websiteDataFetcher->fetch();
    $output->writeln('Finished!');
    return Command::SUCCESS;
  }

}
