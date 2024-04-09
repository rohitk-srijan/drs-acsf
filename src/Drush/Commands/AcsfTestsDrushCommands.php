<?php

namespace Acquia\DrsAcsf\Drush\Commands;

use Acquia\Drupal\RecommendedSettings\Drush\Commands\BaseDrushCommands;
use Acquia\Drupal\RecommendedSettings\Exceptions\SettingsException;

/**
 * A DrsAcsfTestsCommand drush command file.
 */
class AcsfTestsDrushCommands extends BaseDrushCommands {

  /**
   * Executes the acsf-init-validate command.
   *
   * @command validate:acsf
   *
   * @throws \Acquia\Drupal\RecommendedSettings\Exceptions\SettingsException
   */
  public function validateAcsf(): void {
    $this->say("Validating ACSF settings...");
    $task = $this->taskDrush()
      ->stopOnFail()
      ->drush("acsf-init-verify --include=" . DRUPAL_ROOT . "/modules/contrib/acsf/acsf_init");
    $result = $task->run();
    if (!$result->wasSuccessful()) {
      throw new SettingsException("Failed to verify ACSF settings. Re-run drs:acsf:init:all and commit the results.");
    }
  }

}
