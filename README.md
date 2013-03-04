CakePHP A/B Test Plugin
================

CakePHP A/B Test Plugin makes your A/B test easy-configurable and trackable for Google Analytics on both 1.3 and 2.x of CakePHP.

Introduction
------

Once you configure some testcases, 50% goes A case and another one goes B until the case's cookie expires. Do not forget to call 'getAnalyticsCustomVar' between your Google Analytics tracking codes, so that you can combine the case and the result (conversion) on your Analytics management console, see the better one.

The plugin provides AbTestComponent and AbTestHelper. If you just want to test view, like 2-pattern banners to see which one is clicked more, use AbTestHelper to split by cases on a View. Or if you want to test some cases over some actions by somehow, use AbTestComponent to split some conditions in action. Ofcourse you can use both same time.

Requirements
------

The master branch has the following requirements:

* CakePHP 2.2.0 or greater.
* PHP 5.3.0 or greater.

The 1.3 branch is for CakePHP 1.3.x.

Installation
------

* Clone or copy the files into `app/Plugin/AbTest`
* Make sure the plugin is loaded in `app/Config/bootstrap.php` by calling `CakePlugin::load('AbTest');`
* Include the Cookie component in you `AppController.php`:
    * `public $components = array('Cookie');`
* Include the AbTestComponent where you want to use:
    * `public $components = array('AbTest.AbTest');`
* Include the AbTestHelper where you want to use:
    * `public $helpers = array('AbTest.AbTest');`

Configuration
------

Ensure to write below configs in `app/Config/bootstrap.php` or `app/Config/core.php`.

```php

/**
 * A/B test config for AbTest plugin.
 * AbTest.AbTest component and helper read this configs.
 */
Configure::write(
    'AbTestConfig',
    array(
        /**
         * testCases
         *
         * customValueIndex should have a number 1~5 unique by each over all cases.
         * (An error will be raised if it's not unique.)
         * Once you start some cases, it's better to leave those cases not to overwrite.
         * @link http://analytics-ja.blogspot.jp/2010/01/custom-variables-overview.html
         */
        'testCases' => array(
            'Sample1' => array(
                'customValueIndex' => 1,
                'values' => array('pattern1', 'pattern2')
            ),
            'Sample2' => array(
                'customValueIndex' => 2,
                'values' => array('pattern3', 'pattern4')
            ),
            'Sample3' => array(
                'customValueIndex' => 3,
                'values' => array('pattern5', 'pattern6')
            ),
            'Sample4' => array(
                'customValueIndex' => 4,
                'values' => array('pattern7', 'pattern8')
            ),
            'Sample5' => array(
                'customValueIndex' => 5,
                'values' => array('pattern9', 'pattern10')
            ),
        ),
        /**
         * expires
         *
         * A/B test expires setting for AbTest plugin.
         * The split case lasts until its cookie expires set below.
         */
        'expires' => time() + 60 * 60,
    )
);


```

Usage
------

Sample usage of AbTestComponent:

```php

if ($this->AbTest->start("Sample1") == "pattern1") {
    $testcase = "I'm the A case!";
} else {
    $testcase = "I'm the B case!";
}

```

Sample usage of AbTestHelper:

```php

<?php if ($this->AbTest->start("Sample1") == "pattern1") { ?>
    <p>I'm the A case!</p>
<?php } else { ?>
    <p>I'm the B case!</p>
<?php } ?>

```

Example usage of getAnalyticsCustomVar between Google Analytics tracking code:

```php

<!-- Google Analytics -->
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'MY_ANALYTICS_ACCOUNT_HERE']);
<?php echo $this->AbTest->getAnalyticsCustomVar(); ?>
_gaq.push(['_trackPageview']);
(function() {
 var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
 ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
 var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
 })();
<!-- /Google Analytics -->

```

LICENSE
------
Licensed under The MIT License. Redistributions of files must retain the above copyright notice.
Copyright 2013 [ULURU.CO.,LTD.](http://www.uluru.biz/) https://github.com/uluru

