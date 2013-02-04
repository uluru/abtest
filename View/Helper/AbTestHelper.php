<?php
/*
                  #   #   #     #   #   ####    #   #
                  #   #   #     #   #   #   #   #   #
                  #   #   #     #   #   ####    #   #
                  #   #   #     #   #   #   #   #   #
                   ###    ####   ###    #   #    ###

             Copyright 2012 Uluru, Inc. All Rights Reserved.
*/

App::import('Components', 'AbTestComponent');

/**
 * AB Test Helper
 *
 * AB Test Componentをviewで使うためのヘルパー
 * @package     app
 * @subpackage  Helper
 * @since       2012/11/01
 * @author      HASHIDA Kazuhide <k_hashida@uluru.jp>
 */
class AbTestHelper extends AppHelper
{
    private $abTest;

    public function __construct(View $View)
    {
        $componentCollection = new ComponentCollection();
        $this->abTest = new AbTestComponent($componentCollection);
    }

    public function __call($methodName, $args)
    {
        return call_user_func_array(array($this->abTest, $methodName), $args);
    }
}

