<?php

declare(strict_types=1);

use App\Routes\System\MenuRoute;

it(runsTestsAutomatically('menu'), function () {
    MenuRoute::tests($this)->run();
});
