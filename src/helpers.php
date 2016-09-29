<?php

use Tracy\Debugger;
use Tracy\IBarPanel;

if (!function_exists("tracy_wrap")) {
    /**
     * @param callable $yourCode
     * @param IBarPanel[] $barPanels
     */
    function tracy_wrap(callable $yourCode, array $barPanels = [])
    {
        $debugMode = Debugger::isEnabled();

        if ($debugMode) {
            $bar = Debugger::getBar();
            if ($bar->dispatchAssets() || $bar->dispatchContent()) {
                if (func_num_args() === 3) {
                    return /* for unit testing only */;
                }
                exit;
            }
            foreach ($barPanels as $barPanel) {
                $bar->addPanel($barPanel);
            }
            ob_start();
        }

        call_user_func($yourCode);

        if ($debugMode) {
            $output = ob_get_contents();
            ob_end_clean();
            ob_start();
            /** @noinspection PhpUndefinedVariableInspection */
            $bar->render();
            $output = preg_replace('/<\/body>/i', ob_get_contents() . '</body>', $output);
            ob_end_clean();
            print($output);
        }
    }
}
