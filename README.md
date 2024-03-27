## About
This plugin is specially designed for the automated generation of settings files needed for running code in an ACSF environment.

```composer require acquia/drs_acsf```

### Commands
The acsf-init Drush command (provided as part of the Site Factory Connector module) prepares a custom Drupal distribution for development and deployment on Site Factory. The command appends information to your website’s sites/default/settings.php file, while also creating any necessary directories, identifying database credentials, creating a sites.php file, and copying files required by Site Factory.

#### ./vendor/bin/drush drs:acsf:init:all
Above command will initialize support for Acquia Cloud Site Factory by performing the following tasks:
- Executing the `acsf-init` command, provided by the drupal/acsf module.
- Adding default factory-hooks to your application.
#### ./vendor/bin/drush drs:acsf:init:drush
- Executing initialization command provided acsf module
#### ./vendor/bin/drush validate:acsf 
Above command will validate all required settings are in place. 


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
