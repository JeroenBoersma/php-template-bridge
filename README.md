# Template bridge

Template bridge between your legacy php code and newer template engines.

## Engines

* Plain
* Twig
* Compatible

## Basic usage

Use the manager to define your templates.

```php
use \Srcoder\TemplateBridge\Manager;
use \Srcoder\TemplateBridge\Engine\Twig;
use \Srcoder\TemplateBridge\Engine\Plain;

// Initialize template bridge
$templateBridge = new Manager;
// or static
$templateBridge = Manager::instance();

// Add a engine
$templateBridge->add(
        'twig',         // Name
        new Twig(),     // Template engine
        300             // Prio, higher is more important
);

$templateBridge->add(
        'plain',        // Name
        new Plain(),    // Template engine
        600             // Prio, higher is more important
);


// Adding a file
$templateManager->addFile('file');
// Will search for file.twig and file

// Render template
echo $templateBridge->render();

```

## Data

When rendering something you can add data to it.

```php
use \Srcoder\TemplateBridge\Data;

echo $templateBridge->render(new Data(['key' => 'value']))
```

## Fun part




### Twig engine

When a Twig file is found it will work exactly as you expect.

### Plain engine

Plain files will replace {{$variable}} from the Data object

### Compatible engine
