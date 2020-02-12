<?php

use Tracy\Debugger;
use Tracy\IBarPanel;

if (!function_exists("tracy_wrap")) {
    /**
     * @param callable $yourCode
     * @param callable|IBarPanel[] $barPanels
     */
    function tracy_wrap(callable $yourCode, $barPanels = [])
    {
        $debugMode = Debugger::isEnabled();

        if ($debugMode) {
            $bar = Debugger::getBar();
            if ($bar->dispatchAssets()) {
                if (func_num_args() === 3) {
                    return /* for unit testing only */;
                }
                exit;
            }
            if (is_callable($barPanels)) {
                $barPanels = call_user_func($barPanels);
            }
            if (!is_array($barPanels)) {
                user_error("Second parameter must be array or callable which returns array.", E_USER_ERROR);
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
