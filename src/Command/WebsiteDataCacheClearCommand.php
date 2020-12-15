<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\WebsiteDataCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WebsiteDataCacheClearCommand.
 *
 * @package App\Command
 */
class WebsiteDataCacheClearCommand extends Command {

  /**
   * The command name.
   *
   * @var string
   */
  protected static $defaultName = 'website-data:clear-cache';

  /**
   * The website data cache.
   *
   * @var \App\Service\WebsiteDataCache
   */
  protected WebsiteDataCache $websiteDataCache;

  /**
   * WebsiteDataCacheClearCommand constructor.
   *
   * @param \App\Services\WebsiteDataCache $websiteDataCache
   *   The website data cache.
   * @param string|null $name
   *   The name of the command. Passing NULL means it must be set in configure().
   */
  public function __construct(WebsiteDataCache $websiteDataCache, string $name = NULL) {
    parent::__construct($name);
    $this->websiteDataCache = $websiteDataCache;
  }

  /**
   * Command configuration.
   */
  protected function configure(): void {
    $this->setDescription('Clears the website data cache.')
      ->setHelp('Clears the website data cache.');
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
    if ($this->websiteDataCache->delete() === TRUE) {
      $output->writeln('Cleared!');
      return Command::SUCCESS;
    }

    $output->writeln('Something went wrong!');
    return Command::FAILURE;
  }

}
