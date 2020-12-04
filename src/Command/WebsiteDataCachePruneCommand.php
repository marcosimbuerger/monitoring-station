<?php

namespace App\Command;

use App\Service\WebsiteDataCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WebsiteDataCachePruneCommand.
 *
 * @package App\Command
 */
class WebsiteDataCachePruneCommand extends Command {

  /**
   * The command name.
   *
   * @var string
   */
  protected static $defaultName = 'website-data:prune-cache';

  /**
   * The website data cache.
   *
   * @var \App\Services\WebsiteDataCache
   */
  protected $websiteDataCache;

  /**
   * WebsiteDataCachePruneCommand constructor.
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
    $this->setDescription('Prune the website data cache.')
      ->setHelp('The FilesystemAdapter cache pool does not include an automated mechanism for pruning expired cache items. Use this command for manual removal of stale cache items.');
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
    if ($this->websiteDataCache->prune() === TRUE) {
      $output->writeln('Pruned!');
      return Command::SUCCESS;
    }

    $output->writeln('Something went wrong!');
    return Command::FAILURE;
  }

}
