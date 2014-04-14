# SlimController

SlimController is an extension for [the Slim Framework](http://www.slimframework.com/) providing the C of MVC.

With Slim alone, you can create great applications very, very quickly. Sometimes things get out of hand an you just need a bit more structure - or at least I do. That's what SlimController is for.

[![Latest Stable Version](https://poser.pugx.org/slimcontroller/slimcontroller/v/stable.png)](https://packagist.org/packages/slimcontroller/slimcontroller)
[![Total Downloads](https://poser.pugx.org/slimcontroller/slimcontroller/downloads.png)](https://packagist.org/packages/slimcontroller/slimcontroller)

[![Build Status](https://travis-ci.org/fortrabbit/slimcontroller.png?branch=master)](https://travis-ci.org/fortrabbit/slimcontroller)

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

    mkdir -p src/MyApp/Controller templates/home

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

    This is the SlimController extension @ <?= $someVar ?>

`templates/home/hello.php`

    Hello <?= $name ?>

## Boostrap index.php

Minimal bootstrap file for this example

    <?php

    // define a working directory
    define('APP_PATH', dirname(__DIR__)); // PHP v5.3+

    // load
    require APP_PATH . '/vendor/autoload.php';

    // init app
    $app = New \SlimController\Slim(array(
        'templates.path'             => APP_PATH . '/templates',
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

    php -S localhost:8080


# Controller

## Configuration

### controller.class_prefix

Optional class prefix for controller classes. Will be prepended to routes.

Using `\\MyApp\\Controller` as prefix with given routes:

    $app->addRoutes(array(
        '/'            => 'Home:index',
        '/hello/:name' => 'Home:hello',
    ));

Translates to

    $app->addRoutes(array(
        '/'            => '\\MyApp\\Controller\\Home:index',
        '/hello/:name' => '\\MyApp\\Controller\\Home:hello',
    ));

### controller.class_suffix

Optional class suffix for controller classes. Will be appended to routes.

Using `Controller` as suffix with given routes:

    $app->addRoutes(array(
        '/'            => 'Home:index',
        '/hello/:name' => 'Home:hello',
    ));

Translates to

    $app->addRoutes(array(
        '/'            => 'HomeController:index',
        '/hello/:name' => 'HomeController:hello',
    ));

### controller.method_suffix

Optional method suffix. Appended to routes.

Using `Action` as suffix with given routes:

    $app->addRoutes(array(
        '/'            => 'Home:index',
        '/hello/:name' => 'Home:hello',
    ));

Translates to

    $app->addRoutes(array(
        '/'            => 'Home:indexAction',
        '/hello/:name' => 'Home:helloAction',
    ));

### controller.template_suffix

Defaults to `twig`. Will be appended to template name given in `render()` method.

## Extended Examples

### Routes

    // how to integrate the Slim middleware
    $app->addRoutes(array(
        '/' => array('Home:index', array(function() {
                error_log("MIDDLEWARE FOR SINGLE ROUTE");
            },
            function() {
                error_log("ADDITIONAL MIDDLEWARE FOR SINGLE ROUTE");
            })
        ),
        '/hello/:name' => array('post' => 'Home:hello', function() {
                error_log("THIS ROUTE IS ONLY POST");
            }
        )
    ), function() {
        error_log("APPENDED MIDDLEWARE FOR ALL ROUTES");
    });

### Controller

    <?php

    namespace MyApp\Controller;

    class Sample extends \SlimController\SlimController
    {

        public function indexAction()
        {

            /**
             * Access \SlimController\Slim $app
             */

            $this->app->response()->status(404);


            /**
             * Params
             */

            // reads "?data[foo]=some+value"
            $foo = $this->param('foo');

            // reads "data[bar][name]=some+value" only if POST!
            $bar = $this->param('bar.name', 'post');

            // all params of bar ("object attributes")
            //  "?data[bar][name]=me&data[bar][mood]=happy" only if POST!
            $bar = $this->param('bar');
            //error_log($bar['name']. ' is '. $bar['mood']);

            // reads multiple params in array
            $params = $this->params(array('foo', 'bar.name1', 'bar.name1'));
            //error_log($params['bar.name1']);

            // reads multiple params only if they are POST
            $params = $this->params(array('foo', 'bar.name1', 'bar.name1'), 'post');

            // reads multiple params only if they are POST and all are given!
            $params = $this->params(array('foo', 'bar.name1', 'bar.name1'), 'post', true);
            if (!$params) {
                error_log("Not all params given.. maybe some. Don't care");
            }

            // reads multiple params only if they are POST and replaces non given with defaults!
            $params = $this->params(array('foo', 'bar.name1', 'bar.name1'), 'post', array(
                'foo' => 'Some Default'
            ));


            /**
             * Redirect shortcut
             */

            if (false) {
                $this->redirect('/somewhere');
            }


            /**
             * Rendering
             */

            $this->render('folder/file', array(
                'foo' => 'bar'
            ));

        }
    }
