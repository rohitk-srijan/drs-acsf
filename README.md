## About
The acsf-init Drush command (provided as part of the Site Factory Connector module) prepares a custom Drupal distribution for development and deployment on Site Factory. The command appends information to your website’s sites/default/settings.php file, while also creating any necessary directories, identifying database credentials, creating a sites.php file, and copying files required by Site Factory.

Example: ``

### Requirement
Add `installer-paths` in your root composer.json to place this
plugin at `drush/Commands/contrib/drs-acsf`

```
"extra": {
  ...
  "installer-paths": {
    ...
    "drush/Commands/contrib/{$name}": ["type:drupal-drush"]
  }
}
```
