# [Tracy] Wrapper

Helper which helps you with [Tracy] injection into your project.


## Usage

```php
<?php // index.php

tracy_wrap(function() {
    app()->run();
}, [new BarPanelA(), new BarPanelB()] /* optional */);
```


## How to install

Run `composer require netpromotion/tracy-wrapper` in your project directory.



[Tracy]:https://tracy.nette.org/
