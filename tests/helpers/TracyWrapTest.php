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
        Debugger::enable(false);

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
        $this->markTestSkipped();
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
}
