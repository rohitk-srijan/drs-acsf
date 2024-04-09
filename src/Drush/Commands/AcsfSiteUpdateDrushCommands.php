<?php

namespace Acquia\DrsAcsf\Drush\Commands;

use Acquia\Drupal\RecommendedSettings\Drush\Commands\BaseDrushCommands;

/**
 * A DrsAcsfSiteUpdateCommands drush command file.
 */
class AcsfSiteUpdateDrushCommands extends BaseDrushCommands {

  /**
   * Update current database to reflect the state of the Drupal file system.
   *
   * @command drupal:update
   * @aliases du setup:update
   *
   * @throws \Robo\Exception\TaskException
   */
  public function update(): void {
    $task = $this->taskDrush()
      ->stopOnFail()
      ->drush("updatedb")
      ->drush("deploy:hook");
    $task->run();
  }

}
