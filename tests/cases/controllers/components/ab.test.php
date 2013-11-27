<?php
App::import('Component', 'abtest.AbTest');
App::import('Component', 'Cookie');

class ComponentTestController extends Controller
{
    public $name = 'ComponentTest';
    public $components = array(
        'Cookie',
        'abtest.AbTest'
    );

    public $uses = null;
}

/**
* AbTest Test case
*/
class AbTestCase extends CakeTestCase
{
    private $Controller = null;

    public function testInitialize()
    {
        Configure::write(
            'AbTestConfig',
            array(
                'testCases' => array(
                    'TestCase.1' => array(
                        'customValueIndex' => 1,
                        'values' => array('foo', 'bar'),
                    ),
                ),
            )
        );

        $this->reset();
        $abTestArray = (array)$this->Controller->AbTest;
        $expires = $abTestArray['' . "\0". 'AbTestComponent' . "\0" . 'expires'];
        $this->AssertEqual($expires, 2592000);

        Configure::write('AbTestConfig.expires', '1 hours');
        $this->reset();
        $abTestArray = (array)$this->Controller->AbTest;
        $expires = $abTestArray['' . "\0". 'AbTestComponent' . "\0" . 'expires'];
        $this->AssertEqual($expires, '1 hours');
    }

    private function reset()
    {
        unset($this->Controller);
        $this->Controller = new ComponentTestController;

        $this->Controller->constructClasses();
        $this->Controller->Component->initialize($this->Controller);
    }
}
