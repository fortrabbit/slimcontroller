# SlimController

SlimController is an extension for [the Slim Framework](http://www.slimframework.com/) providing the C of MVC.

With Slim alone, you can create great applications very, very quickly. Sometimes things get out of hand an you just need a bit more structure - or at least I do. That's what SlimController is for.

# Install via composer

Create a `composer.json` file

    {
        "require": {
            "slimcontroller/slimcontroller": "dev-master"
        },
        "autoload": {
            "psr-0": {
                "MyApp": "src/"
            }
        }
    }

Run installation

    composer.phar install --dev

# Mini HowTo

If you know how [Slim works](http://docs.slimframework.com/), using SlimController shouldn't be a big deal.

## Example Structure

Setup a structure for your controller and templates (just a suggestion, do as you like):

    mkdir -p src/MyApp/Controller templates

## Controller

Create your first controller in `src/MyApp/Controller/Home.php`

    <?php

    namespace MyApp\Controller;

    class Home extends \SlimController\SlimController
    {

        public function indexAction()
        {
            $this->render('home/index', array(
                'someVar' => date('c')
            ));
        }

        public function helloAction($name)
        {
            $this->render('home/hello', array(
                'name' => $name
            ));
        }
    }

## Templates

Here are the two corresponding demo templates:

`templates/home/index.php`

    This is the SlimControlle extension @ <?= $someVar ?>

`templates/home/hello.php`

    Hello <?= $name ?>

## Boostrap index.php

Minimal bootstrap file for this example

    <?php

    // load
    require 'vendor/autoload.php';

    // init app
    $app = new \SlimController\Slim(array(
        'templates.path'             => './templates',
        'controller.class_prefix'    => '\\MyApp\\Controller',
        'controller.method_suffix'   => 'Action',
        'controller.template_suffix' => 'php',
    ));

    $app->addRoutes(array(
        '/'            => 'Home:index',
        '/hello/:name' => 'Home:hello',
    ));

    $app->run();

## Run

    php -S localhos:8080