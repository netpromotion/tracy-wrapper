<?php

use Tracy\Debugger;

class TracyWrapTest extends PHPUnit_Framework_TestCase
{
    private function getHtmlPage($title = "HTML Page")
    {
        return "<html><head><title>{$title}</title></head><body><h1>{$title}</h1></body></html>";
    }

    private $renderer;

    public function setUp()
    {
        $helpersFile = __DIR__ . "/../../vendor/tracy/tracy/src/Tracy/Helpers.php";
        $contents = file_get_contents($helpersFile);
        file_put_contents($helpersFile, str_replace("PHP_SAPI", "'apache'", $contents));

        parent::setUp();
        $this->renderer = function() {
            print $this->getHtmlPage();
        };
    }

    /**
     * @runInSeparateProcess
     */
    public function testPassThroughIfDisabled()
    {
        ob_start();
        tracy_wrap($this->renderer);
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($this->getHtmlPage(), $content);
    }

    /**
     * @runInSeparateProcess
     * @dataProvider dataAssetIsDispatched
     * @param string $asset
     */
    public function testAssetIsDispatched($asset)
    {
        $_GET['_tracy_bar'] = $asset;
        Debugger::enable(true);

        ob_start();
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        tracy_wrap($this->renderer, [], null);
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertContains("This file is part of the Tracy", $content);
    }

    public function dataAssetIsDispatched()
    {
        return [["assets"]];
    }

    /**
     * @runInSeparateProcess
     */
    public function testContentIsDispatched()
    {
        session_start();
        Debugger::enable(true);
        ob_start();
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        tracy_wrap($this->renderer, [], null);
        $content = ob_get_contents();
        ob_end_clean();
        preg_match('/content(-ajax)?.(\w+)/', $content, $m);
        $_GET['_tracy_bar'] = "content.{$m[2]}{$m[1]}";

        ob_start();
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        tracy_wrap($this->renderer, [], null);
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertContains("Tracy.Debug.init(", $content);
    }

    /**
     * @runInSeparateProcess
     */
    public function testBarIsInjected()
    {
        Debugger::enable(true);

        ob_start();
        tracy_wrap($this->renderer);
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertContains("?_tracy_bar", $content);
    }

    /**
     * @runInSeparateProcess
     */
    public function testPanelsAreAddedFromArray()
    {
        Debugger::enable(true);

        $panel = $this->getMock("Tracy\\IBarPanel");
        $panel->expects($this->exactly(3))->method("getTab")->willReturn("");

        ob_start();
        tracy_wrap($this->renderer, [$panel, $panel, $panel]);
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
    public function testPanelsAreAddedViaCallable()
    {
        Debugger::enable(true);

        ob_start();
        tracy_wrap($this->renderer, function() {
            $panel = $this->getMock("Tracy\\IBarPanel");
            $panel->expects($this->exactly(3))->method("getTab")->willReturn("");

            return [$panel, $panel, $panel];
        });
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     * @dataProvider dataSecondParameterMustBeValid
     * @param mixed $secondParameter
     * @param bool $valid
     */
    public function testSecondParameterMustBeValid($secondParameter, $valid)
    {
        Debugger::enable(true);
        if (!$valid) {
            $this->setExpectedException("ErrorException");
        }

        ob_start();
        tracy_wrap($this->renderer, $secondParameter);
        ob_end_clean();
        $this->assertTrue($valid);
    }

    public function dataSecondParameterMustBeValid()
    {
        return [
            [$this->dataSecondParameterMustBeValid_array(), true],
            [$this->dataSecondParameterMustBeValid_null(), false],
            [$this->dataSecondParameterMustBeValid_string(), false],
            [[$this, "dataSecondParameterMustBeValid_array"], true],
            [[$this, "dataSecondParameterMustBeValid_null"], false],
            [[$this, "dataSecondParameterMustBeValid_string"], false]
        ];
    }

    public function dataSecondParameterMustBeValid_array()
    {
        return [];
    }

    public function dataSecondParameterMustBeValid_null()
    {
        return null;
    }

    public function dataSecondParameterMustBeValid_string()
    {
        return "string";
    }
}
