<?php

namespace Acquia\DrsAcsf\Drush\Commands;

use Acquia\Drupal\RecommendedSettings\Drush\Commands\BaseDrushCommands;
use Acquia\Drupal\RecommendedSettings\Exceptions\SettingsException;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Drush\Attributes as Cli;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;

/**
 * A DrsAcsfCommands drush command file.
 */
class AcsfRecipesDrushCommands extends BaseDrushCommands {

  /**
   * Display message when running command drs:acsf:init:all.
   */
  #[CLI\Hook(type: HookManager::INITIALIZE, target: "drs:acsf:init:all")]
  public function initialize(): void {
    $this->logger->notice("This command will initialize support for Acquia Cloud Site Factory by performing the following tasks:");
    $this->logger->notice("  * Executing the `acsf-init` command, provided by the drupal/acsf module.");
    $this->logger->notice("  * Adding default factory-hooks to your application.");
    $this->logger->notice("");
    $this->logger->notice("Note that the default version of PHP on ACSF is generally not the same as Acquia Cloud.");
    $this->logger->notice("You may wish to adjust the PHP version of your local environment and CI tools to match.");
    $this->logger->notice("");
    $this->logger->notice("For more information, see:");
    $this->logger->notice("<comment>https://docs.acquia.com/acquia-cms/developer-experience/using-drupal-recommended-settings-plugin#section-using-drs-with-site-factory</comment>");
  }

  /**
   * Initializes ACSF support for project.
   */
  #[CLI\Command(name: "drs:acsf:init:all", aliases: ["daia"])]
  #[CLI\Help(description: "Initializes ACSF support for project.")]
  public function drsAcsfInitialize(): int {
    try {
      $this->drsAcsfHooksInitialize();
      $this->drsAcsfComposerInitialize();
      $this->drsAcsfDrushInitialize();
    }
    catch (\Exception $e) {
      $this->print($e->getMessage(), "error");
      return ResultData::EXITCODE_ERROR;
    }
    return ResultData::EXITCODE_OK;
  }

  /**
   * Refreshes the ACSF settings and hook files.
   *
   * @throws \Acquia\Drupal\RecommendedSettings\Exceptions\SettingsException|\Robo\Exception\TaskException
   */
  #[CLI\Command(name: "drs:acsf:init:drush", aliases: ["daid"])]
  #[CLI\Help(description: "Refreshes the ACSF settings and hook files.")]
  public function drsAcsfDrushInitialize(): void {
    $this->say('Executing initialization command provided acsf module...');
    $repoRoot = $this->getConfigValue('repo.root');
    $docRoot = $this->getConfigValue('docroot');
    $acsfInclude = $docRoot . '/modules/contrib/acsf/acsf_init';
    $result = $this->taskExecStack()
      ->exec("$repoRoot/vendor/bin/drush acsf-init --include=\"$acsfInclude\" --root=\"$docRoot\" -y")
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
    $composer_filepath = $this->getConfigValue('repo.root') . '/composer.json';
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
   * @throws \Acquia\Drupal\RecommendedSettings\Exceptions\SettingsException
   */
  #[CLI\Command(name: "drs:acsf:init:hooks", aliases: ["daih"])]
  #[CLI\Help(description: "Creates \"factory-hooks/\" directory in project's repo root.")]
  public function drsAcsfHooksInitialize(): void {
    $defaultAcsfHooks = __DIR__ . '/../../../factory-hooks';
    $projectAcsfHooks = $this->getConfigValue('repo.root') . '/factory-hooks';

    $result = $this->taskCopyDir([$defaultAcsfHooks => $projectAcsfHooks])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new SettingsException("Unable to copy ACSF scripts.");
    }

    $this->say('New "factory-hooks/" directory created in repo root. Please commit this to your project.');
  }

}
