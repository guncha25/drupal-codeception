# Drupal Codeception
## Overview
Sets of codeception modules and utilities to test drupal cms.
Includes:
- Drupal Bootstrap
- Drupal Entity
- Drupal User
- Drupal Watchdog
- Drupal Drush

## Drupal Bootstrap

Provides full bootstrapping in to Drupal before test. Allows using drupal API in test cases.

### Configuration
- root: Drupal root. Defaults to codeception root + `/web`.
- server: Server and execution environment information.

```
modules:
    - DrupalBootstrap:
        root: './web'
        server:
            SERVER_PORT: null
            REQUEST_URI: '/'
            REMOTE_ADDR: '127.0.0.1'
            HTTP_HOST: 'site.multi'
```

## Drupal Drush

Provides drush (`runDrush`) command.

### Configuration
- working_directory: Working directory where drush should be executed. Defaults to codeception root.
- drush: Drush executable. Defaults to `drush`.
```
modules:
    - DrupalDrush:
        working_directory: './web'
        drush: './vendor/bin/drush'
```

### Usage

Run drush config import and store output.

`$output = $i->runDrush('cim -y');`

Get one-time login url.

`$uri = $i->getLoginUri('userName');`

## Drupal Entity

Provides better interaction with drupal entities and test entity cleanup service.

### Configuration
- cleanup_test: Indicator if test entities should be deleted after each test.
- cleanup_failed: Indicator if test entities should be deleted after test fails.
- cleanup_suite: Indicator if test entities should be deleted after suite.
- route_entities: Entity list that can be retrieved by url.
```
modules:
    - DrupalEntity:
        cleanup_test: true
        cleanup_failed: false
        cleanup_suite: true
        route_entities:
            - node
            - taxonomy_term
```

### Usage

Create entities.

`$node = $i->createEntity(['title => 'My node', 'type' => 'page']);`

`$term = $i->createEntity(['name => 'My term', 'vid' => 'tag'], 'taxonomy_term');`

Delete all stored test entities.

`$i->doEntityCleanup();`

Register test entity.

`$i->registerTestEntity('node', '99');`

Register test entity by url.

`$i->registerTestEntityByUrl($i->grabFromCurrentUrl());`

Get entity by url.

`$entity = $i->getEntityFromUrl($i->grabFromCurrentUrl());`

## Drupal User

Provides better interaction with drupal user and test user setup and cleanup service.

### Configuration
- default_role: Default user role if no specified. Defaults to 'authenticated'
- driver: Driver used for interacting with site. Defaults to WebDriver.
- drush: Drush executable. Defaults to `drush`.
- cleanup_entities: Entities to delete when test user gets deleted.
- cleanup_test: Indicator if test entities should be deleted after each test.
- cleanup_failed: Indicator if test entities should be deleted after test fails.
- cleanup_suite: Indicator if test entities should be deleted after suite.
```
modules:
    - DrupalUser:
        default_role: 'authenticated'
        driver: 'PhpBrowser'
        drush: './vendor/bin/drush'
        cleanup_entities:
            - media
            - file
        cleanup_test: true
        cleanup_failed: false
        cleanup_suite: true
```

### Usage

Create test user with specified roles.

`$user = $i->createUserWithRoles(['editor', 'authenticated'], 'password');`

Log in user by username.

`$i->loginAs('userName');`

Create new user with certain role and login.

`$i->logInWithRole('administrator');`


## Drupal Watchdog

Provides log checking while testing.

### Configuration
- enabled: Wheather automatic check is enabled after suite. Defaults to `TRUE`
- level: Global log level that would produce fail. Defaults to 'ERROR'.
- channels: Individual log channel settings.
```
modules:
    - DrupalWatchdog:
        enabled: true
        level: 'ERROR'
        channels:
            my_module: 'NOTICE'
            php: 'WARNING'
```

### Usage

Clean logs.

`$i->prepareLogWatch();`

Check logs.

`$i->checkLogs();`

## Drupal Fields Utility

Provides xpath builder object for drupal specific form field xpath retrieval.

Includes:
    - FormField: Fields that can be set to cardinality unlimited
    - MTOFormField: Single value fields.
    - ParagraphFormField: Paragraph form fields.
    
### Usage

Create paragraph field with machine name field_page_elements.

`$page_elements = ParagraphFormField:field_page_elements();`

Get next paragraph.

`$page_elements->next();`

Fill title field value from field page elements.

`$i->fillField(FormField::title($page_elements)->value);`

Add new paragraph of type liftup_element.

```
$i->click($page_elements->addMore('liftup_element'));
$i->waitForElementVisible($page_elements->getCurrent('Subform'));
```