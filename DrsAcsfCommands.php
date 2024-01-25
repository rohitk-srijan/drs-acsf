<?php

namespace Drush\Commands\drs_acsf;

use Acquia\Drupal\RecommendedSettings\Drush\Commands\BaseDrushCommands;
use Acquia\Drupal\RecommendedSettings\Exceptions\SettingsException;
use Acquia\Drupal\RecommendedSettings\Helpers\EnvironmentDetector;
use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * A DrsAcsfCommands drush command file.
 */
class DrsAcsfCommands extends BaseDrushCommands {

  /**
   * Display message when running command drs:acsf:init:all.
   */
  #[CLI\Hook(type: HookManager::INITIALIZE, target: "drs:acsf:init:all")]
  public function init(InputInterface $input, AnnotationData $annotationData): void {
    $this->logger->notice("This command will initialize support for Acquia Cloud Site Factory by performing the following tasks:");
    $this->logger->notice("  * Executing the `acsf-init` command, provided by the drupal/acsf module.");
    $this->logger->notice("  * Adding default factory-hooks to your application.");
    $this->logger->notice("");
    $this->logger->notice("Note that the default version of PHP on ACSF is generally not the same as Acquia Cloud.");
    $this->logger->notice("You may wish to adjust the PHP version of your local environment and CI tools to match.");
    $this->logger->notice("");
    $this->logger->notice("For more information, see:");
    $this->logger->notice("<comment>https://docs.acquia.com/drs/tech-architect/acsf-setup/</comment>");
  }

  /**
   * Initializes ACSF support for project.
   *
   * @command drs:acsf:init:all
   *
   * @aliases daia
   */
  public function drsAcsfInitialize(): void {
    $this->drsAcsfHooksInitialize();
    $this->drsAcsfComposerInitialize();
    $this->drsAcsfDrushInitialize();
  }

  /**
   * Refreshes the ACSF settings and hook files.
   *
   * @command drs:acsf:init:drush
   *
   * @aliases daid
   */
  public function drsAcsfDrushInitialize(): void {
    $this->say('Executing initialization command provided acsf module...');
    $acsfInclude = DRUPAL_ROOT . '/modules/contrib/acsf/acsf_init';
    $result = $this->taskExecStack()
      ->exec(EnvironmentDetector::getRepoRoot() . "/vendor/bin/drush acsf-init --include=\"$acsfInclude\" --root=\"" . DRUPAL_ROOT . "\" -y")
      ->run();

    if (!$result->wasSuccessful()) {
      throw new SettingsException("Unable to copy ACSF scripts.");
    }

  }

  /**
   * Ensure that ACSF-modified assets don't get overridden.
   */
  public function drsAcsfComposerInitialize(): void {
    // .htaccess will be patched, excluding from further updates.
    $composer_filepath = EnvironmentDetector::getRepoRoot() . '/composer.json';
    $composer_contents = json_decode(file_get_contents($composer_filepath));
    // Drupal Scaffold version (deprecate in Drupal 8.8, remove in Drupal 9).
    if (!property_exists($composer_contents->extra->{'drupal-scaffold'}, 'excludes') || !in_array('.htaccess', $composer_contents->extra->{'drupal-scaffold'}->excludes)) {
      $composer_contents->extra->{'drupal-scaffold'}->excludes[] = '.htaccess';
    }
    // Composer Scaffold version (supported as of Drupal 8.8).
    if (!property_exists($composer_contents->extra->{'drupal-scaffold'}, 'file-mapping') || !property_exists($composer_contents->extra->{'drupal-scaffold'}->{'file-mapping'}, '[web-root]/.htaccess')) {
      $composer_contents->extra->{'drupal-scaffold'}->{'file-mapping'} = new \stdClass();
      $composer_contents->extra->{'drupal-scaffold'}->{'file-mapping'}->{'[web-root]/.htaccess'} = FALSE;
      $composer_contents->extra->{'drupal-scaffold'}->{'gitignore'} = FALSE;
    }
    file_put_contents($composer_filepath, json_encode($composer_contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  }

  /**
   * Creates "factory-hooks/" directory in project's repo root.
   *
   * @command drs:acsf:init:hooks
   * @aliases daih
   */
  public function drsAcsfHooksInitialize(): void {
    $defaultAcsfHooks = __DIR__ . '/factory-hooks';
    $projectAcsfHooks = EnvironmentDetector::getRepoRoot() . '/factory-hooks';

    $result = $this->taskCopyDir([$defaultAcsfHooks => $projectAcsfHooks])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new SettingsException("Unable to copy ACSF scripts.");
    }

    $this->say('New "factory-hooks/" directory created in repo root. Please commit this to your project.');
  }

}
